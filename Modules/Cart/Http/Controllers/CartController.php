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
     * Fetch logged user cart
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function fetch(Request $request): JsonResponse
    {

        $user = auth()->user();
        if ($user->cannot('view', Cart::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->cartService->fetch($request);

        return response()->json($response);
    }

    /**
     * User can add product on their cart
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function add(CartRequest $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('create', Cart::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'Please login as a retailer',
                'data' => ""
            ]);
        }

        $response = $this->cartService->add($request);

        return response()->json($response);
    }

    /**
     * User can delete product from existing cart
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $user = auth()->user();
        $cart = Cart::where('id', $request->id)->where('user_id', $user->id)->first();
        if ($user->cannot('delete', $cart)) {
            return response()->json([
                'res' => false,
                'msg' => 'You are not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->cartService->delete($request->id,$request->user_id);

        return response()->json($response);
    }


}
