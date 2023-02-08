<?php

namespace Modules\Campaign\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Campaign\Http\Requests\StoreCampaignRequest;
use Modules\Campaign\Http\Services\CampaignService;
use Modules\Campaign\Entities\Campaign;
use Modules\User\Entities\User;

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
        $response = $this->campaignService->store($request->validated());

        return response()->json($response);
    }

    /**
     * Fetch the specified campaign
     *
     * @param int $userId
     * @param string $campaignKey
     * @return JsonResponse
     */
    public function show($userId, $campaignKey)
    {
        $response = $this->campaignService->get($userId, $campaignKey);

        return response()->json($response);
    }

    /**
     * Remove the specified campaign from storage
     *
     * @param int $userId
     * @param string $campaignKey
     * @return JsonResponse
     */
    public function destroy($userId, $campaignKey)
    {
        $response = $this->campaignService->delete($userId, $campaignKey);

        return response()->json($response);
    }

}
