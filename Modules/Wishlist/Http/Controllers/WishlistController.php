<?php

namespace Modules\Wishlist\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Wishlist\Entities\Wishlist;
use Modules\Wishlist\Entities\Board;
use Modules\Wishlist\Http\Requests\BoardRequest;
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
     * Add wishlist by logged user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function add(Request $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('create', Wishlist::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->wishlistService->add($request);

        return response()->json($response);
    }

    /**
     * Fetch wishlist by logged user
     *
     * @return JsonResponse
     */
    public function fetch(): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('viewAny', Wishlist::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->wishlistService->fetch();

        return response()->json($response);
    }

    /**
     * Delete wishlist by ID
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {

        $user = auth()->user();
        $wishList = Wishlist::where('id', $request->id)->first();
        if ($user->cannot('delete', $wishList)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->wishlistService->delete($request);

        return response()->json($response);
    }

    /**
     * Fetch boards which created by logged user
     *
     * @return JsonResponse
     */
    public function fetchBoards(): JsonResponse
    {
        $user = auth()->user();

        if ($user->cannot('viewAny', Board::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->wishlistService->fetchBoards();

        return response()->json($response);
    }

    /**
     * Get board by board key
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function fetchBoard(Request $request): JsonResponse
    {
        $user = auth()->user();
        $board = Board::where('board_key', $request->key)->first();
        if ($user->cannot('view', $board)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->wishlistService->fetchBoard($request);

        return response()->json($response);
    }

    /**
     * Add board by logged user
     *
     * @param BoardRequest $request
     * @return JsonResponse
     */
    public function addBoard(BoardRequest $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('create', Board::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->wishlistService->addBoard($request->validated());

        return response()->json($response);
    }

    /**
     * Update board by key
     *
     * @param BoardRequest $request
     * @return JsonResponse
     */
    public function updateBoard(BoardRequest $request): JsonResponse
    {
        $user = auth()->user();
        $board = Board::where('board_key', $request->key)->first();
        if ($user->cannot('update', $board)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->wishlistService->updateBoard($request->validated());

        return response()->json($response);
    }

    /**
     * Delete board by ID
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteBoard(Request $request): JsonResponse
    {
        $user = auth()->user();
        $board = Board::where('board_key', $request->key)->first();
        if ($user->cannot('delete', $board)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->wishlistService->deleteBoard($request);

        return response()->json($response);
    }

    /**
     * Change board by ID
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changeBoard(Request $request): JsonResponse
    {
        $user = auth()->user();
        $wishList = Wishlist::where('id', $request->wish_id)->first();
        if ($user->cannot('update', $wishList)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->wishlistService->changeBoard($request);

        return response()->json($response);
    }
}
