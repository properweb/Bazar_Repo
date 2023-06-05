<?php

namespace Modules\Order\Http\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderReview;
use Modules\Order\Entities\OrderReturn;
use Modules\Order\Entities\ReturnPolicy;
use Modules\Order\Entities\ReturnReason;
use Modules\Order\Entities\ReturnItem;
use Modules\Cart\Entities\Cart;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Retailer\Entities\Retailer;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariation;
use Modules\Wordpress\Entities\Store;
use Modules\Shipping\Entities\Shipping;
use Modules\Invoice\Entities\Invoice;
use Modules\Wordpress\Http\Controllers\WordpressController;
use Modules\Shopify\Http\Controllers\ShopifyController;
use Illuminate\Support\Facades\DB;


class OrderService
{
    protected Order $order;

    protected User $user;

    /**
     * Show all orders by logged user
     *
     * @param object $request
     * @return array
     */
    public function index(object $request): array
    {
        $rOrders = [];
        $orders = '';
        $userId = auth()->user()->id;
        $user = auth()->user();
        $allOrdersCount = '';
        $newOrdersCount = '';
        $unfulfilledOrdersCount = '';
        $fulfilledOrdersCount = '';
        $cancelledOrdersCount = '';
        if ($user) {
            if ($user->role === 'retailer') {
                $retailer = Retailer::where('user_id', $userId)->first();
                $allOrdersCount = Order::where('user_id', $retailer->user_id)->count();
                $newOrdersCount = Order::where('user_id', $retailer->user_id)->where('status', 'new')->count();
                $unfulfilledOrdersCount = Order::where('user_id', $retailer->user_id)->where('status', 'unfulfilled')->count();
                $fulfilledOrdersCount = Order::where('user_id', $retailer->user_id)->where('status', 'fulfilled')->count();
                $cancelledOrdersCount = Order::where('user_id', $retailer->user_id)->where('status', 'cancelled')->count();
                $orders = Order::where('user_id', $retailer->user_id)->orderBy('created_at', 'DESC');
            }
            if ($user->role === 'brand') {
                $brand = Brand::where('user_id', $userId)->first();
                $allOrdersCount = Order::where('brand_id', $brand->user_id)->count();
                $newOrdersCount = Order::where('brand_id', $brand->user_id)->where('status', 'new')->count();
                $unfulfilledOrdersCount = Order::where('brand_id', $brand->user_id)->where('status', 'unfulfilled')->count();
                $fulfilledOrdersCount = Order::where('brand_id', $brand->user_id)->where('status', 'fulfilled')->count();
                $cancelledOrdersCount = Order::where('brand_id', $brand->user_id)->where('status', 'cancelled')->count();
                $orders = Order::where('brand_id', $brand->user_id)->orderBy('created_at', 'DESC');
            }
            $status = strtolower($request->status);
            switch ($status) {
                case 'all':
                    break;
                default:
                    $orders->where('status', $status);
                    break;
            }
            if ($request->search_key && $request->search_key != '' && !in_array($request->search_key, array('undefined', 'null'))) {
                $orders->where('name', 'Like', '%' . $request->search_key . '%');
            }
            $pOrders = $orders->paginate(10);
            if ($pOrders) {
                if ($user->role === 'retailer') {
                    foreach ($pOrders as $order) {
                        $shippingDetailsArr = [];
                        $brand = Brand::where('user_id', $order->brand_id)->first();
                        if ($order->town) {
                            $town = DB::table('cities')->where('id', $order->town)->first();
                            $shippingDetailsArr[] = $town->name;
                        }
                        if ($order->state) {
                            $state = DB::table('states')->where('id', $order->state)->first();
                            $shippingDetailsArr[] = $state->name;
                        }
                        if ($order->country) {
                            $country = DB::table('countries')->where('id', $order->country)->first();
                            $shippingDetailsArr[] = $country->name;
                        }
                        $shippingDetailsStr = implode(',', $shippingDetailsArr);
                        switch ($order->status) {
                            case 'new':
                                $orderStatus = 'open';
                                break;
                            case 'unfulfilled':
                                $orderStatus = 'in progress';
                                break;
                            case 'fulfilled':
                                $orderStatus = 'completed';
                                break;
                            default:
                                $orderStatus = $order->status;
                                break;
                        }
                        $rOrders[] = array(

                            'brand' => $brand->brand_name,
                            'order_number' => $order->order_number,
                            'order_id' => $order->id,
                            'total_amount' => $order->total_amount,
                            'order_date' => date('Y-m-d', strtotime($order->created_at)),
                            'customer_name' => $order->name,
                            'order_status' => $orderStatus,
                            'shipping_date' => $order->shipping_date,
                            'shipping_details' => $shippingDetailsStr,
                        );
                    }
                }
                if ($user->role === 'brand') {
                    foreach ($pOrders as $order) {
                        $rOrders[] = array(
                            'order_number' => $order->order_number,
                            'order_id' => $order->id,
                            'total_amount' => $order->total_amount,
                            'order_date' => date('Y-m-d', strtotime($order->created_at)),
                            'customer_name' => $order->name,
                            'order_status' => $order->status,
                            'shipping_date' => $order->shipping_date,
                        );
                    }
                }
                $data = array(
                    "orders" => $rOrders,
                    "allOrdersCount" => $allOrdersCount,
                    "newOrdersCount" => $newOrdersCount,
                    "unfulfilledOrdersCount" => $unfulfilledOrdersCount,
                    "fulfilledOrdersCount" => $fulfilledOrdersCount,
                    "cancelledOrdersCount" => $cancelledOrdersCount
                );

                return ['res' => true, 'msg' => "", 'data' => $data];
            } else {

                return ['res' => false, 'msg' => "No record found", 'data' => ""];
            }

        } else {

            return ['res' => false, 'msg' => "No record found", 'data' => ""];
        }
    }

    /**
     * Checkout by logged user
     *
     * @param object $request
     * @return array
     */
    public function checkout(object $request): array
    {
        $userId = auth()->user()->id;
        if (empty(Cart::where('user_id', $userId)->where('order_id', null)->first())) {

            return ['res' => false, 'msg' => 'Cart is Empty !', 'data' => ""];
        }
        $response = '';
        $order = new Order();
        $orderData = $request->all();
        $orderData['order_number'] = 'ORD-' . strtoupper(Str::random(10));
        $orderData['user_id'] = $userId;
        $user = User::find($userId);
        $retailer = Retailer::where('user_id', $userId)->first();
        $brand = Brand::where('brand_key', $request->brand_key)->first();
        $orderData['user_email'] = $user->email;
        $orderData['brand_id'] = $brand->user_id;
        $orderData['shipping_date'] = date('Y-m-d', strtotime("+" . $brand->avg_lead_time . " days"));
        $orderData['actualShipDate'] = date('Y-m-d', strtotime("+" . $brand->avg_lead_time . " days"));
        $orderData['sub_total'] = Cart::where('user_id', $userId)->where('order_id', null)->sum('amount');
        $orderData['quantity'] = Cart::where('user_id', $userId)->where('order_id', null)->sum('quantity');
        $orderData['total_amount'] = Cart::where('user_id', $userId)->where('order_id', null)->sum('amount');
        $orderData['status'] = "new";
        if (empty($request->payment_method) && $request->payment_method == '') {

            return ['res' => false, 'msg' => 'Please select payment method', 'data' => ''];

        }
        $orderData['payment_method'] = $request->payment_method;
        $orderData['payment_status'] = 'unpaid';
        if (empty($request->shipping_id) && $request->billing == 0) {

            return ['res' => false, 'msg' => 'Please select Shipping Address', 'data' => ''];

        }

        if ($request->sameAsBilling == 1) {
            $orderData['shipping_name'] = $user->first_name . ' ' . $user->last_name;
            $orderData['shipping_phone'] = $retailer->phone_number;
            $orderData['shipping_country'] = $retailer->country;
            $orderData['shipping_state'] = $retailer->state;
            $orderData['shipping_town'] = $retailer->town;
            $orderData['shipping_zip'] = $retailer->zip;
            $orderData['shipping_street'] = $retailer->address1;
        } else {
            $shipping = Shipping::where('id', $request->shipping_id)->first();
            $orderData['shipping_name'] = $shipping->name;
            $orderData['shipping_country'] = $shipping->country;
            $orderData['shipping_street'] = $shipping->street;
            $orderData['shipping_suite'] = $shipping->suite;
            $orderData['shipping_state'] = $shipping->state;
            $orderData['shipping_town'] = $shipping->town;
            $orderData['shipping_zip'] = $shipping->zip;
            $orderData['shipping_phoneCode'] = $shipping->phoneCode;
            $orderData['shipping_phone'] = $shipping->phone;
        }


        $orderData['name'] = $user->first_name . ' ' . $user->last_name;
        $orderData['phone'] = $retailer->phone_number;
        $orderData['country'] = $retailer->country;
        $orderData['state'] = $retailer->state;
        $orderData['town'] = $retailer->town;
        $orderData['post_code'] = $retailer->zip;
        $orderData['address1'] = $retailer->address1;
//        $orderData['brand_name'] = $brand->brand_name;
//        $orderData['brand_phone'] = $brand->phone_number;
//        $orderData['brand_country'] = $brand->country;
//        $orderData['brand_state'] = $brand->state;
//        $orderData['brand_town'] = $brand->city;
        $order->fill($orderData);
        $order->save();
        if (!empty($order)) {
            Cart::where('user_id', $userId)->where('order_id', null)->update(['order_id' => $order->id]);
            $prdArr = Cart::where('user_id', $userId)->where('order_id', $order->id)->where('brand_id', $brand->user_id)->get();

            $this->syncExternal($prdArr, $brand->user_id);
            $response = ['res' => true, 'msg' => 'Your product successfully placed in order', 'data' => ''];
        }
        return $response;
    }

    /**
     * Update order details
     *
     * @param object $request
     * @return array
     */
    public function update(object $request): array
    {
        $order = Order::find($request->order_id);
        $newCart = $request->items;
        if ($order && $newCart) {
            $cartArr = Cart::where('order_id', $order->id)->get();
            if ($cartArr) {
                foreach ($cartArr as $cItem) {
                    $cartId = $cItem->id;
                    $cartNewQty = $newCart[$cItem->id]['qty'];
                    $quantity = max($cartNewQty, 0);
                    $cartNewAnt = $cartNewQty * $cItem->price;
                    Cart::where('id', $cartId)->update(['quantity' => $quantity, 'amount' => $cartNewAnt]);
                }
            }
            $order->has_discount = (string)$request->is_discount;
            $order->discount_type = $request->disc_amt_type;
            $order->discount = $request->disc_amt;
            $order->shipping_free = (string)$request->ship_free;
            $order->shipping_date = $request->ship_date;
            $order->sub_total = Cart::where('order_id', $order->id)->sum('amount');
            $order->quantity = Cart::where('order_id', $order->id)->sum('quantity');
            $order->total_amount = $order->sub_total;
            $order->save();
        }

        return ['res' => true, 'msg' => "", 'data' => ""];
    }

    /**
     * Sync to imported website
     *
     * @param object $prdArr
     * @param string $brandId
     * @return void
     */
    private function syncExternal(object $prdArr, string $brandId): void
    {
        if (!empty($prdArr)) {
            foreach ($prdArr as $prdCt) {

                switch ($prdCt->type) {
                    case 'OPEN_SIZING':
                        $referenceArr = unserialize($prdCt->reference);
                        if (!empty($referenceArr)) {
                            foreach ($referenceArr as $refK => $refV) {
                                $variantId = $refK;
                                $orderedQty = (int)$refV;
                                $variant = ProductVariation::find($variantId);
                                $stock = (int)$variant->stock - $orderedQty;
                                $variant->stock = $stock;
                                $variant->save();
                            }
                        }
                        break;
                    case 'SINGLE_PRODUCT':
                        if (!empty($prdCt->variant_id)) {
                            $variant = ProductVariation::find($prdCt->variant_id);
                            $stock = (int)$variant->stock - $prdCt->quantity;
                            $variant->stock = $stock;
                            $variant->save();
                            $userCount = ProductVariation::where('product_id', $prdCt->product_id)->count();
                            if ($userCount == 1) {
                                Product::where('id', $prdCt->product_id)->update(array("stock" => $stock));
                            }
                        } else {
                            $product = Product::find($prdCt->product_id);
                            $stock = (int)$product->stock - $prdCt->quantity;
                            $product->stock = $stock;
                            $product->save();
                        }
                        break;
                    default:
                        break;
                }
                if (!empty($prdCt->website)) {
                    $syncs = Store::where('brand_id', $brandId)->where('website', $prdCt->website)->get()->first();
                    if ($syncs) {
                        $types = $syncs->types;
                        if ($types == 'wordpress') {
                            $wordpressController = new WordpressController;
                            $request = new \Illuminate\Http\Request();
                            $request->user_id = $brandId;
                            $request->product_id = $prdCt->product_id;
                            $wordpressController->syncWordpress($request);
                        }

                        if ($types == 'shopify') {

                            $shopifyController = new ShopifyController;
                            $request = new \Illuminate\Http\Request();
                            $request->user_id = $brandId;
                            $request->product_id = $prdCt->product_id;
                            $shopifyController->syncToShopify($request);
                        }
                    }
                }
            }
        }
    }

    /**
     * Order details by order number
     *
     * @param string $orderNumber
     * @return array
     */
    public function show(string $orderNumber): array
    {

        $data = [];
        $order = Order::where('order_number', $orderNumber)->first();
        if (!empty($order)) {
            $cart = Cart::where('order_id', $order->id)->first();
            $brand = Brand::where('user_id', $cart->brand_id)->first();
            $order->display_shipping_date = date('F j,Y', strtotime($order->shipping_date));
            $order->created_date = date('F j,Y', strtotime($order->created_at)) . ' at ' . date('g:i A', strtotime($order->created_at));
            $order->updated_date = date('F j,Y', strtotime($order->updated_at)) . ' at ' . date('g:i A', strtotime($order->updated_at));
            $orderId = $order->id;
            $retailerId = $order->user_id;
            $user = User::find($retailerId);
            $retailer = Retailer::where('user_id', $retailerId)->first();
            if ($order->country) {
                $country = DB::table('countries')->where('id', $order->country)->first();
                $order->country = $country->name;
                $order->country_id = $country->id;
            }
            if ($order->state) {
                $state = DB::table('states')->where('id', $order->state)->first();
                $order->state = $state->name;
                $order->state_id = $state->id;
            }
            if ($order->town) {
                $town = DB::table('cities')->where('id', $order->town)->first();
                $order->town = $town->name;
                $order->town_id = $town->id;
            }
            if ($order->brand_country) {
                $country = DB::table('countries')->where('id', $order->brand_country)->first();
                $order->brand_country_text = $country->name;
            }

            $order->address_str = $order->country . ',' . $order->state . ',' . $order->town . ',' . $order->address1 . ' ' . $order->address2;
            $totalPrice = 0;
            $totalQty = 0;
            $orderItems = Cart::where('brand_id', $brand->user_id)->where('order_id', $orderId)->get();
            if ($orderItems) {
                foreach ($orderItems as $cartItem) {
                    $subTotal = (float)$cartItem->price * (int)$cartItem->quantity;
                    $totalQty += (int)$cartItem->quantity;
                    $totalPrice += $subTotal;
                    $product = Product::where('id', $cartItem->product_id)->first();
                    $cartItem->product_id = $product->id;
                    $cartItem->product_name = $product->name;
                    $cartItem->product_price = (float)$cartItem->price;
                    $cartItem->totalPrice = $subTotal;
                    $cartItem->product_qty = (int)$cartItem->quantity;
                    $cartItem->product_image = $product->featured_image != '' ? $product->featured_image : asset('public/admin/dist/img/logo-image.png');
                }
            }
            $relatedOrders = [];
            $splitOrders = Order::where('parent_id', $orderId)->get();
            if ($splitOrders) {
                foreach ($splitOrders as $sOrder) {
                    $relatedOrders[] = array(
                        "order_id" => $sOrder->id,
                        "order_number" => $sOrder->order_number,
                    );
                }
            }
            if ($order->parent_id != null) {
                $parentOrder = Order::where('id', $order->parent_id)->first();
                if ($parentOrder) {
                    $relatedOrders[] = array(
                        "order_id" => $parentOrder->id,
                        "order_number" => $parentOrder->order_number,
                    );
                }
            }

            if ($order->discount_type == 'amount' && $order->has_discount == 1) {

                $orderTotal = $order->total_amount - $order->discount;
            } else if ($order->discount_type == 'percent' && $order->has_discount == 1) {
                $orderTotal = $order->total_amount - ($order->total_amount * $order->discount / 100);
            } else {
                $orderTotal = $totalPrice;
            }

            $orderReview = OrderReview::where('order_id', $order->id)->get();

            $latestBrandOrder = Order::where('brand_id', $order->brand_id)->whereNotNull('brand_country')->orderBy('created_at', 'DESC')->first();
            $shipFrom = array(
                'name' => $latestBrandOrder->brand_name ?? '',
                'phone' => $latestBrandOrder->brand_phone ?? '',
                'address1' => $latestBrandOrder->brand_address1 ?? '',
                'address2' => $latestBrandOrder->brand_address2 ?? '',
                'town' => $latestBrandOrder->brand_town ?? '',
                'state' => $latestBrandOrder->brand_state ?? '',
                'post_code' => $latestBrandOrder->brand_post_code ?? '',
                'country' => $latestBrandOrder->brand_country ?? '',
            );

            //order returns
            $returnArray = [];
            $orderReturns = OrderReturn::where('order_id', $order->id)->get();
            if ($orderReturns) {
                foreach ($orderReturns as $orderReturn) {

                    $policiesArray = [];
                    $returnPolicies = explode(',',$orderReturn->policies);
                    if ($returnPolicies) {
                        foreach ($returnPolicies as $returnPolicy) {
                            $returnPolicyDetail = ReturnPolicy::find($returnPolicy);
                            $policiesArray[] = $returnPolicyDetail->title;
                        }
                    }
                    $returnItemsArray = [];
                    $returnItems = ReturnItem::where('return_id', $orderReturn->id)->get();
                    if ($returnItems) {
                        foreach ($returnItems as $returnItem) {
                            $cart = Cart::find($returnItem->item_id);
                            $product = Product::find($cart->product_id);
                            $returnReason = ReturnReason::find($returnItem->reason_id);
                            $subTotal = (float)$cart->price * (int)$returnItem->quantity;
                            $returnItemsArray[] = array(
                                "product_id" => $cart->product_id,
                                "product_name" => $cart->product_name,
                                "style_name" => $cart->style_name,
                                "style_group_name" => $cart->style_group_name,
                                "product_image" => $product->featured_image != '' ? $product->featured_image : asset('public/img/logo-image.png'),
                                "quantity" => $returnItem->quantity,
                                "reason" => $returnReason->title,
                                "price" => $cart->price,
                                "sub_total" => $subTotal
                            );
                        }
                    }
                    $orderReturn->items = $returnItemsArray;
                    $returnArray[] = array(
                        "shipping_date" => $orderReturn->shipping_date,
                        "status" => $orderReturn->status,
                        "feedback" => $orderReturn->feedback,
                        "created_at" => date('F j,Y', strtotime($orderReturn->created_at)) . ' at ' . date('g:i A', strtotime($orderReturn->created_at)),
                        "items" => $returnItemsArray,
                        "policies" => $policiesArray
                    );
                }
            }

            $data = array(
                'retailer_name' => $user->first_name . ' ' . $user->last_name,
                'retailer_phone' => $retailer->country_code . ' ' . $retailer->phone_number,
                'brand' => $brand->brand_name,
                'order' => $order,
                'cart' => $orderItems,
                'returns' => $returnArray,
                'total_qty' => $totalQty,
                'total_price' => $totalPrice,
                'orderTotal' => $orderTotal,
                'related_orders' => $relatedOrders,
                'review' => $orderReview,
                'ship_from' => $shipFrom,
            );
        }

        return ['res' => true, 'msg' => "", 'data' => $data];
    }

    /**
     * Create packing list by order
     *
     * @param object $request
     * @return array
     */
    public function packingSlip(object $request): array
    {

        $data = [];
        if ($request->items) {
            $orders = $request->items;
            foreach ($orders as $order) {
                $order = Order::find($order);
                if ($order) {
                    $cart = Cart::where('order_id', $order->id)->first();
                    $brand = Brand::where('user_id', $cart->brand_id)->first();
                    $order->created_date = date('F j,Y', strtotime($order->created_at)) . ' at ' . date('g:i A', strtotime($order->created_at));
                    $order->updated_date = date('F j,Y', strtotime($order->updated_at)) . ' at ' . date('g:i A', strtotime($order->updated_at));
                    $orderId = $order->id;
                    $retailerId = $order->user_id;
                    $user = User::find($retailerId);
                    $retailer = Retailer::where('user_id', $retailerId)->first();
                    if ($order->country) {
                        $country = DB::table('countries')->where('id', $order->country)->first();
                        $order->country = $country->name;
                    }
                    if ($order->state) {
                        $state = DB::table('states')->where('id', $order->state)->first();
                        $order->state = $state->name;
                    }
                    if ($order->town) {
                        $town = DB::table('cities')->where('id', $order->town)->first();
                        $order->town = $town->name;
                    }
                    $cart = Cart::where('brand_id', $brand->user_id)->where('order_id', $orderId)->get();
                    $totalPrice = 0;
                    if ($cart) {
                        $totalQty = 0;
                        foreach ($cart as $cartItem) {
                            $product = Product::where('id', $cartItem->product_id)->first();
                            $subTotal = (float)$cartItem->price * (int)$cartItem->quantity;
                            $totalQty += (int)$cartItem->quantity;
                            $totalPrice += $subTotal;
                            $cartItem->product_id = $product->id;
                            $cartItem->product_name = $product->name;
                            $cartItem->product_price = (float)$cartItem->price;
                            $cartItem->product_qty = (int)$cartItem->quantity;
                            $cartItem->totalPrice = $subTotal;
                            $cartItem->product_image = $product->featured_image != '' ? $product->featured_image : asset('public/admin/dist/img/logo-image.png');
                        }
                    }

                    if ($order->discount_type == 'amount' && $order->has_discount == 1) {

                        $orderTotal = $order->total_amount - $order->discount;
                    } else if ($order->discount_type == 'percent' && $order->has_discount == 1) {
                        $orderTotal = $order->total_amount - ($order->total_amount * $order->discount / 100);
                    } else {
                        $orderTotal = $totalPrice;
                    }

                    $data[] = array(
                        'retailer_name' => $user->first_name . ' ' . $user->last_name,
                        'retailer_phone' => $retailer->country_code . ' ' . $retailer->phone_number,
                        'brand' => $brand->brand_name,
                        'order' => $order,
                        'cart' => $cart,
                        'totalPrice' => $totalPrice,
                        'orderTotal' => $orderTotal
                    );
                }
            }
        }

        return ['res' => true, 'msg' => "", 'data' => $data];
    }

    /**
     * Accept order by brand
     *
     * @param array $request
     * @return array
     */
    public function shipFrom(array $request): array
    {

        $order = Order::where('order_number', $request['ord_no'])->first();
        if (!empty($order)) {

            $order->brand_name = $request['brand_name'];
            $order->brand_phone = $request['brand_phone'];
            $order->brand_country = $request['brand_country'];
            $order->brand_state = $request['brand_state'];
            $order->brand_town = $request['brand_town'];
            $order->brand_post_code = $request['brand_post_code'];
            $order->brand_address1 = $request['brand_address1'];
            $order->brand_address2 = $request['brand_address2'] ?? '';
            $order->shipping_date = $request['ship_date'] ?? '';

            $response = ['res' => true, 'msg' => '', 'data' => ''];
        } else {

            $response = ['res' => false, 'msg' => 'Order is empty', 'data' => ''];
        }

        return $response;
    }

    /**
     * Accept order by brand
     *
     * @param array $request
     * @return array
     */
    public function processOrder(array $request): array
    {

        $order = Order::where('order_number', $request['ord_no'])->first();
        if (!empty($order)) {
            $order->status = 'unfulfilled';
            $order->save();

            $response = ['res' => true, 'msg' => 'Now this order is being processed.', 'data' => ''];
        } else {

            $response = ['res' => false, 'msg' => 'Order is empty', 'data' => ''];
        }

        return $response;
    }

    /**
     * Accept order by brand
     *
     * @param array $request
     * @return array
     */
    public function processReturn(array $request): array
    {

        $order = Order::where('order_number', $request['ord_no'])->first();
        if (!empty($order)) {
            $orderReturn = OrderReturn::where('order_id', $order->id)->first();
            $orderReturn->status = 1;
            $orderReturn->save();

            $response = ['res' => true, 'msg' => 'Now this return is being processed.', 'data' => ''];
        } else {

            $response = ['res' => false, 'msg' => 'Order return is empty', 'data' => ''];
        }

        return $response;
    }

    /**
     * Change order address
     *
     * @param object $request
     * @return array
     */
    public function changeAddress(object $request): array
    {

        $order = Order::where('order_number', $request->ord_no)->first();
        $order->shipping_name = $request->shipping_name;
        $order->shipping_phone = $request->shipping_phone;
        $order->shipping_state = $request->shipping_state;
        $order->shipping_town = $request->shipping_town;
        $order->shipping_zip = $request->shipping_zip;
        $order->shipping_country = $request->shipping_country;
        $order->shipping_street = $request->shipping_street;
        $order->shipping_suite = $request->shipping_suite;
        $order->shipping_phoneCode = $request->shipping_phoneCode;
        $order->save();
        $data = array(
            'name' => $request->shipping_name,
            'phone' => $request->shipping_phone,
            'state' => $request->shipping_state,
            'town' => $request->shipping_town,
            'zip' => $request->shipping_zip,
            'country' => $request->shipping_country,
            'street' => $request->shipping_street,
            'suite' => $request->shipping_suite,
            'phoneCode' => $request->shipping_phoneCode
        );
        Shipping::where('id', $request->shipping_id)->update($data);

        return ['res' => true, 'msg' => "", 'data' => ""];
    }

    /**
     * Change shipping date by order
     *
     * @param object $request
     * @return array
     */
    public function changeDate(object $request): array
    {

        $data = [];
        if ($request->items) {
            $orders = $request->items;
            foreach ($orders as $order) {
                $order = Order::find($order);

                if ($order) {
                    $order->shipping_date = $request->ship_date;
                    $order->save();
                    $retailerId = $order->user_id;
                    $retailerUser = User::find($retailerId);
                    $data = array('email' => $retailerUser->email, 'order_number' => $order->order_number);
                }
            }
        }

        return ['res' => true, 'msg' => "", 'data' => $data];
    }

    /**
     * Split order by order
     *
     * @param object $request
     * @return array
     */
    public function split(object $request): array
    {
        $order = Order::find($request->order_id);
        $newCart = $request->items;
        if ($order && $newCart) {
            $cartArr = Cart::where('id', $order->id)->get();
            if ($cartArr) {
                foreach ($cartArr as $cItem) {
                    $cartId = $cItem->id;
                    $cartNewQty = $cItem->quantity - $newCart[$cItem->id]['qty'];
                    $quantity = max($cartNewQty, 0);

                    $cartNewAnt = $cartNewQty * $cItem->price;
                    Cart::where('id', $cartId)->update(['quantity' => $quantity, 'amount' => $cartNewAnt]);

                    $sharedCItem = $cItem->replicate();
                    $sharedCItem->quantity = $newCart[$cItem->id]['qty'];
                    $sharedCItem->amount = $sharedCItem->price * $sharedCItem->quantity;
                    $sharedCItem->order_id = null;
                    $sharedCItem->save();
                }
            }

            $order->sub_total = Cart::where('order_id', $order->id)->sum('amount');
            $order->quantity = Cart::where('order_id', $order->id)->sum('quantity');
            $order->total_amount = $order->sub_total;
            $order->save();

            $sharedOrder = $order->replicate();
            $sharedOrder->order_number = 'ORD-' . strtoupper(Str::random(10));
            $sharedOrder->parent_id = $order->id;
            $sharedOrder->sub_total = Cart::where('order_id', null)->sum('amount');
            $sharedOrder->quantity = Cart::where('order_id', null)->sum('quantity');
            $sharedOrder->total_amount = $sharedOrder->sub_total;
            $sharedOrder->save();
            Cart::where('user_id', $order->user_id)->where('brand_id', $order->brand_id)->where('order_id', null)->update(['order_id' => $sharedOrder->id]);
        }

        return ['res' => true, 'msg' => "", 'data' => ""];
    }

    /**
     * Cancel order
     *
     * @param object $request
     * @return array
     */
    public function cancel(object $request): array
    {
        $order = Order::find($request->order_id);
        if ($order) {

            $order->status = 'cancelled';
            $order->cancel_reason_title = $request->cancel_reason_title;
            $order->cancel_reason_desc = $request->cancel_reason_desc;
            $order->cancel_date = date('Y-m-d');
            $order->save();
            $brand = Brand::where('user_id', $order->brand_id)->first();
            $prdArr = Cart::where('order_id', $order->id)->get();
            $this->syncExternal($prdArr, $brand->user_id);

        }

        return ['res' => true, 'msg' => "", 'data' => ""];
    }

    /**
     * Export order by csv format
     *
     * @param object $request
     * @return array
     */
    public function csv(object $request): array
    {
        $userId = auth()->user()->id;
        $brand = Brand::where('user_id', $userId)->first();
        $store = str_replace(" ", "", $brand->brand_name);
        $date = date("d-m-y,h:i a");
        $filename = $store . $date;

        $orders = explode(',', $request->order_id);
        $data = "Order No.,SKU,Name,Quantity\n";
        foreach ($orders as $order) {
            $orderDetails = Order::find($order);
            $items = Cart::where('brand_id', $request->brand_id)->where('order_id', $order)->get();
            foreach ($items as $item) {
                $data .= $orderDetails->order_number . "," . $item->product_sku . "," . $item->product_name . "," . $item->quantity . "\n";
            }
        }
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

        return ['res' => true, 'msg' => "", 'data' => $data];
    }

    /**
     * Update billing address by user
     *
     * @param array $request
     * @return array
     */
    public function updateBilling(array $request): array
    {

        $userId = auth()->user()->id;
        $country = $request['country'];
        $state = $request['state'];
        $town = $request['town'];
        $zip = $request['zip'];
        $address1 = $request['address1'];
        $phone = $request['phone'];
        $data = array(
            'country' => $country,
            'phone_number' => $phone,
            'state' => $state,
            'town' => $town,
            'post_code' => $zip,
            'address1' => $address1

        );
        Retailer::where('user_id', $userId)->update($data);

        return [
            'res' => true,
            'msg' => 'Updated Successfully',
            'data' => ''
        ];
    }

    /**
     * Create new order review
     *
     * @param array $reviewData
     * @return array
     */
    public function storeReview(array $reviewData): array
    {
        $userId = auth()->user()->id;
        $order = Order::where('order_number', $reviewData['order_number'])->first();

        $orderReview = OrderReview::where('order_id', $order->id)->first();
        // return error if already reviewed
        if ($orderReview) {
            return [
                'res' => false,
                'msg' => 'You have already reviewed !',
                'data' => ""
            ];
        }
        $reviewData['order_id'] = $order->id;
        $reviewData['status'] = 'reviewed';
        $reviewData['user_id'] = $userId;

        //create review
        $review = new OrderReview();
        $review->fill($reviewData);
        $review->save();

        return [
            'res' => true,
            'msg' => 'Review posted Successfully',
            'data' => $review
        ];
    }

    /**
     * Fetch list of return's policies.
     *
     * @return array
     */
    public function fetchReturnPolicies(): array
    {
        $returnPolicies = ReturnPolicy::where('status', 1)->get();

        $response = ['res' => true, 'msg' => '', 'data' => $returnPolicies];

        return ($response);
    }

    /**
     * Fetch list of return's reasons.
     *
     * @return array
     */
    public function fetchReturnReasons(): array
    {
        $returnReasons = ReturnReason::where('status', 1)->get();

        $response = ['res' => true, 'msg' => '', 'data' => $returnReasons];

        return ($response);
    }

    /**
     * Create new order return
     *
     * @param array $returnData
     * @return array
     */
    public function createReturnOrder(array $returnData): array
    {
        $userId = auth()->user()->id;
        $order = Order::where('order_number', $returnData['order_number'])->first();

        $returnData['order_id'] = $order->id;
        $policiesStr = '';
        if (!empty($returnData['policy_values'])) {
            $policiesStr = implode(',', $returnData["policy_values"]);
        }
        $returnData['policies'] = $policiesStr;
        $returnData['shipping_date'] = date('Y-m-d', strtotime($returnData['shipping_time']));
        //create order return
        $orderReturn = new OrderReturn();
        $orderReturn->fill($returnData);
        $orderReturn->save();

        if (!empty($returnData['products'])) {
            foreach ($returnData['products'] as $product) {
                //create review
                $returnItemData = [];
                $returnItem = new ReturnItem();
                $returnItemData['return_id'] = $orderReturn->id;
                $returnItemData['item_id'] = $product['id'];
                $returnItemData['quantity'] = $product['returned_qty'];
                $returnItemData['reason_id'] = $product['returned_reason'];
                $returnItem->fill($returnItemData);
                $returnItem->save();
            }
        }

        return [
            'res' => true,
            'msg' => 'Return initiated successfully',
            'data' => $orderReturn
        ];
    }

    /**
     * Cancel order request by retailer
     *
     * @param Request $request
     * @return array
     */
    public function cancelRequest(Request $request): array
    {
        $order = Order::where('order_number', $request->order_number)->first();
        if ($order) {
            $order->status = 'cancel requested';
            $order->save();
        }

        return ['res' => true, 'msg' => "Cancel order requested successfully", 'data' => $order];
    }

    /**
     * deliver accept order request by retailer
     *
     * @param Request $request
     * @return array
     */
    public function orderFulfilled(Request $request): array
    {
        $order = Order::where('order_number', $request->order_number)->first();
        if ($order) {
            $order->status = 'fulfilled';
            $order->save();
        }

        return ['res' => true, 'msg' => "Successfully Saved", 'data' => $order];
    }

    /**
     * Add payment method by retailer
     *
     * @param Request $request
     * @return array
     */
    public function addPayment(Request $request): array
    {
        $order = Order::where('order_number', $request->order_number)->first();
        if ($order) {
            $order->payment_method = $request->payment_method;
            $order->payment_status = 'paid';
            $order->status = 'new';
            $order->save();
            $invoice = Invoice::find($order->invoice_id);
            $invoice->status = Invoice::STATUS_CONFIRMED;
            $invoice->save();
        }

        return ['res' => true, 'msg' => "Successfully Saved", 'data' => $order];
    }
}