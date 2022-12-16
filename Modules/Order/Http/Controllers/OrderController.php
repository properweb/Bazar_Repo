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
use DB;

class OrderController extends Controller
{

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


                $brand_arr = Cart::where('user_id', $request->user_id)->where('order_id', $order->id)->groupBy('brand_id')->get()->toArray();
                if (!empty($brand_arr)) {
                    foreach ($brand_arr as $brandk => $brandv) {
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


}
