<?php

namespace Modules\Retailer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Retailer\Entities\Retailer;
use Modules\Retailer\Http\Requests\StoreRetailerRequest;
use Modules\Retailer\Http\Requests\UpdateRetailerRequest;
use Modules\Retailer\Http\Services\RetailerService;
use DB;


class RetailerController extends Controller
{
    private RetailerService $retailerService;

    public function __construct(RetailerService $retailerService)
    {
        $this->retailerService = $retailerService;
    }

    /**
     * Store a newly created brand in storage
     *
     * @param StoreRetailerRequest $request
     * @return JsonResponse
     */
    public function store(StoreRetailerRequest $request): JsonResponse
    {
        $response = $this->retailerService->store($request->validated());

        return response()->json($response);

    }

    /**
     * Fetch the specified retailer's account details.
     *
     * @param string $retailerKey
     * @return JsonResponse
     */
    public function show(string $retailerKey): JsonResponse
    {
        $user = auth()->user();
        $retailer = Retailer::where('retailer_key', $retailerKey)->first();

        // return error if no retailer found
        if (!$retailer) {
            return [
                'res' => false,
                'msg' => 'Retailer not found !',
                'data' => ""
            ];
        }

        // return error if user not view the retailer
        if ($user->cannot('view', $retailer)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->retailerService->get($retailer->id);

        return response()->json($response);
    }

    /**
     * Update the specified retailer in storage
     *
     * @param UpdateRetailerRequest $request
     * @return JsonResponse
     */
    public function update(UpdateRetailerRequest $request, string $retailerKey): JsonResponse
    {
        $user = auth()->user();
        $retailer = Retailer::where('retailer_key', $retailerKey)->first();

        // return error if user cannot update retailer
        if ($user->cannot('update', $retailer)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->retailerService->update($request->validated());

        return response()->json($response);

    }

}
