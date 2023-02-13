<?php

namespace Modules\Campaign\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Campaign\Entities\Campaign;
use Modules\Campaign\Http\Requests\StoreCampaignRequest;
use Modules\Campaign\Http\Services\CampaignService;


class CampaignController extends Controller
{

    private CampaignService $campaignService;

    public function __construct(CampaignService $campaignService)
    {
        $this->campaignService = $campaignService;
    }

    /**
     * Get list of campaigns
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {

        $user = auth('sanctum')->user();

        // return error if user is not a brand
        if ($user->cannot('viewAny', Campaign::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $request->request->add(['user_id' => $user->id]);
        $response = $this->campaignService->getCampaigns($request);

        return response()->json($response);
    }

    /**
     * Store a newly created campaign in storage
     *
     * @param StoreCampaignRequest $request
     * @return JsonResponse
     */
    public function store(StoreCampaignRequest $request): JsonResponse
    {
        $user = auth('sanctum')->user();

        // return error if user cannot create campaign
        if ($user->cannot('create', Campaign::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $request->request->add(['user_id' => $user->id]);
        $response = $this->campaignService->store($request->validated());

        return response()->json($response);
    }

    /**
     * Fetch the specified campaign
     *
     * @param string $campaignKey
     * @return JsonResponse
     */
    public function show(string $campaignKey): JsonResponse
    {

        $user = auth('sanctum')->user();
        $campaign = Campaign::where('campaign_key', $campaignKey)->first();

        // return error if user not created the campaign
        if ($user->cannot('view', $campaign)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->campaignService->get($campaignKey);

        return response()->json($response);
    }

    /**
     * Remove the specified campaign from storage
     *
     * @param string $campaignKey
     * @return JsonResponse
     */
    public function destroy(string $campaignKey): JsonResponse
    {
        $user = auth('sanctum')->user();
        $campaign = Campaign::where('campaign_key', $campaignKey)->first();

        // return error if user not created the campaign
        if ($user->cannot('delete', $campaign)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->campaignService->delete($campaignKey);

        return response()->json($response);
    }

}
