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
        DB::beginTransaction();

        try {
            $this->campaign = $this->createCampaign($requestData);

            $response = [
                'res' => true,
                'msg' => 'Your campaign created successfully',
                'data' => ""
            ];

            DB::commit();
            //todo Log successfull creation
        } catch (\Exception $e) {
            // something went wrong
            //todo Log exception
            DB::rollback();
            $response = [
                'res' => false,
                'msg' => 'Someting went wrong !',
                'data' => ""
            ];

        }

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
        if ($user) {
            $brand = Brand::where('user_id', $user->id)->first();
            $allCampaignsCount = Campaign::where('brand_id', $brand->user_id)->count();
            $draftCampaignsCount = Campaign::where('brand_id', $brand->user_id)->where('status', 'draft')->count();
            $scheduledCampaignsCount = Campaign::where('brand_id', $brand->user_id)->where('status', 'schedule')->count();
            $completedCampaignsCount = Campaign::where('brand_id', $brand->user_id)->where('status', 'completed')->count();
            $campaigns = Campaign::where('brand_id', $brand->user_id);
            $status = strtolower($requestData->status);
            switch ($status) {
                case 'all':
                    break;
                default:
                    $campaigns->where('status', $status);
                    break;
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
        } else {
            $response = ['res' => false, 'msg' => "No record found", 'data' => ""];
        }
        
        

        return $response;
    }
    
    /**
     * Delete campaign
     *
     * @param array $request
     * @return array
     */
    public function delete( $campaignKey): 
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

        DB::beginTransaction();

        try {
            $this->campaign = Campaign::where('campaign_key', $campaignKey)->first();

            $this->deleteCampaign($campaign);

            
            $response = [
                'res' => true,
                'msg' => 'Campaign successfully deleted',
                'data' => ""
            ];

            DB::commit();
            //todo Log successfull creation
        } catch (\Exception $e) {
            // something went wrong
            //todo Log exception
            DB::rollback();
            $response = [
                'res' => false,
                'msg' => 'Error while deleting campaign !',
                'data' => ""
            ];

        }

        return $response;
    }
    
    /**
     * @param Product|null $existingProduct
     */
    private function deleteCampaign(DeleteCampaign $deleteCampaign): void
    {
        $deleteCampaign->delete();
    }

    
}
