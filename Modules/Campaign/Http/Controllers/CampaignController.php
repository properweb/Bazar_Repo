<?php

namespace Modules\Campaign\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Campaign\Entities\Campaign;

class CampaignController extends Controller {
    
    public function __construct(CampaignService $campaignService)
    {
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request) {
        $response = $this->campaignService->store($request->all());
        
        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreOrderRequest $request
     * @return mixed
     */
    public function store(Request $request) {
        
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
        $campaign = Campaign::where('campaign_key', $campaignKey)->first();
        $status = $campaign->delete();

        if ($status) {
            $response = ['res' => true, 'msg' => "Campaign successfully deleted", 'data' => ""];
        } else {
            $response = ['res' => false, 'msg' => "Error while deleting campaign", 'data' => ""];
        }
        return response()->json($response);
    }

}
