<?php

namespace Modules\Promotion\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Promotion\Http\Requests\StorePromotionRequest;
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
        $response = $this->promotionService->store($request->all());

        return response()->json($response);
    }

    /**
     * Fetch the specified promotion
     * 
     * @param string $promotionKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($promotionKey)
    {
        $response = $this->promotionService->get($promotionKey);
        
        return response()->json($response);
    }

    /**
     * Update the specified promotion in storage
     * 
     * @param StorePromotionRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(StorePromotionRequest $request)
    {
        $response = $this->promotionService->update($request->all());

        return response()->json($response);
    }

    /**
     * Remove the specified promotion from storage
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $response = $this->promotionService->delete($request->all());
        
        return response()->json($response);
    }

}
