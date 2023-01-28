<?php

namespace Modules\Campaign\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Campaign\Http\Requests\StoreCampaignRequest;
use Modules\Campaign\Http\Services\CampaignService;
use Modules\Campaign\Entities\Campaign;

class CampaignController extends Controller {
    
    public function __construct(CampaignService $campaignService)
    {
        $this->campaignService = $campaignService;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request) {
        
        $response = $this->campaignService->getCampaigns($request);
        
        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreOrderRequest $request
     * @return mixed
     */
    public function store(StoreCampaignRequest $request) {
        
        $response = $this->campaignService->store($request->all());
        
        return response()->json($response);
    }

    /**
     * Show the specified resource.
     * @param Request $request
     * @param $campaignKey
     * @return mixed
     */
    public function show($campaignKey) {
        $campaign = Campaign::where('campaign_key', $campaignKey)->first();
        if ($campaign) {
            $response = ['res' => true, 'msg' => "", 'data' => $campaign];
        } else {
            $response = ['res' => false, 'msg' => "No record found", 'data' => ""];
        }
        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param $campaignKey
     * @return Renderable
     */
    public function update(Request $request, $campaignKey) {
        
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @param int $campaignKey
     * @return mixed
     */
    public function destroy(Request $request, $campaignKey) {
        $response = $this->campaignService->delete($campaignKey);
        
        return response()->json($response);
        
    }

}
