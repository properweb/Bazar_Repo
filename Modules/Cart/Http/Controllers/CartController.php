<?php

namespace Modules\Cart\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Cart\Entities\Cart;
use Modules\Cart\Http\Requests\CartRequest;
use Modules\Cart\Http\Services\CartService;
use Illuminate\Http\Request;


class CartController extends Controller
{

    private CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function fetch(Request $request): JsonResponse
    {
        $response = $this->cartService->fetch($request);
        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function add(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();

        $response = $this->cartService->add($request->all());
        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $response = $this->cartService->delete($request->id,$request->user_id);
        return response()->json($response);
    }


}
