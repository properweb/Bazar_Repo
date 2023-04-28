<?php

namespace Modules\Analytic\Http\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\ReturnItem;
use Modules\Cart\Entities\Cart;
use Modules\User\Entities\User;
use Modules\Retailer\Entities\Retailer;
use Modules\Product\Entities\Product;
use Modules\Analytic\Entities\Visit;
use Illuminate\Support\Facades\DB;


class AnalyticService
{
    protected Order $order;

    protected User $user;


    /**
     * Show all orders cancel by logged brand
     *
     * @return array
     */
    public function orderCancel(): array
    {

        $userId = auth()->user()->id;
        $orders = [];
        $query = Order::where('brand_id', $userId)->where('status', '=', 'cancelled')->whereDate('created_at', '>=', Carbon::now()->subMonths(3))->get();
        if (!empty($query)) {
            foreach ($query as $order) {

                $store = Retailer::where('user_id', $order->user_id)->first();
                $orders[] = array('order_number' => $order->order_number, 'order_id' => $order->id, 'cancel_date' => date('M d, Y', strtotime($order->cancel_date)), 'order_date' => date('M d, Y', strtotime($order->created_at)), 'customer_name' => $order->name, 'reason' => $order->cancel_reason_title, 'store' => $store->store_name);
            }

            $response = ['res' => true, 'msg' => '', 'data' => $orders];
        } else {
            $response = ['res' => false, 'msg' => 'No record found', 'data' => ''];
        }

        return $response;
    }

    /**
     * Fetch top-selling product by brand
     *
     * @param Request $request
     * @return array
     */
    public function topSelling(Request $request): array
    {
        $userId = auth()->user()->id;
        $topSell = [];
        $productSales = Cart::selectRaw('product_id, SUM(quantity) as sales_count')
            ->where('brand_id', $userId)->where('order_id', '>', 0)
            ->whereBetween('created_at', [$request->from_date, $request->to_date])
            ->groupBy('product_id')->orderBy('sales_count', 'desc')->take(5)->get();
        if (!empty($productSales)) {
            foreach ($productSales as $sale) {
                $totalOrderedQuantity = $sale->sales_count;
                $returnedItems = ReturnItem::selectRaw("SUM(return_items.quantity) as returned_quantity ")
                    ->join('carts', 'carts.id', '=', 'return_items.item_id')
                    ->where('carts.product_id', $sale->product_id)
                    ->whereBetween('return_items.created_at', [$request->from_date, $request->to_date])
                    ->first();
                $totalReturnedQuantity = $returnedItems->returned_quantity ?? 0;
                $itemSellThroughRate = (($totalOrderedQuantity - $totalReturnedQuantity) / $totalOrderedQuantity) * 100;
                $product = Product::where('id', $sale->product_id)->first();

                $topSell[] = array(
                    'product' => $product->name,
                    'totalOrder' => $totalOrderedQuantity,
                    'total_returned' => $totalReturnedQuantity,
                    'sellthrough_rate' => number_format($itemSellThroughRate, 2, '.', ''),
                    );
            }

            $response = ['res' => true, 'msg' => '', 'data' => $topSell];

        } else {

            $response = ['res' => false, 'msg' => '', 'data' => ''];
        }

        return $response;
    }

    /**
     * Total sales between dates
     *
     * @param object $request
     * @return array
     */
    public function totalSales(object $request): array
    {
        $userId = auth()->user()->id;
        $totalSell = [];
        $Sales = Order::selectRaw('count(id) as totalOrder, SUM(total_amount) as totalSales')->where('brand_id', $userId)->whereBetween('created_at', [$request->from_date, $request->to_date])->get();
        if (!empty($Sales)) {
            foreach ($Sales as $sale) {
                $totalSell[] = array('totalOrder' => $sale->totalOrder, 'totalAmount' => $sale->totalSales,);
            }

            $response = ['res' => true, 'msg' => '', 'data' => $totalSell];

        } else {

            $response = ['res' => false, 'msg' => '', 'data' => ''];
        }

        return $response;
    }

    /**
     * Total sales by dates
     *
     * @param object $request
     * @return array
     */
    public function totalSalesDates(object $request): array
    {
        $userId = auth()->user()->id;
        $totalSell = [];
        $groupBy = 'day';
        $Sales = Order::selectRaw("DATE_FORMAT(created_at, '%m/%d') as date, SUM(total_amount) as totalSales")->where('brand_id', $userId)->whereBetween('created_at', [$request->from_date, $request->to_date])->groupBy(DB::raw("DATE_FORMAT(created_at, '%{$groupBy}')"))->get();
        if (!empty($Sales)) {
            foreach ($Sales as $sale) {
                $totalSell[] = array('date' => $sale->date, 'totalAmount' => $sale->totalSales,);
            }

            $response = ['res' => true, 'msg' => '', 'data' => $totalSell];

        } else {

            $response = ['res' => false, 'msg' => '', 'data' => ''];
        }

        return $response;
    }

    /**
     * Add visit by brand shop
     *
     * @param object $requestData
     * @return array
     */
    public function shopVisit(object $requestData): array
    {
        $visit = new Visit;
        $visit->ip_address = $requestData->ip_address;
        $visit->brand_id = $requestData->brand_id;
        $visit->orders = $requestData->orders;
        $visit->save();

        return ['res' => true, 'msg' => "Successfully added", 'data' => ''];
    }

    /**
     * Show traffic by brand
     *
     * @param object $request
     * @return array
     */
    public function traffic(object $request): array
    {
        $userId = auth()->user()->id;
        $totalSell = [];
        $groupBy = '%m/%d';
        $selectedDate = Carbon::parse($request->to_date); // parse the selected date
        $nextDate = $selectedDate->addDay();
        $sales = Visit::selectRaw("DATE_FORMAT(created_at, '%m/%d') as date, SUM(orders) as totalSales,(count(DISTINCT(ip_address))) as counts ")->where('brand_id', $userId)->whereBetween('created_at', [$request->from_date, $nextDate])->groupBy(DB::raw("DATE_FORMAT(created_at, '%{$groupBy}')"))->get();

        if (!empty($sales)) {

            foreach ($sales as $sale) {

                $totalSell[] = array('date' => $sale->date, 'totalSales' => $sale->totalSales, 'totalVisit' => $sale->counts,);
            }

            $response = ['res' => true, 'msg' => '', 'data' => $totalSell];

        } else {

            $response = ['res' => false, 'msg' => '', 'data' => ''];
        }

        return $response;
    }

    /**
     * Total traffic by brand
     *
     * @param object $request
     * @return array
     */
    public function totalTraffic(object $request): array
    {
        $userId = auth()->user()->id;
        $totalSell = [];

        $selectedDate = Carbon::parse($request->to_date); // parse the selected date
        $nextDate = $selectedDate->addDay();
        $sales = Visit::selectRaw("SUM(orders) as totalSales,(count(DISTINCT(ip_address))) as counts ")->where('brand_id', $userId)->whereBetween('created_at', [$request->from_date, $nextDate])->first();
        if (!empty($sales)) {
            $totalSell[] = array(

                'totalSales' => $sales->totalSales, 'totalVisit' => $sales->counts, 'rate' => $sales->totalSales * 100 / $sales->counts

            );

            $response = ['res' => true, 'msg' => '', 'data' => $totalSell];

        } else {

            $response = ['res' => false, 'msg' => '', 'data' => ''];
        }

        return $response;
    }

    /**
     * Fetch all orders with issues
     *
     * @return array
     */
    public function fetchOrderIssues(): array
    {

        $userId = auth()->user()->id;

        $lateShippedOrders = [];
        $lateShipments = Order::where('brand_id', $userId)->whereColumn('shipping_date', '>', 'actualShipDate')->whereDate('created_at', '>=', Carbon::now()->subMonths(3))->get();
        if (!empty($lateShipments)) {
            foreach ($lateShipments as $order) {
                $dateLate = strtotime($order->shipping_date) - strtotime($order->actualShipDate);
                $store = Retailer::where('user_id', $order->user_id)->first();
                $lateShippedOrders[] = array('order_number' => $order->order_number, 'order_id' => $order->id, 'total_amount' => $order->total_amount, 'order_date' => date('M d, Y', strtotime($order->created_at)), 'customer_name' => $order->name, 'expected_shipping_date' => date('M d, Y', strtotime($order->shipping_date)), 'actual_shipping_date' => date('M d, Y', strtotime($order->actualShipDate)), 'date_late' => round($dateLate / (60 * 60 * 24)), 'store' => $store->store_name);
            }
        }

        $cancelledOrders = [];

        $issuedOrders = [];
        $returnOrders = Order::join('returns', 'orders.id', '=', 'returns.order_id')
            ->where('orders.brand_id', $userId)
            ->whereDate('returns.created_at', '>=', Carbon::now()->subMonths(3))
            ->get(['orders.id as order_id', 'orders.user_id as customer_id', 'returns.id as return_id', 'orders.created_at as order_date', 'orders.total_amount', 'orders.quantity as ordered_items', 'orders.order_number']);
        if (!empty($returnOrders)) {
            foreach ($returnOrders as $returnOrder) {
                $returnedItems = ReturnItem::selectRaw("SUM(quantity) as returned_items ")->where('return_id', $returnOrder->return_id)->first();
                $store = Retailer::where('user_id', $returnOrder->customer_id)->first();
                $issuedOrders[] = array(
                    'order_number' => $returnOrder->order_number,
                    'order_date' => date('M d, Y', strtotime($returnOrder->order_date)),
                    'store' => $store->store_name,
                    'total_amount' => $returnOrder->total_amount,
                    'ordered_items' => $returnOrder->ordered_items,
                    'returned_items' => $returnedItems->returned_items,
                );
            }
        }

        $data = array(
            'lateshipped_orders' => $lateShippedOrders,
            'cancelled_orders' => $cancelledOrders,
            'issuedOrders' => $issuedOrders
        );

        return $response = ['res' => true, 'msg' => '', 'data' => $data];
    }

    /**
     * Fetch sell through details
     *
     * @param Request $request
     * @return array
     */
    public function fetchSellThrough(Request $request): array
    {
        $userId = auth()->user()->id;

        $totalOrderedQuantity = 0;
        $totalReturnedQuantity = 0;
        $totalReturnedOrder = 0;
        $sellThroughRate = 0;
        $totalReturnedItems = 0;
        $topReturnedItems = [];
        $returnedReasons = [];
        $returnedComments = [];

        $orderDetails = Order::selectRaw("SUM(quantity) as total_quantity")->where('brand_id', $userId)->first();
        $totalOrderedQuantity = $orderDetails->total_quantity;
        $returnOrders = Order::join('returns', 'orders.id', '=', 'returns.order_id')
            ->where('orders.brand_id', $userId)
            ->whereBetween('returns.created_at', [$request->from_date, $request->to_date])
            ->get(['orders.id as order_id', 'orders.user_id as customer_id', 'returns.id as return_id', 'orders.created_at as order_date', 'orders.total_amount', 'orders.quantity as ordered_items', 'orders.order_number']);

        $totalReturnedOrder = count($returnOrders);

        if (!empty($returnOrders)) {
            foreach ($returnOrders as $returnOrder) {
                $returnedItemQuantity = ReturnItem::selectRaw("SUM(quantity) as returned_items ")->where('return_id', $returnOrder->return_id)->first();
                $totalReturnedQuantity += $returnedItemQuantity->returned_items;
                $returnedItems = ReturnItem::join('carts', 'carts.id', '=', 'return_items.item_id')
                    ->where('return_items.return_id', $returnOrder->return_id)
                    ->get(['carts.product_name as product_name', 'carts.quantity as order_quantity', 'return_items.quantity as returned_quantity']);
                $totalReturnedItems += count($returnedItems);
                if (!empty($returnedItems)) {
                    foreach ($returnedItems as $returnedItem) {
                        $itemSellThroughRate = (($returnedItem->order_quantity - $returnedItem->returned_quantity) / $returnedItem->order_quantity) * 100;
                        $topReturnedItems[] = array(
                            'product_name' => $returnedItem->product_name,
                            'order_quantity' => $returnedItem->order_quantity,
                            'returned_quantity' => $returnedItem->returned_quantity,
                            'sellthrough_rate' => number_format($itemSellThroughRate, 2, '.', ''),
                        );
                        $sort_array[] = $returnedItem->returned_quantity;
                    }
                }
            }
        }

        array_multisort($sort_array, SORT_DESC, $topReturnedItems);

        $sellThroughRate = (($totalOrderedQuantity - $totalReturnedQuantity) / $totalOrderedQuantity) * 100;
        $data = array(
            'total_ordered' => $totalOrderedQuantity,
            'total_returned' => $totalReturnedQuantity,
            'sellthrough_rate' => number_format($sellThroughRate, 2, '.', ''),
            'total_returned_orders' => $totalReturnedOrder,
            'returned_reasons' => $returnedReasons,
            'returned_comments' => $returnedComments,
            'top_returned_items' => $topReturnedItems,
            'avg_returned_items' => $totalReturnedItems/$totalReturnedOrder,
        );

        return ['res' => true, 'msg' => '', 'data' => $data];
    }
}
