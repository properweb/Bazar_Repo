<?php

namespace Modules\Custom\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Product\Entities\Product;
use Modules\Custom\Http\Requests\CustomRequest;
use Modules\Custom\Http\Requests\FileRequest;
use Modules\Custom\Http\Services\CustomService;
use Illuminate\Http\Request;


class CustomController extends Controller
{

    private CustomService $customService;

    public function __construct(CustomService $customService)
    {
        $this->customService = $customService;
    }


    /**
     * Add custom API details
     *
     * @param CustomRequest $request
     * @return JsonResponse
     */
    public function addApi(CustomRequest $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('create', Product::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->customService->addApi($request->validated());

        return response()->json($response);
    }

    /**
     * Import custom website products
     *
     * @param FileRequest $request
     * @return JsonResponse
     */
    public function importProduct(FileRequest $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('create', Product::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->customService->importProduct($request->validated());

        return response()->json($response);
    }

    /**
     * Import custom website products
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function exportProduct(Request $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('create', Product::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->customService->exportProduct($request);

        return response()->json($response);
    }

    /**
     * Update Stock
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateStock(Request $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('create', Product::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->customService->updateStock($request);

        return response()->json($response);
    }

    /**
     * Fetch all custom website
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function fetchCustom(): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('viewAny', Product::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->customService->fetchCustom();

        return response()->json($response);
    }

}
