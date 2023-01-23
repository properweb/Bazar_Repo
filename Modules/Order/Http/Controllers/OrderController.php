<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Order\Entities\Order;
use Modules\Cart\Entities\Cart;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Retailer\Entities\Retailer;
use Modules\Product\Entities\Products;
use Modules\Product\Entities\ProductVariation;
use Modules\Wordpress\Http\Controllers\WordpressController;
use Modules\Shopify\Http\Controllers\ShopifyController;
use Modules\Product\Entities\BrandStore;
use DB;

class OrderController extends Controller
{

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $data = [];
        $validator = Validator::make($request->all(), [
            'name' => 'string|required',
            'address1' => 'string|required',
            'address2' => 'string|nullable',
            'phone' => 'numeric|required',
            'post_code' => 'string|nullable',
        ]);
        if ($validator->fails()) {
            $response = ['res' => false, 'msg' => $validator->errors()->first(), 'data' => ""];
        } else {

            if (empty(Cart::where('user_id', $request->user_id)->where('order_id', null)->first())) {
                $response = ['res' => false, 'msg' => 'Cart is Empty !', 'data' => ""];
                return response()->json($response);
            }

            $order = new Order();
            $orderData = $request->all();
            $orderData['order_number'] = 'ORD-' . strtoupper(Str::random(10));
            $orderData['user_id'] = $request->user_id;
            $user = User::find($request->user_id);
            $retailer = Retailer::where('user_id', $request->user_id)->first();
            $brand = Brand::where('brand_key', $request->brand_key)->first();
            $brand_user = User::find($brand->user_id);
            $orderData['user_email'] = $user->email;
            $orderData['brand_id'] = $brand->user_id;
            $orderData['shipping_date'] = date('Y-m-d', strtotime("+" . $brand->avg_lead_time . " days"));

            $orderData['sub_total'] = Cart::where('user_id', $request->user_id)->where('order_id', null)->sum('amount');
            $orderData['quantity'] = Cart::where('user_id', $request->user_id)->where('order_id', null)->sum('quantity');
            $orderData['total_amount'] = Cart::where('user_id', $request->user_id)->where('order_id', null)->sum('amount');

            $orderData['status'] = "new";
            $orderData['payment_method'] = 'cod';
            $orderData['payment_status'] = 'Unpaid';
            $order->fill($orderData);
            $status = $order->save();
            if ($order) {
                $user_name = $user->first_name . " " . $user->last_name;
                $user_email = $user->email;
                $store_name = $retailer->store_name;

                Cart::where('user_id', $request->user_id)->where('order_id', null)->update(['order_id' => $order->id]);

                $prdct_arr = Cart::where('user_id', $request->user_id)->where('order_id', $order->id)->where('brand_id', $brand->user_id)->get();

                $this->syncExternal($prdct_arr, $brand->user_id);

                $brandArr = Cart::where('user_id', $request->user_id)->where('order_id', $order->id)->groupBy('brand_id')->get()->toArray();
                if (!empty($brandArr)) {
                    foreach ($brandArr as $brandk => $brandv) {
                        $brand = Brand::where('user_id', $brandv['brand_id'])->first();
                        $cart_arr[$brandk]['brand_id'] = $brand->user_id;
                        $cart_arr[$brandk]['brand_name'] = $brand->brand_name;
                        $cart_arr[$brandk]['brand_logo'] = $brand->logo_image != '' ? asset('public') . '/' . $brand->logo_image : asset('public/img/logo-image.png');
                        $prdct_arr = Cart::where('user_id', $request->user_id)->where('order_id', $order->id)->where('brand_id', $brandv['brand_id'])->get()->toArray();
                        if (!empty($prdct_arr)) {
                            foreach ($prdct_arr as $prdctk => $prdctv) {
                                $product = Products::where('id', $prdctv['product_id'])->first();
                                $cart_arr[$brandk]['products'][$prdctk]['id'] = $prdctv['id'];
                                $cart_arr[$brandk]['products'][$prdctk]['product_id'] = $product->id;
                                $cart_arr[$brandk]['products'][$prdctk]['product_name'] = $product->name;
                                $cart_arr[$brandk]['products'][$prdctk]['product_price'] = (float)$prdctv['price'];
                                $cart_arr[$brandk]['products'][$prdctk]['product_qty'] = (int)$prdctv['quantity'];
                                $cart_arr[$brandk]['products'][$prdctk]['product_image'] = $product->featured_image != '' ? $product->featured_image : asset('public/admin/dist/img/logo-image.png');
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
                $oprdct_arr = [];
                $ocart_arr = Cart::where('user_id', $request->user_id)->where('order_id', $order->id)->where('brand_id', $brandv['brand_id'])->get()->toArray();
                $ototal_price = 0;
                $ototal_qty = 0;
                if (!empty($ocart_arr)) {
                    foreach ($ocart_arr as $prdctk => $prdctv) {
                        $sub_total = (float)$prdctv['price'] * (int)$prdctv['quantity'];
                        $ototal_qty += (int)$prdctv['quantity'];
                        $ototal_price += $sub_total;
                        $oprdct_arr[] = array(
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
                    'cart_det' => $cart_arr,
                );
                $response = ['res' => true, 'msg' => 'Your product successfully placed in order', 'data' => $data];
            }
            return response()->json($response);
        }
    }
    
    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $rorders = [];
        $data = [];
        $brand = Brand::where('user_id', $request->user_id)->first();
        if ($brand) {
            $allOrdersCount = Order::where('brand_id', $brand->user_id)->count();
            $newOrdersCount = Order::where('brand_id', $brand->user_id)->where('status', 'new')->count();
            $unfulfilledOrdersCount = Order::where('brand_id', $brand->user_id)->where('status', 'unfulfilled')->count();
            $fulfilledOrdersCount = Order::where('brand_id', $brand->user_id)->where('status', 'fulfilled')->count();
            $cancelledOrdersCount = Order::where('brand_id', $brand->user_id)->where('status', 'cancelled')->count();
            $orders = Order::where('brand_id', $brand->user_id);
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
            if (!empty($porders)) {
                foreach ($porders as $order) {
                    $retailer = User::find($order->user_id);
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
            return response()->json($response);
        } else {
            $response = ['res' => false, 'msg' => "No record found", 'data' => ""];
            return response()->json($response);
        }
    }
    
    /**
     * @param Request $request
     * @param $orderNumber
     * @return mixed
     */
    public function show(Request $request, $orderNumber)
    {
        $orders = [];
        $data = [];
        $order = Order::where('order_number', $orderNumber)->first();

        if ($order) {
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
        return response()->json($response);
    }
    
    /**
     * @param Request $request
     * @return mixed
     */
    public function packingSlip(Request $request)
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
        return response()->json($response);
    }
    
    /**
     * @param Request $request
     * @return mixed
     */
    public function accept(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brand_address1' => 'string|required',
            'brand_address2' => 'string|nullable',
            'brand_phone' => 'numeric|required',
            'brand_post_code' => 'string|nullable',
        ]);
        if ($validator->fails()) {
            $response = ['res' => false, 'msg' => $validator->errors()->first(), 'data' => ""];
            return response()->json($response);
        } else {

            if (empty(Order::where('order_number', $request->ord_no)->first())) {
                $response = ['res' => false, 'msg' => 'Order is Empty !', 'data' => ""];
                return response()->json($response);
            }
            $order = Order::where('order_number', $request->ord_no)->first();
            $order->brand_name = $request->brand_name;
            $order->brand_email = $request->brand_email;
            $order->brand_phone = $request->brand_phone;
            $order->brand_country = $request->brand_country;
            $order->brand_state = $request->brand_state;
            $order->brand_town = $request->brand_town;
            $order->brand_post_code = $request->brand_post_code;
            $order->brand_address1 = $request->brand_address1;
            $order->brand_address2 = $request->brand_address2;
            $order->shipping_date = $request->ship_date;
            $order->status = 'unfulfilled';
            $order->save();
            $retailerId = $order->user_id;
            $retailer_user = User::find($retailerId);
            $brand = Brand::where('user_id', $order->brand_id)->first();
            $msg = "Your orders with " . $brand->brand_name . " having order number <strong>#" . $order->order_number . "</strong> has been processing.";
            $data = array('email' => $retailer_user->email, 'order_number' => $order->order_number);

            $data = [];


            $response = ['res' => true, 'msg' => '', 'data' => $data];
        }
        return response()->json($response);
    }
    
    /**
     * @param Request $request
     * @return mixed
     */
    public function changeDate(Request $request)
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
                    $retailer_user = User::find($retailerId);
                    $msg = "Your order's ship date with " . $brand->brand_name . " having order number <strong>#" . $order->order_number . "</strong> has been changed to " . $request->ship_date;
                    $data = array('email' => $retailer_user->email, 'order_number' => $order->order_number);
                }
            }
        }


        $response = ['res' => true, 'msg' => "", 'data' => $data];
        return response()->json($response);
    }
    
    /**
     * @param Request $request
     * @return mixed
     */
    public function changeAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|required',
            'address1' => 'string|required',
            'address2' => 'string|nullable',
            'phone' => 'numeric|required',
            'post_code' => 'string|nullable',
        ]);
        if ($validator->fails()) {
            $response = ['res' => false, 'msg' => $validator->errors()->first(), 'data' => ""];
        } else {
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
        }

        $response = ['res' => true, 'msg' => "", 'data' => ""];
        return response()->json($response);
    }
    
    /**
     * @param Request $request
     * @return mixed
     */
    public function cancel(Request $request)
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
            $retailer_user = User::find($retailerId);
            $msg = "Your order with " . $brand->brand_name . " having order number <strong>#" . $order->order_number . "</strong> has been cancelled.<br>";
            $msg .= "<strong>Reason for cancelling</strong><br>";
            $msg .= $request->cancel_reason_title . "<br>";
            $msg .= $request->cancel_reason_desc . "<br>";
            $data = array('email' => $retailer_user->email, 'order_number' => $order->order_number);
        }
        $response = ['res' => true, 'msg' => "", 'data' => ""];
        return response()->json($response);
    }
    
    /**
     * @param Request $request
     * @return mixed
     */
    public function split(Request $request)
    {
        $order = Order::find($request->order_id);
        $new_cart = $request->items;

        if ($order && $new_cart) {
            $cart_arr = Cart::where('order_id', $order->id)->get();
            if ($cart_arr) {
                foreach ($cart_arr as $citem) {
                    $cart_id = $citem->id;
                    $cart_new_qty = $citem->quantity - $new_cart[$citem->id]['qty'];
                    $quantity = $cart_new_qty < 0 ? 0 : $cart_new_qty;

                    $cart_new_amnt = $cart_new_qty * $citem->price;
                    Cart::where('id', $cart_id)->update(['quantity' => $quantity, 'amount' => $cart_new_amnt]);

                    $shared_citem = $citem->replicate();
                    $shared_citem->quantity = $new_cart[$citem->id]['qty'];
                    $shared_citem->amount = $shared_citem->price * $shared_citem->quantity;
                    $shared_citem->order_id = null;
                    $shared_citem->save();
                }
            }

            $order->sub_total = Cart::where('user_id', $order->user_id)->where('brand_id', $order->brand_id)->where('order_id', $order->id)->sum('amount');
            $order->quantity = Cart::where('user_id', $order->user_id)->where('brand_id', $order->brand_id)->where('order_id', $order->id)->sum('quantity');
            $order->total_amount = $order->sub_total;
            $order->save();


            $shared_order = $order->replicate();
            $shared_order->order_number = 'ORD-' . strtoupper(Str::random(10));
            $shared_order->parent_id = $order->id;
            $shared_order->sub_total = Cart::where('user_id', $order->user_id)->where('brand_id', $order->brand_id)->where('order_id', null)->sum('amount');
            $shared_order->quantity = Cart::where('user_id', $order->user_id)->where('brand_id', $order->brand_id)->where('order_id', null)->sum('quantity');
            $shared_order->total_amount = $shared_order->sub_total;
            $status = $shared_order->save();
            Cart::where('user_id', $order->user_id)->where('brand_id', $order->brand_id)->where('order_id', null)->update(['order_id' => $shared_order->id]);
        }
        $response = ['res' => true, 'msg' => "", 'data' => ""];
        return response()->json($response);
    }
    
    /**
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request)
    {
        $order = Order::find($request->order_id);
        $new_cart = $request->items;

        if ($order && $new_cart) {
            $cart_arr = Cart::where('order_id', $order->id)->get();
            if ($cart_arr) {
                foreach ($cart_arr as $citem) {
                    $cart_id = $citem->id;
                    $cart_new_qty = $new_cart[$citem->id]['qty'];
                    $quantity = $cart_new_qty < 0 ? 0 : $cart_new_qty;
                    $cart_new_amnt = $cart_new_qty * $citem->price;
                    Cart::where('id', $cart_id)->update(['quantity' => $quantity, 'amount' => $cart_new_amnt]);
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
        return response()->json($response);
    }
    
    /**
     * @param Request $request
     * @return void
     */
    public function csv(Request $request)
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
        echo $data;
        exit();
    }
    
    /**
     * @param $prdct_arr
     * @param $brandId
     * @return bool
     */
    private function syncExternal($prdct_arr, $brandId)
    {

        $result_array = array();
        if (!empty($prdct_arr)) {
            foreach ($prdct_arr as $prdct) {
                switch ($prdct->type) {
                    case 'OPEN_SIZING':
                        $reference_arr = unserialize($prdct->reference);
                        if (!empty($reference_arr)) {
                            foreach ($reference_arr as $refk => $refv) {
                                $variant_id = $refk;
                                $ordered_qty = (int)$refv;
                                $variant = ProductVariation::find($variant_id);
                                $stock = (int)$variant->stock - $ordered_qty;
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
                            $user_count = ProductVariation::where('product_id', $prdct->product_id)->count();
                            if ($user_count == 1) {
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
                $product = Products::find($prdct->product_id);
                $syncs = Brandstore::where('brand_id', $brandId)->where('website', $product->website)
                    ->get()->first();
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
        return true;
    }

}
