<?php

namespace Modules\Wishlist\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Wishlist\Entities\Wishlist;
use Modules\Board\Entities\Board;
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
     * @param Request $request
     * @return JsonResponse
     */
    public function fetch(Request $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('viewAny', Wishlist::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->wishlistService->fetch($request);

        return response()->json($response);
    }

    /**
     * Fetch boards which created by logged user
     *
     * @param Request $request
     * @return JsonResponse
     */

    public function fetchBoards(Request $request): JsonResponse
    {
        $user = auth()->user();

        if ($user->cannot('viewAny', Board::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->wishlistService->fetchBoards($request);

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
        if ($user->cannot('viewBoard', $board)) {
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
     * @param WishlistRequest $request
     * @return JsonResponse
     *
     */

    public function addBoard(WishlistRequest $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('createBoard', Board::class)) {
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
     * @param WishlistRequest $request
     * @return JsonResponse
     */

    public function updateBoard(WishlistRequest $request): JsonResponse
    {
        $user = auth()->user();
        $board = Board::where('board_key', $request->key)->first();
        if ($user->cannot('updateBoard', $board)) {
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
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteBoard(Request $request): JsonResponse
    {
        $user = auth()->user();
        $board = Board::where('board_key', $request->key)->first();
        if ($user->cannot('deleteBoard', $board)) {
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
     * @param Request $request
     * @return JsonResponse
     */
    public function changeBoard(Request $request): JsonResponse
    {
        $user = auth()->user();
        $board = Board::where('board_key', $request->key)->first();
        if ($user->cannot('viewAny', $board)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->wishlistService->changeBoard($request);

        return response()->json($response);
    }

    /**
     * Delete wishlist by ID
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

}
