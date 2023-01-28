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

class PromotionController extends Controller {
    
    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request) {
        $response = $this->promotionService->getPromotions($request);
        
        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return mixed
     */
    public function store(StorePromotionRequest $request) {
        
        $response = $this->promotionService->store($request->all());
        
        return response()->json($response);
    }

    /**
     * Show the specified resource.
     * @param Request $request
     * @param $promotionKey
     * @return mixed
     */
    public function show(Request $request, $promotionKey) {
        $promotion = Promotion::where('promotion_key', $promotionKey)->first();
        if ($promotion) {
            $response = ['res' => true, 'msg' => "", 'data' => $promotion];
        } else {
            $response = ['res' => false, 'msg' => "No record found", 'data' => ""];
        }
        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param $promotionKey
     * @return Renderable
     */
    public function update(Request $request, $promotionKey) {
        
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @param int $promotionKey
     * @return mixed
     */
    public function destroy(Request $request, $promotionKey) {
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
