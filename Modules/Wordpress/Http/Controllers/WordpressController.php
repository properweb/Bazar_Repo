<?php

namespace Modules\Wordpress\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Product\Entities\Product;
use Modules\Wordpress\Http\Requests\WordpressRequest;
use Modules\Wordpress\Http\Services\WordpressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class WordpressController extends Controller
{

    private WordpressService $wordpressService;

    public function __construct(WordpressService $wordpressService)
    {
        $this->wordpressService = $wordpressService;
    }

    /**
     * Get list of brands
     *
     * @param WordpressRequest $request
     * @return JsonResponse
     */
    public function index(WordpressRequest $request): JsonResponse
    {
        $user = auth()->user();

        if ($user->cannot('viewAny', Product::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->wordpressService->create($request->validated());

        return response()->json($response);
    }

    /**
     * Webhook for update
     *
     * @param Request $request
     * @return mixed
     */
    public function webHookUpdate(Request $request): mixed
    {

        $response = $this->wordpressService->webHookUpdate($request);

        return response()->json($response);
    }

    /**
     * Webhook for create
     *
     * @param Request $request
     * @return mixed
     */
    public function webHookCreate(Request $request): mixed
    {

        $response = $this->wordpressService->webHookCreate($request);

        return response()->json($response);
    }

    /**
     * Get info from import products
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function actionInfo(Request $request): JsonResponse
    {
        $user = auth()->user();

        if ($user->cannot('viewAny', Product::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->wordpressService->actionInfo();

        return response()->json($response);
    }

    /**
     * Delete notification by ID
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteNotification(Request $request): JsonResponse
    {
        $user = auth()->user();

        if ($user->cannot('viewAny', Product::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->wordpressService->deleteNotification($request);

        return response()->json($response);
    }


}
