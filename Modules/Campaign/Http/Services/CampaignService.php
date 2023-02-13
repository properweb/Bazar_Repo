<?php

namespace Modules\Campaign\Http\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\User\Entities\User;
use Modules\Campaign\Entities\Campaign;


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

        $allCampaignsCount = Campaign::where('user_id', $requestData->user_id)->count();
        $draftCampaignsCount = Campaign::where('user_id', $requestData->user_id)->where('status', 'draft')->count();
        $scheduledCampaignsCount = Campaign::where('user_id', $requestData->user_id)->where('status', 'schedule')->count();
        $completedCampaignsCount = Campaign::where('user_id', $requestData->user_id)->where('status', 'completed')->count();
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

        return [
            'res' => true,
            'msg' => '',
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
        $user = auth('sanctum')->user();
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
