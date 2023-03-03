<?php

namespace Modules\Wishlist\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Wishlist\Http\Requests\WishlistRequest;
use Modules\Wishlist\Http\Services\WishlistService;
use Illuminate\Http\Request;


class WishlistController extends Controller
{

    private WishlistService $wishlistService;

    public function __construct(WishlistService $wishlistService)
    {
        $this->wishlistService = $wishlistService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function add(Request $request): JsonResponse
    {
        $response = $this->wishlistService->add($request);
        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function fetch(Request $request): JsonResponse
    {
        $response = $this->wishlistService->fetch($request);
        return response()->json($response);
    }

    public function fetchBoards(Request $request): JsonResponse
    {
        $response = $this->wishlistService->fetchBoards($request);
        return response()->json($response);
    }

    public function fetchBoard(Request $request): JsonResponse
    {
        $response = $this->wishlistService->fetchBoard($request);
        return response()->json($response);
    }

    public function addBoard(WishlistRequest $request): JsonResponse
    {
        $response = $this->wishlistService->addBoard($request->validated());
        return response()->json($response);
    }
    public function updateBoard(WishlistRequest $request): JsonResponse
    {
        $response = $this->wishlistService->updateBoard($request->validated());
        return response()->json($response);
    }
    public function deleteBoard(Request $request): JsonResponse
    {
        $response = $this->wishlistService->deleteBoard($request);
        return response()->json($response);
    }

    public function changeBoard(Request $request): JsonResponse
    {
        $response = $this->wishlistService->changeBoard($request);
        return response()->json($response);
    }

    public function delete(Request $request): JsonResponse
    {
        $response = $this->wishlistService->delete($request);
        return response()->json($response);
    }

}
