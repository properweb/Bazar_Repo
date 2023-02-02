<?php

namespace Modules\Promotion\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Promotion\Http\Requests\StorePromotionRequest;
use Modules\Promotion\Http\Services\PromotionService;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Promotion\Entities\Promotion;

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
        $promotion = Promotion::where('promotion_key', $promotionKey)->first();
        if ($promotion) {
            $response = ['res' => true, 'msg' => "", 'data' => $promotion];
        } else {
            $response = ['res' => false, 'msg' => "No record found", 'data' => ""];
        }
        return response()->json($response);
    }

    /**
     * Remove the specified promotion from storage
     * 
     * @param string $promotionKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($promotionKey)
    {
        $promotion = Promotion::where('promotion_key', $promotionKey)->first();
        $status = $promotion->delete();

        if ($status) {
            $response = ['res' => true, 'msg' => "Promotion successfully deleted", 'data' => ""];
        } else {
            $response = ['res' => false, 'msg' => "Error while deleting promotion", 'data' => ""];
        }
        return response()->json($response);
    }

}
