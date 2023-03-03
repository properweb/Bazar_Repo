<?php

namespace Modules\Shipping\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Shipping\Entities\Shipping;
use Modules\Shipping\Http\Requests\ShippingRequest;
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
     * @param Request $request
     * @return JsonResponse
     */
    public function fetch(Request $request): JsonResponse
    {
        $response = $this->shippingService->getShippings($request);
        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function details(Request $request): JsonResponse
    {
        $response = $this->shippingService->details($request);
        return response()->json($response);
    }

    /**
     * Store a newly created shipping in storage
     *
     * @param ShippingRequest $request
     * @return JsonResponse
     */
    public function create(ShippingRequest $request): JsonResponse
    {
        $user = auth('sanctum')->user();
        $request->request->add(['user_id' => $request->user_id]);
        $response = $this->shippingService->create($request->validated());

        return response()->json($response);
    }

    public function update(ShippingRequest $request): JsonResponse
    {
        $response = $this->shippingService->update($request->all());
        return response()->json($response);
    }
    public function delete(Request $request): JsonResponse
    {
        $response = $this->shippingService->delete($request->id,$request->user_id);
        return response()->json($response);
    }


}
