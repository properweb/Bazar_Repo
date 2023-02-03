<?php

namespace Modules\Campaign\Http\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Campaign\Entities\Campaign;


class CampaignService
{
    protected Campaign $campaign;

    protected Brand $brand;

    /**
     * Save order
     *
     * @param array $request
     * @return array
     */
    public function store(array $requestData): array
    {
        $this->campaign = $this->createCampaign($requestData);

        $response = [
            'res' => true,
            'msg' => 'Your campaign created successfully',
            'data' => ""
        ];

        return $response;
    }

    /**
     * Create new campaign
     *
     * @param  array  $campaignData
     * @return Campaign
     */
    public function createCampaign(array $campaignData): Campaign
    {
        //create campaign
        $campaign = new Campaign();
        $campaign->brand_id = $campaignData['user_id'];
        $campaign->campaign_key = 'bmc_' . Str::lower(Str::random(10));
        $campaign->title = $campaignData['title'];
        $campaign->save();

        return $campaign;
    }

    /**
     * Get all Campaigns
     *
     * @return array
     */
    public function getCampaigns($requestData): array
    {
        $user = User::find($requestData->user_id);
        // return error if no campaign found
        if (empty($user)) {
            return [
                'res' => false,
                'msg' => 'No record found !',
                'data' => ""
            ];
        }
        
        $brand = Brand::where('user_id', $user->id)->first();
        $allCampaignsCount = Campaign::where('brand_id', $brand->user_id)->count();
        $draftCampaignsCount = Campaign::where('brand_id', $brand->user_id)->where('status', 'draft')->count();
        $scheduledCampaignsCount = Campaign::where('brand_id', $brand->user_id)->where('status', 'schedule')->count();
        $completedCampaignsCount = Campaign::where('brand_id', $brand->user_id)->where('status', 'completed')->count();
        $campaigns = Campaign::where('brand_id', $brand->user_id);
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
        $response = ['res' => true, 'msg' => "", 'data' => $data];

        return $response;
    }

    /**
     * Delete campaign
     *
     * @param array $request
     * @return array
     */
    public function delete($campaignKey): void
    {
        $campaign = Campaign::where('campaign_key', $campaignKey)->first();


        // return error if no campaign found
        if (empty($campaign)) {
            return [
                'res' => false,
                'msg' => 'No record found !',
                'data' => ""
            ];
        }
        $this->campaign = Campaign::findOrFail($campaign->id);
        $this->campaign->delete();

        $response = [
                'res' => true,
                'msg' => 'Campaign successfully deleted',
                'data' => ""
         ];

        return $response;
    }

}
