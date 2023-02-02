<?php

namespace Modules\Campaign\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Campaign\Http\Requests\StoreCampaignRequest;
use Modules\Campaign\Http\Services\CampaignService;
use Modules\Campaign\Entities\Campaign;

class CampaignController extends Controller
{

    public function __construct(CampaignService $campaignService)
    {
        $this->campaignService = $campaignService;
    }

    /**
     * Get list of campaigns
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {

        $response = $this->campaignService->getCampaigns($request);

        return response()->json($response);
    }

    /**
     * Store a newly created campaign in storage
     * 
     * @param StoreCampaignRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreCampaignRequest $request)
    {

        $response = $this->campaignService->store($request->all());

        return response()->json($response);
    }

    /**
     * Fetch the specified campaign
     * 
     * @param string $campaignKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($campaignKey)
    {
        $campaign = Campaign::where('campaign_key', $campaignKey)->first();
        if ($campaign) {
            $response = ['res' => true, 'msg' => "", 'data' => $campaign];
        } else {
            $response = ['res' => false, 'msg' => "No record found", 'data' => ""];
        }
        return response()->json($response);
    }

    /**
     * Remove the specified campaign from storage
     * 
     * @param string $campaignKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($campaignKey)
    {
        $response = $this->campaignService->delete($campaignKey);

        return response()->json($response);
    }

}
