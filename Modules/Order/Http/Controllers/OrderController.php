<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Order\Entities\Order;
use Modules\Order\Http\Requests\OrderRequest;
use Modules\Order\Http\Requests\AcceptRequest;
use Modules\Order\Http\Requests\ChangeRequest;
use Modules\Order\Http\Services\OrderService;

class OrderController extends Controller
{

    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Show all order for logged user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('viewAny', Order::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->orderService->index($request);

        return response()->json($response);
    }

    /**
     * Add shipping and billing address for order
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkout(Request $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('checkout', Order::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->orderService->checkout($request);

        return response()->json($response);
    }

    /**
     * Update billing address by retailer
     *
     * @param OrderRequest $request
     * @return JsonResponse
     */
    public function updateBilling(OrderRequest $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('update', Order::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->orderService->updateBilling($request->validated());

        return response()->json($response);
    }

    /**
     * Get order details by order number
     *
     * @param string $orderNumber
     * @return JsonResponse
     */
    public function show(string $orderNumber): JsonResponse
    {
        $user = auth()->user();
        $order = Order::where('order_number', $orderNumber)->first();
        if ($user->cannot('view', $order)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->orderService->show($orderNumber);

        return response()->json($response);
    }

    /**
     * Create packing slip by order
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function packingSlip(Request $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('viewAny', Order::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->orderService->packingSlip($request);

        return response()->json($response);
    }

    /**
     * Order accepted by brand
     *
     * @param AcceptRequest $request
     * @return JsonResponse
     */
    public function accept(AcceptRequest $request): JsonResponse
    {
        $user = auth()->user();
        $order = Order::where('order_number', $request->ord_no)->first();
        if ($user->cannot('accept', $order)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->orderService->accept($request->validated());

        return response()->json($response);
    }

    /**
     * Change shipping date of an order
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changeDate(Request $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('update', Order::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->orderService->changeDate($request);

        return response()->json($response);
    }

    /**
     * Change shipping address
     *
     * @param ChangeRequest $request
     * @return JsonResponse
     */
    public function changeAddress(ChangeRequest $request): JsonResponse
    {
        $user = auth()->user();
        $order = Order::where('order_number', $request->ord_no)->first();
        if ($user->cannot('changeAdders', $order)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->orderService->changeAddress($request);

        return response()->json($response);
    }

    /**
     * Update order details by user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $user = auth()->user();
        $order = Order::where('id', $request->order_id)->first();
        if ($user->cannot('updateOrder', $order)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->orderService->update($request);

        return response()->json($response);
    }

    /**
     * Split order by users
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function split(Request $request): JsonResponse
    {
        $user = auth()->user();
        $order = Order::where('id', $request->order_id)->first();
        if ($user->cannot('updateOrder', $order)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->orderService->split($request);

        return response()->json($response);
    }

    /**
     * Cancel order by users
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function cancel(Request $request): JsonResponse
    {
        $user = auth()->user();
        $order = Order::where('order_id', $request->order_id)->first();
        if ($user->cannot('cancel', $order)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->orderService->cancel($request);

        return response()->json($response);
    }

    /**
     * Download order csv
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function csv(Request $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('viewAny', Order::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $this->orderService->csv($request);

        exit;
    }
}
