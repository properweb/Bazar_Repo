<?php

namespace Modules\Campaign\Http\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Modules\Brand\Entities\Brand;
use Modules\Customer\Entities\Customer;
use Modules\User\Entities\User;
use Modules\Campaign\Entities\Campaign;
use Modules\Campaign\Entities\CampaignRecipent;


class CampaignService
{
    protected Campaign $campaign;

    protected User $user;

    /**
     * Save a new campaign
     *
     * @param array $requestData
     * @return array
     */
    public function store(array $requestData): array
    {

        //set request data with authenticated user id.
        $requestData["status"] = Campaign :: STATUS_DRAFT;
        $campaign = $this->createCampaign($requestData);
        $response = [
            'res' => true,
            'msg' => 'Your campaign created successfully',
            'data' => $campaign
        ];

        return $response;
    }

    /**
     * Create a new campaign
     *
     * @param array $campaignData
     * @return Campaign
     */
    public function createCampaign(array $campaignData): Campaign
    {

        //set campaign data
        $campaignData["campaign_key"] = 'bmc_' . Str::lower(Str::random(10));

        //create campaign
        $campaign = new Campaign();
        $campaign->fill($campaignData);
        $campaign->save();

        return $campaign;
    }

    /**
     * Get a listing of the campaigns
     *
     * @param $requestData
     * @return array
     */
    public function getCampaigns($requestData): array
    {
        $user = auth()->user();
        $campaignCounts = Campaign::selectRaw('status, count(*) as count')
            ->where('user_id', $requestData->user_id)
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        $allCampaignsCount = $campaignCounts->sum();
        $draftCampaignsCount = $campaignCounts->get('draft', 0);
        $scheduledCampaignsCount = $campaignCounts->get('schedule', 0);
        $completedCampaignsCount = $campaignCounts->get('completed', 0);
        $campaigns = Campaign::where('user_id', $requestData->user_id);
        $status = strtolower($requestData->status);
        if ($status !== 'all') {
            $campaigns->where('status', $status);
        }
        $paginatedCampaigns = $campaigns->paginate(10);
        $filteredCampaigns = [];
        if ($paginatedCampaigns) {
            foreach ($paginatedCampaigns as $campaign) {
                $filteredCampaigns[] = array(
                    'title' => $campaign->title,
                    'campaign_key' => $campaign->campaign_key,
                    'updated_at' => date("F j, Y, g:i a", strtotime($campaign->updated_at)),
                );
            }
        }
        $data = array(
            "campaigns" => $filteredCampaigns,
            "allCampaignsCount" => $allCampaignsCount,
            "draftCampaignsCount" => $draftCampaignsCount,
            "scheduledCampaignsCount" => $scheduledCampaignsCount,
            "completedCampaignsCount" => $completedCampaignsCount,
        );

        return ['res' => true, 'msg' => "", 'data' => $data];
    }

    /**
     * Get the specified campaign
     *
     * @param string $campaignKey
     * @return array
     */
    public function get(string $campaignKey): array
    {

        $campaign = Campaign::where('campaign_key', $campaignKey)->first();

        // return error if no campaign found
        if (!$campaign) {
            return [
                'res' => false,
                'msg' => 'Campaign not found !',
                'data' => ""
            ];
        }
        $campaign->updated_at_text = date("F j, Y", strtotime($campaign->updated_at));

        return [
            'res' => true,
            'msg' => '',
            'data' => $campaign
        ];
    }

    /**
     * Update the specified campaign in storage.
     *
     * @param array $requestData
     * @return array
     */
    public function update(array $requestData): array
    {
        $user = auth()->user();
        $brand = Brand::where('user_id', $user->id)->first();
        $campaign = Campaign::where('campaign_key', $requestData['campaign_key'])->first();


        // return error if no promotion found
        if (!$campaign) {
            return [
                'res' => false,
                'msg' => 'Campaign not found !',
                'data' => ""
            ];
        }

        $campaign->update($requestData);
        $emailCustomers = [];
        if (!empty($requestData['customers'])) {
            foreach ($requestData['customers'] as $customerType) {
                if ($customerType == 'all') {
                    $customers = Customer::where('user_id', $user->id)->get();
                    if (!empty($customers)) {
                        foreach ($customers as $customer) {
                            if (!in_array($customer->id, $emailCustomers)) {
                                $emailCustomers[] = $customer->id;
                            }
                        }
                    }
                }
            }
        }
        $imgTags = [];
        $emailBody = $campaign->email_design;
        preg_match_all('/<img[^>]+>/i', stripcslashes($emailBody), $imgTags);

        if (!empty($imgTags[0])) {
            foreach ($imgTags[0] as $imgVal) {
                preg_match('/src="([^"]+)/i', $imgVal, $withSrc);
                //Remove src
                $withoutSrc = str_ireplace('src="', '', $withSrc[0]);
                //dd($withoutSrc);
                if (strpos($withoutSrc, ";base64,")) {
                    $image_64 = $withoutSrc; //your base64 encoded data
                    $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                    $image_64 = str_replace($replace, '', $image_64);
                    $image_64 = str_replace(' ', '+', $image_64);
                    $imageName = Str::random(10) . '.' . 'png';
                    File::put(public_path('uploads/campaigns/') . $imageName, base64_decode($image_64));
                    $uploadedImage = asset('public') . '/uploads/campaigns/' . $imageName;
                    $uploadedImage = '<img src="' . asset('public') . '/uploads/campaigns/' . $imageName . '"/>';
                    //dd($uploadedImage);
                    $emailBody = str_ireplace($imgVal, $uploadedImage, $emailBody);
                    //$emailBody=preg_replace($imgVal,$uploadedImage,$emailBody);
                }
            }
        }
        $brandUrl = '<a style="background-image:initial;background-position-x:initial;background-position-y:initial;background-size:initial;background-repeat-x:initial;background-repeat-y:initial;background-attachment:initial;background-origin:initial;background-clip:initial;background-color:#393939;border-image-source:initial;border-image-slice:initial;border-image-width:initial;border-image-outset:initial;border-image-repeat:initial;border-top-left-radius:3px;border-top-right-radius:3px;border-bottom-right-radius:3px;border-bottom-left-radius:3px;color:#fff;display:inline-block;font-family:Arial,Helvetica,sans-serif;font-size:16px;font-weight:400;line-height:41px;text-align:center;border-color:#393939;border-style:solid;border-width:1px;padding:0 100px;tex-decoration:none;" href="' . config('app.web_url') . '/brand/' . $brand->bazaar_direct_link . '">Shop</a>';
        $emailBody = preg_replace("/<a\s(.+?)>(.+?)<\/a>/is", $brandUrl, $emailBody);

        $emailCSS = $campaign->email_style;
        if (!empty($emailCustomers)) {
            foreach ($emailCustomers as $emailCustomer) {
                //create review
                $recipentData = [];
                $recipent = new CampaignRecipent();
                $recipentData['campaign_id'] = $campaign->id;
                $recipentData['customer_id'] = $emailCustomer;
                $recipent->fill($recipentData);
                $recipent->save();

                $customer = Customer::find($emailCustomer);
                Mail::send(['html' => 'email.campaign'], ['body' => $emailBody, 'css' => $emailCSS], function ($message) use ($customer) {
                    $message->to($customer->email);
                    $message->from("sender@demoupdates.com");
                    $message->subject('Bazar:Campaign');
                });
            }
        }

        return [
            'res' => true,
            'msg' => 'Your campaign updated successfully',
            'data' => $campaign
        ];
    }

    /**
     * Update the specified campaign in storage.
     *
     * @param array $requestData
     * @return array
     */
    public function rename(array $requestData): array
    {
        $user = auth()->user();
        $campaign = Campaign::where('campaign_key', $requestData['campaign_key'])->first();

        // return error if no promotion found
        if (!$campaign) {
            return [
                'res' => false,
                'msg' => 'Campaign not found !',
                'data' => ""
            ];
        }
        $campaign->title = $requestData['title'];
        $campaign->save();

        return [
            'res' => true,
            'msg' => 'Your campaign updated successfully',
            'data' => $campaign
        ];
    }

    /**
     * Remove the specified campaign from storage.
     *
     * @param string $campaignKey
     * @return array
     */
    public function delete(string $campaignKey): array
    {
        $campaign = Campaign::where('campaign_key', $campaignKey)->first();

        // return error if no campaign found
        if (!$campaign) {
            return [
                'res' => false,
                'msg' => 'Campaign not found !',
                'data' => ""
            ];
        }

        $campaign->delete();

        return [
            'res' => true,
            'msg' => 'Campaign successfully deleted',
            'data' => ""
        ];
    }

}
