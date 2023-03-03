<?php

namespace Modules\Order\Http\Services;


use Illuminate\Support\Str;
use Modules\Order\Entities\Order;
use Modules\Cart\Entities\Cart;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Retailer\Entities\Retailer;
use Modules\Product\Entities\Products;
use Modules\Product\Entities\ProductVariation;
use Modules\Product\Entities\BrandStore;
use Modules\Shipping\Entities\Shipping;
use Modules\Wordpress\Http\Controllers\WordpressController;
use Modules\Shopify\Http\Controllers\ShopifyController;
use DB;


class OrderService
{
    protected Order $order;

    protected User $user;

    /**
     * @param $request
     * @return array
     */
    public function index($request): array
    {
        $rorders = [];
        $data = [];
        $user = User::find($request->user_id);
        if ($user) {
            if ($user->role === 'retailer') {
                $retailer = Retailer::where('user_id', $request->user_id)->first();
                $allOrdersCount = Order::where('user_id', $retailer->user_id)->count();
                $newOrdersCount = Order::where('user_id', $retailer->user_id)->where('status', 'new')->count();
                $unfulfilledOrdersCount = Order::where('user_id', $retailer->user_id)->where('status', 'unfulfilled')->count();
                $fulfilledOrdersCount = Order::where('user_id', $retailer->user_id)->where('status', 'fulfilled')->count();
                $cancelledOrdersCount = Order::where('user_id', $retailer->user_id)->where('status', 'cancelled')->count();
                $orders = Order::where('user_id', $retailer->user_id);
            }
            if ($user->role === 'brand') {
                $brand = Brand::where('user_id', $request->user_id)->first();
                $allOrdersCount = Order::where('brand_id', $brand->user_id)->count();
                $newOrdersCount = Order::where('brand_id', $brand->user_id)->where('status', 'new')->count();
                $unfulfilledOrdersCount = Order::where('brand_id', $brand->user_id)->where('status', 'unfulfilled')->count();
                $fulfilledOrdersCount = Order::where('brand_id', $brand->user_id)->where('status', 'fulfilled')->count();
                $cancelledOrdersCount = Order::where('brand_id', $brand->user_id)->where('status', 'cancelled')->count();
                $orders = Order::where('brand_id', $brand->user_id);
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
            $porders = $orders->paginate(10);
            if ($porders) {
                if ($user->role === 'retailer') {
                    foreach ($porders as $order) {
                        $shippingDetailsArr = [];
                        $cart = Cart::where('user_id', $order->user_id)->where('order_id', $order->id)->get();
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




                        $rorders[] = array(

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
                    foreach ($porders as $order) {
                        $rorders[] = array(
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
                    "orders" => $rorders,
                    "allOrdersCount" => $allOrdersCount,
                    "newOrdersCount" => $newOrdersCount,
                    "unfulfilledOrdersCount" => $unfulfilledOrdersCount,
                    "fulfilledOrdersCount" => $fulfilledOrdersCount,
                    "cancelledOrdersCount" => $cancelledOrdersCount
                );
                $response = ['res' => true, 'msg' => "", 'data' => $data];
                return $response;
            } else {
                $response = ['res' => false, 'msg' => "No record found", 'data' => ""];
                return $response;
            }


        } else {
            $response = ['res' => false, 'msg' => "No record found", 'data' => ""];
            return $response;
        }


    }


    /**
     * @param $request
     * @return array
     */
    public function checkout($request): array
    {


        if (empty(Cart::where('user_id', $request->user_id)->where('order_id', null)->first())) {
            $response = ['res' => false, 'msg' => 'Cart is Empty !', 'data' => ""];
            return $response;
        }

        $order = new Order();
        $orderData = $request->all();
        $orderData['order_number'] = 'ORD-' . strtoupper(Str::random(10));
        $orderData['user_id'] = $request->user_id;
        $user = User::find($request->user_id);
        $retailer = Retailer::where('user_id', $request->user_id)->first();
        $brand = Brand::where('brand_key', $request->brand_key)->first();
        $orderData['user_email'] = $user->email;
        $orderData['brand_id'] = $brand->user_id;
        $orderData['shipping_date'] = date('Y-m-d', strtotime("+" . $brand->avg_lead_time . " days"));

        $orderData['sub_total'] = Cart::where('user_id', $request->user_id)->where('order_id', null)->sum('amount');
        $orderData['quantity'] = Cart::where('user_id', $request->user_id)->where('order_id', null)->sum('quantity');
        $orderData['total_amount'] = Cart::where('user_id', $request->user_id)->where('order_id', null)->sum('amount');

        $orderData['status'] = "new";
        $orderData['payment_method'] = 'cod';
        $orderData['payment_status'] = 'Unpaid';
        if (empty($request->shipping_id)) {
            $response = ['res' => false, 'msg' => 'Please select Shipping Address', 'data' => ''];

        }
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

        $orderData['name'] = $user->first_name . ' ' . $user->last_name;
        $orderData['phone'] = $retailer->phone_number;
        $orderData['country'] = $retailer->country;
        $orderData['state'] = $retailer->state;
        $orderData['town'] = $retailer->town;
        $orderData['post_code'] = $retailer->zip;
        $orderData['address1'] = $retailer->address1;

        $orderData['brand_name'] = $brand->brand_name;
        $orderData['brand_phone'] = $brand->phone_number;
        $orderData['brand_country'] = $brand->country;
        $orderData['brand_state'] = $brand->state;
        $orderData['brand_town'] = $brand->city;
        $order->fill($orderData);
        $status = $order->save();
        if ($order) {


            Cart::where('user_id', $request->user_id)->where('order_id', null)->update(['order_id' => $order->id]);

            $prdctArr = Cart::where('user_id', $request->user_id)->where('order_id', $order->id)->where('brand_id', $brand->user_id)->get();

            $this->syncExternal($prdctArr, $brand->user_id);

            $brandArr = Cart::where('user_id', $request->user_id)->where('order_id', $order->id)->groupBy('brand_id')->get()->toArray();
            if (!empty($brandArr)) {
                foreach ($brandArr as $brandk => $brandv) {
                    $brand = Brand::where('user_id', $brandv['brand_id'])->first();
                    $cartArr[$brandk]['brand_id'] = $brand->user_id;
                    $cartArr[$brandk]['brand_name'] = $brand->brand_name;
                    $cartArr[$brandk]['brand_logo'] = $brand->logo_image != '' ? asset('public') . '/' . $brand->logo_image : asset('public/img/logo-image.png');
                    $prdctArr = Cart::where('user_id', $request->user_id)->where('order_id', $order->id)->where('brand_id', $brandv['brand_id'])->get()->toArray();
                    if (!empty($prdctArr)) {
                        foreach ($prdctArr as $prdctk => $prdctv) {
                            $product = Products::where('id', $prdctv['product_id'])->first();
                            $cartArr[$brandk]['products'][$prdctk]['id'] = $prdctv['id'];
                            $cartArr[$brandk]['products'][$prdctk]['product_id'] = $product->id;
                            $cartArr[$brandk]['products'][$prdctk]['product_name'] = $product->name;
                            $cartArr[$brandk]['products'][$prdctk]['product_price'] = (float)$prdctv['price'];
                            $cartArr[$brandk]['products'][$prdctk]['product_qty'] = (int)$prdctv['quantity'];
                            $cartArr[$brandk]['products'][$prdctk]['product_image'] = $product->featured_image != '' ? $product->featured_image : asset('public/admin/dist/img/logo-image.png');
                        }
                    }
                }
            }
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
            $oprdctArr = [];
            $ocartArr = Cart::where('user_id', $request->user_id)->where('order_id', $order->id)->where('brand_id', $brandv['brand_id'])->get()->toArray();
            $ototalPrice = 0;
            $ototalQty = 0;
            if (!empty($ocartArr)) {
                foreach ($ocartArr as $prdctk => $prdctv) {
                    $sub_total = (float)$prdctv['price'] * (int)$prdctv['quantity'];
                    $ototalQty += (int)$prdctv['quantity'];
                    $ototalPrice += $sub_total;
                    $oprdctArr[] = array(
                        "sku" => $prdctv['product_sku'],
                        "name" => $prdctv['product_name'],
                        "desc" => $prdctv['style_name'],
                        "qty" => (int)$prdctv['quantity'],
                        "price" => (float)$prdctv['price'],
                        "subtotal" => $sub_total,
                    );
                }
            }
            $data = array(
                'order_det' => $order,
                'cart_det' => $cartArr,
            );
            $response = ['res' => true, 'msg' => 'Your product successfully placed in order', 'data' => $data];
        }
        return $response;

    }

    /**
     * @param $request
     * @return array
     */
    public function show($request): array
    {

        $orders = [];
        $data = [];
        $orderNumber = $request->order_number;
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
            $order->address_str = $order->country . ',' . $order->state . ',' . $order->town . ',' . $order->address1 . ' ' . $order->address2;
            $total_price = 0;
            $total_qty = 0;
            $cart = Cart::where('brand_id', $brand->user_id)->where('order_id', $orderId)->get();
            if ($cart) {
                foreach ($cart as $cartItem) {
                    $sub_total = (float)$cartItem->price * (int)$cartItem->quantity;
                    $total_qty += (int)$cartItem->quantity;
                    $total_price += $sub_total;
                    $product = Products::where('id', $cartItem->product_id)->first();
                    $cartItem->product_id = $product->id;
                    $cartItem->product_name = $product->name;
                    $cartItem->product_price = (float)$cartItem->price;
                    $cartItem->product_qty = (int)$cartItem->quantity;
                    $cartItem->product_image = $product->featured_image != '' ? $product->featured_image : asset('public/admin/dist/img/logo-image.png');
                }
            }
            $related_orders = [];
            $splited_orders = Order::where('parent_id', $orderId)->get();
            if ($splited_orders) {
                foreach ($splited_orders as $sorder) {
                    $related_orders[] = array(
                        "order_id" => $sorder->id,
                        "order_number" => $sorder->order_number,
                    );
                }
            }
            if ($order->parent_id != null) {
                $parent_order = Order::where('id', $order->parent_id)->first();
                if ($parent_order) {
                    $related_orders[] = array(
                        "order_id" => $parent_order->id,
                        "order_number" => $parent_order->order_number,
                    );
                }
            }


            $data = array(
                'retailer_name' => $user->first_name . ' ' . $user->last_name,
                'retailer_phone' => $retailer->country_code . ' ' . $retailer->phone_number,
                'brand' => $brand->brand_name,
                'order' => $order,
                'cart' => $cart,
                'total_qty' => $total_qty,
                'total_price' => $total_price,
                'related_orders' => $related_orders
            );
        }

        $response = ['res' => true, 'msg' => "", 'data' => $data];
        return $response;


    }


    /**
     * @param $request
     * @return array
     */

    public function packingSlip($request): array
    {
        $orders = [];
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
                    if ($cart) {
                        foreach ($cart as $cartItem) {
                            $product = Products::where('id', $cartItem->product_id)->first();
                            $cartItem->product_id = $product->id;
                            $cartItem->product_name = $product->name;
                            $cartItem->product_price = (float)$cartItem->price;
                            $cartItem->product_qty = (int)$cartItem->quantity;
                            $cartItem->product_image = $product->featured_image != '' ? $product->featured_image : asset('public/admin/dist/img/logo-image.png');
                        }
                    }
                    $data[] = array(
                        'retailer_name' => $user->first_name . ' ' . $user->last_name,
                        'retailer_phone' => $retailer->country_code . ' ' . $retailer->phone_number,
                        'brand' => $brand->brand_name,
                        'order' => $order,
                        'cart' => $cart,
                    );
                }
            }
        }

        $response = ['res' => true, 'msg' => "", 'data' => $data];
        return $response;
    }

    /**
     * @param $request
     * @return array
     */

    public function accept($request): array
    {



        $order = Order::where('order_number', $request['ord_no'])->where('brand_id', $request['user_id'])->first();
        if (!empty($order)) {

        $order->brand_name = $request['brand_name'];
        $order->brand_phone = $request['brand_phone'];
        $order->brand_country = $request['brand_country'];
        $order->brand_state = $request['brand_state'];
        $order->brand_town = $request['brand_town'];
        $order->brand_post_code = $request['brand_post_code'];
        $order->brand_address1 = $request['brand_address1'];
        $order->brand_address2 = $request['brand_address2'];
        $order->shipping_date = $request['ship_date'];
        $order->status = 'unfulfilled';
        $order->save();
        $retailerId = $order->user_id;
        $retailerUser = User::find($retailerId);
        $brand = Brand::where('user_id', $order->brand_id)->first();
        $msg = "Your orders with " . $brand->brand_name . " having order number <strong>#" . $order->order_number . "</strong> has been processing.";
        $data = array('email' => $retailerUser->email, 'order_number' => $order->order_number);

        $data = [];


        $response = ['res' => true, 'msg' => '', 'data' => $data];
    }
        else
        {
            $response = ['res' => false, 'msg' => 'Order is empty', 'data' => ''];
        }

        return $response;
    }

    /**
     * @param $request
     * @return array
     */

    public function changeAddress($request): array
    {

        $order = Order::where('order_number', $request->ord_no)->first();
        $order->name = $request->name;
        $order->phone = $request->phone;
        $order->address1 = $request->address1;
        $order->address2 = $request->address2;
        $order->state = $request->state;
        $order->town = $request->town;
        $order->post_code = $request->post_code;
        $order->country = $request->country;
        $order->save();


        $response = ['res' => true, 'msg' => "", 'data' => ""];
        return $response;
    }

    /**
     * @param $request
     * @return array
     */

    public function changeDate($request): array
    {
        $orders = [];
        $data = [];
        if ($request->items) {
            $orders = $request->items;
            foreach ($orders as $order) {
                $order = Order::find($order);

                if ($order) {
                    $order->shipping_date = $request->ship_date;
                    $order->save();
                    $brand = Brand::where('user_id', $order->brand_id)->first();
                    $retailerId = $order->user_id;
                    $retailerUser = User::find($retailerId);
                    $msg = "Your order's ship date with " . $brand->brand_name . " having order number <strong>#" . $order->order_number . "</strong> has been changed to " . $request->ship_date;
                    $data = array('email' => $retailerUser->email, 'order_number' => $order->order_number);
                }
            }
        }


        $response = ['res' => true, 'msg' => "", 'data' => $data];
        return $response;
    }

    /**
     * @param $request
     * @return array
     */
    public function update($request): array
    {
        $order = Order::find($request->order_id);
        $newCart = $request->items;

        if ($order && $newCart) {
            $cartArr = Cart::where('order_id', $order->id)->get();
            if ($cartArr) {
                foreach ($cartArr as $citem) {
                    $cartId = $citem->id;
                    $cartNewQty = $newCart[$citem->id]['qty'];
                    $quantity = $cartNewQty < 0 ? 0 : $cartNewQty;
                    $cartNewAmnt = $cartNewQty * $citem->price;
                    Cart::where('id', $cartId)->update(['quantity' => $quantity, 'amount' => $cartNewAmnt]);
                }
            }

            $order->has_discount = (string)$request->is_discount;
            $order->discount_type = $request->disc_amt_type;
            $order->discount = $request->disc_amt;
            $order->shipping_free = (string)$request->ship_free;
            $order->shipping_date = $request->ship_date;
            $order->sub_total = Cart::where('user_id', $order->user_id)->where('brand_id', $order->brand_id)->where('order_id', $order->id)->sum('amount');
            $order->quantity = Cart::where('user_id', $order->user_id)->where('brand_id', $order->brand_id)->where('order_id', $order->id)->sum('quantity');
            $order->total_amount = $order->sub_total;
            $order->save();
        }
        $response = ['res' => true, 'msg' => "", 'data' => ""];
        return $response;
    }

    /**
     * @param $request
     * @return array
     */
    public function split($request): array
    {
        $order = Order::find($request->order_id);
        $newCart = $request->items;

        if ($order && $newCart) {
            $cartArr = Cart::where('order_id', $order->id)->get();
            if ($cartArr) {
                foreach ($cartArr as $citem) {
                    $cartId = $citem->id;
                    $cartNewQty = $citem->quantity - $newCart[$citem->id]['qty'];
                    $quantity = $cartNewQty < 0 ? 0 : $cartNewQty;

                    $cartNewAmnt = $cartNewQty * $citem->price;
                    Cart::where('id', $cartId)->update(['quantity' => $quantity, 'amount' => $cartNewAmnt]);

                    $sharedCItem = $citem->replicate();
                    $sharedCItem->quantity = $newCart[$citem->id]['qty'];
                    $sharedCItem->amount = $sharedCItem->price * $sharedCItem->quantity;
                    $sharedCItem->order_id = null;
                    $sharedCItem->save();
                }
            }

            $order->sub_total = Cart::where('user_id', $order->user_id)->where('brand_id', $order->brand_id)->where('order_id', $order->id)->sum('amount');
            $order->quantity = Cart::where('user_id', $order->user_id)->where('brand_id', $order->brand_id)->where('order_id', $order->id)->sum('quantity');
            $order->total_amount = $order->sub_total;
            $order->save();


            $sharedOrder = $order->replicate();
            $sharedOrder->order_number = 'ORD-' . strtoupper(Str::random(10));
            $sharedOrder->parent_id = $order->id;
            $sharedOrder->sub_total = Cart::where('user_id', $order->user_id)->where('brand_id', $order->brand_id)->where('order_id', null)->sum('amount');
            $sharedOrder->quantity = Cart::where('user_id', $order->user_id)->where('brand_id', $order->brand_id)->where('order_id', null)->sum('quantity');
            $sharedOrder->total_amount = $sharedOrder->sub_total;
            $status = $sharedOrder->save();
            Cart::where('user_id', $order->user_id)->where('brand_id', $order->brand_id)->where('order_id', null)->update(['order_id' => $sharedOrder->id]);
        }
        $response = ['res' => true, 'msg' => "", 'data' => ""];
        return $response;
    }

    /**
     * @param $request
     * @return array
     */
    public function cancel($request): array
    {
        $order = Order::find($request->order_id);
        if ($order) {

            $order->status = 'cancelled';
            $order->cancel_reason_title = $request->cancel_reason_title;
            $order->cancel_reason_desc = $request->cancel_reason_desc;
            $order->save();
            $brand = Brand::where('user_id', $order->brand_id)->first();
            $prdctArr = Cart::where('order_id', $order->id)->get();
            $this->syncExternal($prdctArr, $brand->user_id);

            $retailerId = $order->user_id;
            $retailerUser = User::find($retailerId);
            $msg = "Your order with " . $brand->brand_name . " having order number <strong>#" . $order->order_number . "</strong> has been cancelled.<br>";
            $msg .= "<strong>Reason for cancelling</strong><br>";
            $msg .= $request->cancel_reason_title . "<br>";
            $msg .= $request->cancel_reason_desc . "<br>";
            $data = array('email' => $retailerUser->email, 'order_number' => $order->order_number);
        }
        $response = ['res' => true, 'msg' => "", 'data' => ""];
        return $response;
    }

    public function csv($request): array
    {
        $brand = Brand::where('user_id', $request->brand_id)->first();
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
        $response = ['res' => true, 'msg' => "", 'data' => $data];
        return $response;
    }


    /**
     * @param $prdctArr
     * @param $brandId
     * @return bool
     */
    private function syncExternal($prdctArr, $brandId)
    {

        $result_array = array();
        if (!empty($prdctArr)) {
            foreach ($prdctArr as $prdct) {

                switch ($prdct->type) {
                    case 'OPEN_SIZING':
                        $referenceArr = unserialize($prdct->reference);
                        if (!empty($referenceArr)) {
                            foreach ($referenceArr as $refk => $refv) {
                                $variantId = $refk;
                                $orderedQty = (int)$refv;
                                $variant = ProductVariation::find($variantId);
                                $stock = (int)$variant->stock - $orderedQty;
                                $variant->stock = $stock;
                                $variant->save();
                            }
                        }
                        break;
                    case 'SINGLE_PRODUCT':
                        if (!empty($prdct->variant_id)) {
                            $variant = ProductVariation::find($prdct->variant_id);
                            $stock = (int)$variant->stock - $prdct->quantity;
                            $variant->stock = $stock;
                            $variant->save();
                            $userCount = ProductVariation::where('product_id', $prdct->product_id)->count();
                            if ($userCount == 1) {
                                Products::where('id', $prdct->product_id)->update(array("stock" => $stock));
                            }
                        } else {
                            $product = Products::find($prdct->product_id);
                            $stock = (int)$product->stock - $prdct->quantity;
                            $product->stock = $stock;
                            $product->save();
                        }
                        break;
                    default:
                        break;
                }
                $productDetails = Products::find($prdct->product_id);
                $syncs = BrandStore::where('brand_id', $brandId)->where('website', $product->website)->get()->first();
                if ($syncs) {
                    $types = $syncs->types;
                    if ($types == 'wordpress') {
                        $wordpressController = new WordpressController;
                        $request = new \Illuminate\Http\Request();
                        $request->user_id = $brandId;
                        $request->product_id = $prdct->product_id;
                        $wordpressController->syncWordpress($request);
                    }

                    if ($types == 'shopify') {

                        $shopifyController = new ShopifyController;
                        $request = new \Illuminate\Http\Request();
                        $request->user_id = $brandId;
                        $request->product_id = $prdct->product_id;
                        $shopifyController->syncToShopify($request);
                    }
                }
            }
        }
        return true;
    }

    public function updatebilling(array $request): array
    {

        $userId = $request['user_id'];
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

        $response = [
            'res' => true,
            'msg' => 'Updated Successfully',
            'data' => ''
        ];

        return $response;
    }


}
