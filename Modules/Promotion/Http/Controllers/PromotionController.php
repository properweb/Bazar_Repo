<?php

namespace Modules\Promotion\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Promotion\Http\Requests\StorePromotionRequest;
use Modules\Promotion\Http\Requests\UpdatePromotionRequest;
use Modules\Promotion\Http\Services\PromotionService;

class PromotionController extends Controller
{

    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    /**
     * Get list of promotions
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $response = $this->promotionService->getPromotions($request);

        return response()->json($response);
    }

    /**
     * Store a newly created promotion in storage
     *
     * @param StorePromotionRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StorePromotionRequest $request)
    {
        $response = $this->promotionService->store($request->validated());

        return response()->json($response);
    }

    /**
     * Fetch the specified promotion
     *
     * @param int $userId
     * @param string $promotionKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($userId, $promotionKey)
    {
        $response = $this->promotionService->get($userId, $promotionKey);

        return response()->json($response);
    }

    /**
     * Update the specified promotion in storage
     *
     * @param UpdatePromotionRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdatePromotionRequest $request)
    {
        $response = $this->promotionService->update($request->validated());

        return response()->json($response);
    }

    /**
     * Remove the specified promotion from storage
     *
     * @param int $userId
     * @param string $promotionKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($userId, $promotionKey)
    {
        $response = $this->promotionService->delete($userId, $promotionKey);

        return response()->json($response);
    }

}
