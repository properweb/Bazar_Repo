<?php

namespace Modules\Shipping\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Shipping\Entities\Shipping;
use Modules\Shipping\Http\Requests\StoreShippingRequest;
use Modules\Shipping\Http\Services\ShippingService;
use Illuminate\Http\Request;


class ShippingController extends Controller
{

    private ShippingService $shippingService;

    public function __construct(ShippingService $shippingService)
    {
        $this->shippingService = $shippingService;
    }

    /**
     * Fetch all shipping address respected user
     *
     * @return JsonResponse
     */
    public function fetch(): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('viewAny', Shipping::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->shippingService->getShipping();

        return response()->json($response);
    }

    /**
     * Fetch shipping details respected shipping ID
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function details(Request $request): JsonResponse
    {
        $user = auth()->user();
        $shipping = Shipping::where('id', $request->id)->first();
        if ($user->cannot('view', $shipping)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->shippingService->details($request);

        return response()->json($response);
    }

    /**
     * User can add shipping
     *
     * @param StoreShippingRequest $request
     * @return JsonResponse
     */
    public function create(StoreShippingRequest $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('create', Shipping::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->shippingService->create($request->validated());

        return response()->json($response);
    }

    /**
     * Update shipping details by ID
     * @param StoreShippingRequest $request
     * @return JsonResponse
     */

    public function update(StoreShippingRequest $request): JsonResponse
    {
        $user = auth()->user();
        $shipping = Shipping::where('id', $request->id)->first();
        if ($user->cannot('update', $shipping)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->shippingService->update($request->validated());

        return response()->json($response);
    }

    /**
     * Delete shipping by ID
     *
     * @param Request $request
     * @return JsonResponse
     */

    public function delete(Request $request): JsonResponse
    {
        $user = auth()->user();
        $shipping = Shipping::where('id', $request->id)->first();
        if ($user->cannot('delete', $shipping)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->shippingService->delete($request->id);

        return response()->json($response);
    }


}
