<?php

namespace Modules\Promotion\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Promotion\Entities\Promotion;
use Modules\Promotion\Http\Requests\StorePromotionRequest;
use Modules\Promotion\Http\Requests\UpdatePromotionRequest;
use Modules\Promotion\Http\Services\PromotionService;

class PromotionController extends Controller
{

    private PromotionService $promotionService;

    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    /**
     * Get list of promotions
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        // return error if user is not a brand
        if ($user->cannot('viewAny', Promotion::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->promotionService->getPromotions($user->id);

        return response()->json($response);
    }

    /**
     * Store a newly created promotion in storage
     *
     * @param StorePromotionRequest $request
     * @return JsonResponse
     */
    public function store(StorePromotionRequest $request): JsonResponse
    {
        $user = auth()->user();

        // return error if user cannot create promotion
        if ($user->cannot('create', Promotion::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $request->request->add(['user_id' => $user->id]);
        $response = $this->promotionService->store($request->validated());

        return response()->json($response);
    }

    /**
     * Fetch the specified promotion
     *
     * @param string $promotionKey
     * @return JsonResponse
     */
    public function show(string $promotionKey): JsonResponse
    {
        $user = auth()->user();
        $promotion = Promotion::where('promotion_key', $promotionKey)->first();

        // return error if user not created the promotion
        if ($user->cannot('view', $promotion)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->promotionService->get($promotionKey);

        return response()->json($response);
    }

    /**
     * Update the specified promotion in storage
     *
     * @param UpdatePromotionRequest $request
     * @return JsonResponse
     */
    public function update(UpdatePromotionRequest $request): JsonResponse
    {
        $user = auth()->user();
        $promotion = Promotion::where('promotion_key', $request->promotion_key)->first();

        // return error if user cannot update promotion
        if ($user->cannot('update', $promotion)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->promotionService->update($request->validated());

        return response()->json($response);
    }

    /**
     * Remove the specified promotion from storage
     *
     * @param string $promotionKey
     * @return JsonResponse
     */
    public function destroy(string $promotionKey): JsonResponse
    {
        $user = auth()->user();
        $promotion = Promotion::where('promotion_key', $promotionKey)->first();

        // return error if user not created the promotion
        if ($user->cannot('delete', $promotion)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->promotionService->delete($promotionKey);

        return response()->json($response);
    }

    /**
     * Get list of promotion featured ads
     *
     * @return JsonResponse
     */
    public function featuresList(): JsonResponse
    {
        $response = $this->promotionService->getFeatures();

        return response()->json($response);
    }

}
