<?php

namespace Modules\Retailer\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Modules\Retailer\Entities\Retailer;
use Modules\Brand\Entities\Brand;
use Modules\Product\Entities\Products;
use Modules\Cart\Entities\Cart;
use Modules\Order\Entities\Order;
use Modules\User\Entities\User;
use DB;


class RetailerController extends Controller
{

    public function register(Request $request)
    {
        if ($request->retailer_id) {
            $validator = Validator::make($request->all(), []);
        } else {
            $validator = Validator::make($request->all(), [
                'email' => 'string|email|required|unique:users,email',
                'password' => 'required|min:6',
            ]);
        }
        if ($validator->fails()) {
            $response = ['res' => false, 'msg' => $validator->errors()->first(), 'data' => ""];
        } else {
            $data = (array)$request->all();
            if ($request->retailer_id) {
                $user = User::find($request->retailer_id);
            } else {
                $user = User::create(['email' => $data['email'], 'first_name' => $data['first_name'], 'last_name' => $data['last_name'], 'password' => Hash::make($data['password']), 'role' => 'retailer']);
            }
            if ($user) {
                $userId = $user->id;
                $retailer = Retailer::where('user_id', $user->id)->first();
                $retailerKey = $retailer ? $retailer->retailer_key : 'r_' . Str::lower(Str::random(10));
                $storeDesc = $request->store_desc && !empty($request->store_desc) ? implode(',', $request->store_desc) : '';
                $storeTags = $request->store_tags && !empty($request->store_tags) ? implode(',', $request->store_tags) : '';
                $storeCats = $request->store_cats && !empty($request->store_cats) ? implode(',', $request->store_cats) : '';
                request()->merge(array(
                    'retailer_key' => $retailerKey,
                    'store_desc' => $storeDesc,
                    'store_tags' => $storeTags,
                    'store_cats' => $storeCats,
                ));

                $retailer = Retailer::updateOrCreate(['user_id' => $userId], $request->except(['email', 'password', 'first_name', 'last_name', 'retailer_id']));
                $data['retailer_id'] = $userId;
                $data['retailer_key'] = $request->retailer_key;
                $response = ['res' => true, 'msg' => "Registered successfully!", 'data' => $data];
            } else {
                $response = ['res' => false, 'msg' => "Please try again!", 'data' => ""];
            }
        }
        return response()->json($response);
    }

    public function edit($id)
    {
        $user = User::find($id);
        $retailer = Retailer::where('user_id', $user->id)->first();

        $retailer->first_name = $user->first_name;
        $retailer->last_name = $user->last_name;
        $retailer->email = $user->email;
        $retailer->verified = $user->verified;

        $response = ['res' => true, 'msg' => "", 'data' => $retailer];
        return response()->json($response);
    }

    public function updateAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'phone_number' => 'required',
        ]);
        if ($validator->fails()) {
            $response = ['res' => false, 'msg' => $validator->errors()->first(), 'data' => ""];
        } else {
            $user = User::find($request->user_id);
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $status = $user->save();
            if ($status) {
                $retailer = Retailer::where('user_id', $request->user_id)->first();
                $retailer->country_code = $request->country_code;
                $retailer->country = $request->country;
                $retailer->phone_number = $request->phone_number;
                $retailer->language = $request->language;
                $retailer->store_name = $request->store_name;
                $retailer->store_type = $request->store_type;
                $retailer->sign_up_for_email = $request->sign_up_for_email;
                $retailer->website_url = $request->website_url;
                $retailer->save();
                if ($request->new_password != '') {
                    $validator2 = Validator::make($request->all(), [
                        'old_password' => 'required',
                        'new_password' => 'required|min:6|different:old_password',
                        'confirm_password' => 'required|same:new_password'
                    ]);
                    if ($validator2->fails()) {
                        $response = ['res' => false, 'msg' => $validator2->errors()->first(), 'data' => ""];
                    } else {
                        if (Hash::check($request->old_password, $user->password)) {
                            $user->password = Hash::make($request->new_password);
                            $user->save();
                            $response = ['res' => true, 'msg' => "Successfully updated your account", 'data' => ''];
                        } else {
                            $response = ['res' => false, 'msg' => 'old password does not match our record.', 'data' => ""];
                        }
                    }
                } else {
                    $response = ['res' => true, 'msg' => "Successfully updated your account", 'data' => ''];
                }
            } else {
                $response = ['res' => false, 'msg' => "Please try again!", 'data' => ''];
            }
        }

        return response()->json($response);
    }


    public function orders(Request $request)
    {
        $rorders = [];
        $data = [];
        $retailer = Retailer::where('user_id', $request->user_id)->first();
        if ($retailer) {
            $orders = Cart::where('user_id', $retailer->user_id)->where('order_id', '!=', null)->get();

            $allOrdersCount = Order::where('user_id', $retailer->user_id)->count();
            $newOrdersCount = Order::where('user_id', $retailer->user_id)->where('status', 'new')->count();
            $unfulfilledOrdersCount = Order::where('user_id', $retailer->user_id)->where('status', 'unfulfilled')->count();
            $fulfilledOrdersCount = Order::where('user_id', $retailer->user_id)->where('status', 'fulfilled')->count();
            $cancelledOrdersCount = Order::where('user_id', $retailer->user_id)->where('status', 'cancelled')->count();

            $orders = Order::where('user_id', $retailer->user_id);
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
                    if (!empty($cart)) {
                        foreach ($cart as $cItem) {
                            $product = Products::find($cItem->product_id);

                            $rorders[] = array(
                                'product_name' => $cItem->product_name,
                                'product_image' => $product->featured_image != '' ? $product->featured_image : asset('public/img/logo-image.png'),
                                'style_name' => $product->style_name,
                                'style_group_name' => $product->style_group_name,
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

    public function order(Request $request, $order_number)
    {
        $orders = [];
        $data = [];
        $order = Order::where('order_number', $order_number)->first();

        if ($order) {
            $cart = Cart::where('order_id', $order->id)->first();
            $retailer = Retailer::where('user_id', $cart->brand_id)->first();
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
            $cart = Cart::where('brand_id', $retailer->user_id)->where('order_id', $orderId)->get();
            if ($cart) {
                foreach ($cart as $cartitem) {
                    $sub_total = (float)$cartitem->price * (int)$cartitem->quantity;
                    $total_qty += (int)$cartitem->quantity;
                    $total_price += $sub_total;
                    $product = Products::where('id', $cartitem->product_id)->first();
                    $cartitem->product_id = $product->id;
                    $cartitem->product_name = $product->name;
                    $cartitem->product_price = (float)$cartitem->price;
                    $cartitem->product_qty = (int)$cartitem->quantity;
                    $cartitem->product_image = $product->featured_image != '' ? $product->featured_image : asset('public/admin/dist/img/logo-image.png');
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
                'brand' => $retailer->brand_name,
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


}
