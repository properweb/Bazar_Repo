<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\User\Entities\User;
use Modules\Order\Entities\Order;
use Modules\Order\Http\Requests\OrderRequest;
use Modules\Order\Http\Requests\AcceptRequest;
use Modules\Order\Http\Requests\ChangeRequest;
use Modules\Order\Http\Services\OrderService;
use Illuminate\Http\Request;


class OrderController extends Controller
{

    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();

        $response = $this->orderService->index($request);

        return response()->json($response);
    }
    /**
     * Store a newly created order in storage
     *
     * @param OrderRequest $request
     * @return JsonResponse
     */
    public function checkout(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();

        $response = $this->orderService->checkout($request);

        return response()->json($response);
    }

    /**
     * @param OrderRequest $request
     * @return JsonResponse
     */
    public function UpdateBilling(OrderRequest $request): JsonResponse
    {
        $user = auth('sanctum')->user();
        $request->request->add(['user_id' => $request->user_id]);
        $response = $this->orderService->updatebilling($request->validated());

        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();

        $response = $this->orderService->show($request);

        return response()->json($response);
    }



    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function packingSlip(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();

        $response = $this->orderService->packingSlip($request);

        return response()->json($response);
    }

    /**
     * @param AcceptRequest $request
     * @return JsonResponse
     */
    public function accept(AcceptRequest $request): JsonResponse
    {
        $user = auth('sanctum')->user();
       
        $response = $this->orderService->accept($request->validated());

        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function changeDate(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();

        $response = $this->orderService->changeDate($request);

        return response()->json($response);
    }

    /**
     * @param ChangeRequest $request
     * @return JsonResponse
     */
    public function changeAddress(ChangeRequest $request): JsonResponse
    {
        $user = auth('sanctum')->user();

        $response = $this->orderService->changeAddress($request->validated());

        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();

        $response = $this->orderService->update($request);

        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function split(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();

        $response = $this->orderService->split($request);

        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function cancel(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();

        $response = $this->orderService->cancel($request);

        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function csv(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();

        $response = $this->orderService->csv($request);

        exit;
    }

}
