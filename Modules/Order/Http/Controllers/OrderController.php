<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderReview;
use Modules\Order\Http\Requests\StoreOrderReviewRequest;
use Modules\Order\Http\Requests\OrderRequest;
use Modules\Order\Http\Requests\AcceptRequest;
use Modules\Order\Http\Requests\ChangeRequest;
use Modules\Order\Http\Services\OrderService;
use Modules\Order\Http\Requests\StoreReturnRequest;
use Modules\Cart\Entities\Cart;

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
        if ($user->cannot('viewAny', Order::class)) {
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
    public function shipFrom(AcceptRequest $request): JsonResponse
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

        $response = $this->orderService->shipFrom($request->validated());

        return response()->json($response);
    }

    /**
     * Order accepted by brand
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function processOrder(Request $request): JsonResponse
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

        $response = $this->orderService->processOrder($request->all());

        return response()->json($response);
    }

    /**
     * Order accepted by brand
     *
     * @param AcceptRequest $request
     * @return JsonResponse
     */
    public function acceptA(AcceptRequest $request): JsonResponse
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
        $order = Order::where('id', $request->order_id)->first();
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
     * Accept Deliver order by users
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function orderFulfilled(Request $request): JsonResponse
    {
        $user = auth()->user();
        $order = Order::where('order_number', $request->order_number)->first();

        if ($user->cannot('fulFilled', $order)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->orderService->orderFulfilled($request);

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

    /**
     * store review of a fulfilled order
     *
     * @param StoreOrderReviewRequest $request
     * @return JsonResponse
     */
    public function review(StoreOrderReviewRequest $request): JsonResponse
    {
        $user = auth()->user();
        $order = Order::where('order_number', $request->order_number)->first();

        if ($user->cannot('view', $order)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        if ($order->status != 'fulfilled') {
            return response()->json([
                'res' => false,
                'msg' => 'Order can not be reviewed!',
                'data' => ""
            ]);
        }

        $response = $this->orderService->storeReview($request->validated());

        return response()->json($response);
    }

    /**
     * Get list of return's policies
     *
     * @return JsonResponse
     */
    public function returnPolicies(): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('viewAny', Order::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->orderService->fetchReturnPolicies();

        return response()->json($response);
    }

    /**
     * Get list of return's reasons
     *
     * @return JsonResponse
     */
    public function returnReasons(): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('viewAny', Order::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->orderService->fetchReturnReasons();

        return response()->json($response);
    }

    /**
     * Return accepted by brand
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function processReturn(Request $request): JsonResponse
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

        $response = $this->orderService->processReturn($request->all());

        return response()->json($response);
    }

    /**
     * Initiate a order return in storage
     *
     * @param StoreReturnRequest $request
     * @return JsonResponse
     */
    public function returnOrder(StoreReturnRequest $request): JsonResponse
    {
        $user = auth()->user();
        $order = Order::where('order_number', $request->order_number)->first();

        if ($user->cannot('update', $order)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        if ($request->products) {
            foreach ($request->products as $product) {
                $cartItem = Cart::where('order_id', $order->id)->where('product_id', $product)->first();
                if (!$cartItem) {
                    return response()->json([
                        'res' => false,
                        'msg' => 'Order can not be returned!',
                        'data' => ""
                    ]);
                }
            }
        }

        $response = $this->orderService->createReturnOrder($request->validated());

        return response()->json($response);
    }

    /**
     * Cancel order request by retailer
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function cancelRequest(Request $request): JsonResponse
    {
        $user = auth()->user();
        $order = Order::where('order_number', $request->order_number)->first();

        if ($user->cannot('cancelRequest', $order)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->orderService->cancelRequest($request);

        return response()->json($response);
    }

    /**
     * Add payment method by retailer
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addPayment(Request $request): JsonResponse
    {
        $user = auth()->user();
        $order = Order::where('order_number', $request->order_number)->first();

        if ($user->cannot('cancelRequest', $order)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->orderService->addPayment($request);

        return response()->json($response);
    }

    /**
     * decline order from sent invoice by retailer
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function declineOrder(Request $request): JsonResponse
    {
        $user = auth()->user();
        $order = Order::where('order_number', $request->order_number)->first();

        if ($user->cannot('cancelRequest', $order)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->orderService->declineOrder($request);

        return response()->json($response);
    }
}
