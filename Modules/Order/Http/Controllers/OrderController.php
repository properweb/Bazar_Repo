<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
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

class OrderController extends Controller {

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request) {
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

            // return $orderData['total_amount'];
            $orderData['status'] = "new";
            $orderData['payment_method'] = 'cod';
            $orderData['payment_status'] = 'Unpaid';
            $order->fill($orderData);
            $status = $order->save();
            if ($order) {
                //mail to brand
                $data = array('email' => $brand_user->email);
//            $url = 'https://demoupdates.com/updates/new-bazar/dev/view-order/' . $order->order_number;
//            Mail::send('email.brandNewOrder', ['url' => $url, 'site_url' => 'https://demoupdates.com/updates/new-bazar/dev/', 'site_name' => 'BAZAR', 'name' => $brand_user->first_name . ' ' . $brand_user->last_name], function($message) use($data) {
//                $message->to($data['email']);
//                $message->from("sender@demoupdates.com");
//                $message->subject('Bazar:New Order');
//            });



                $user_name = $user->first_name . " " . $user->last_name;
                $user_email = $user->email;
                $store_name = $retailer->store_name;
//            $user_count = DB::table('brand_contact_customer_list')->where('email_address', $user_email)->count();
//            if ($user_count == 0) {
//                DB::insert("INSERT INTO brand_contact_customer_list(`name`,`store_name`,`email_address`,`brand_id`,`sources`,`ship_name`,`ship_country`,`street`,`apt`,`town`,`state`,`zip`,`phone`) VALUES('" . $user_name . "','" . $store_name . "','" . $user_email . "','" . $brand->user_id . "','order','" . $request->name . "','" . $request->country . "','" . $request->address2 . "','" . $request->address1 . "','" . $request->town . "','" . $request->state . "','" . $request->post_code . "','" . $request->phone . "')");
//            }
                //update cart with order id
                Cart::where('user_id', $request->user_id)->where('order_id', null)->update(['order_id' => $order->id]);

                $prdct_arr = Cart::where('user_id', $request->user_id)->where('order_id', $order->id)->where('brand_id', $brand->user_id)->get();
                //sync stock to external sites
                //$this->syncExternal($prdct_arr, $brand->user_id);

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
                                $cart_arr[$brandk]['products'][$prdctk]['product_price'] = (float) $prdctv['price'];
                                $cart_arr[$brandk]['products'][$prdctk]['product_qty'] = (int) $prdctv['quantity'];
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
                        $sub_total = (float) $prdctv['price'] * (int) $prdctv['quantity'];
                        $ototal_qty += (int) $prdctv['quantity'];
                        $ototal_price += $sub_total;
                        $oprdct_arr[] = array(
                            "sku" => $prdctv['product_sku'],
                            "name" => $prdctv['product_name'],
                            "desc" => $prdctv['style_name'],
                            "qty" => (int) $prdctv['quantity'],
                            "price" => (float) $prdctv['price'],
                            "subtotal" => $sub_total,
                        );
                    }
                }
                $email = $user->email;
//            Mail::send('email.order', ['site_url' => 'https://demoupdates.com/updates/new-bazar/dev/', 'site_name' => 'BAZAR', 'sstore_name' => $retailer->store_name, 'sname' => $order->name, 'scountry' => $order->country, 'sstate' => $order->state, 'stown' => $order->town, 'spost_code' => $order->post_code, 'saddress1' => $order->address1, 'saddress2' => $order->address2, 'sphone' => $order->phone, 'sphone_code' => $retailer->country_code, 'bname' => $brand->brand_name, 'bemail' => $brand_user->email, 'bphone' => $brand->phone_number, 'bphone_code' => $brand->country_code, 'total' => $request->cart_total, 'products' => $oprdct_arr, 'total_price' => $ototal_price, 'total_qty' => $ototal_qty, 'order_no' => $order->order_number, 'ordered_date' => date('F j,Y', strtotime($order->created_at)), 'shipping_date' => date('F j,Y', strtotime($order->shipping_date)), 'payment_method' => $order->payment_method], function($message) use($email) {
//                $message->to($email);
//                $message->from("sender@demoupdates.com");
//                $message->subject('Bazar:Order Confirmation');
//            });

                $data = array(
                    'order_det' => $order,
                    'cart_det' => $cart_arr,
                );

                // dd($users);        
                $response = ['res' => true, 'msg' => 'Your product successfully placed in order', 'data' => $data];
            }
            return response()->json($response);
        }
    }

    private function syncExternal($prdct_arr, $brand_id) {

        $result_array = array();
        $brand_id = $brand_id;
        $prdct_arr = $prdct_arr;

        if (!empty($prdct_arr)) {
            foreach ($prdct_arr as $prdct) {
                switch ($prdct->type) {
                    case 'OPEN_SIZING':
                        $reference_arr = unserialize($prdct->reference);
                        if (!empty($reference_arr)) {
                            foreach ($reference_arr as $refk => $refv) {
                                $variant_id = $refk;
                                $ordered_qty = (int) $refv;
                                $variant = ProductVariation::find($variant_id);
                                $stock = (int) $variant->stock - $ordered_qty;
                                $variant->stock = $stock;
                                $variant->save();
                            }
                        }
                        break;
                    case 'PREPACK':
                        break;
                    case 'SINGLE_PRODUCT':
                        if (!empty($prdct->variant_id)) {
                            $variant = ProductVariation::find($prdct->variant_id);
                            $stock = (int) $variant->stock - $prdct->quantity;
                            $variant->stock = $stock;
                            $variant->save();
                            $user_count = ProductVariation::where('product_id', $prdct->product_id)->count();
                            if ($user_count == 1) {
                                Products::where('id', $prdct->product_id)->update(array("stock" => $stock));
                            }
                        } else {
                            $product = Products::find($prdct->product_id);
                            $stock = (int) $product->stock - $prdct->quantity;
                            $product->stock = $stock;
                            $product->save();
                        }
                        break;
                    default:
                        break;
                }
                $product = Products::find($prdct->product_id);

                $syncs = DB::table('brand_store_import_tbl')
                                ->where('brand_id', $brand_id)
                                ->where('website', $product->website)
                                ->get()->first();

                $types = $syncs->types;
                if ($types == 'wordpress') {
                    // include(app_path() . '/Classes/class-sw-api-client.php');


                    $consumer_key = $syncs->api_key;

                    $website = 'https://' . $syncs->website;
                    $consumer_secret = $syncs->api_password;




                    $prdct_qry = "SELECT * FROM products WHERE id='" . $product->product_id . "'";
                    $prdct_res = DB::select($prdct_qry);

                    $url = "" . $website . "/wp-json/wc/v3/products/" . $prdct_res[0]->product_id . "";

                    $headers = array(
                        'Authorization' => 'Basic ' . base64_encode($consumer_key . ':' . $consumer_secret)
                    );
                    $data = array(
                        'stock_quantity' => $prdct_res[0]->stock,
                    );

                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_TIMEOUT, 30);

                    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
//for debug only!
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($curl, CURLOPT_USERPWD, "$consumer_key:$consumer_secret");
                    $resp = curl_exec($curl);
                    $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    curl_close($curl);


                    $qry = "SELECT * FROM product_variations WHERE website='" . $syncs->website . "' AND product_id='" . $product->product_id . "'";
                    $res = DB::select($qry);
                    if (count($res) > 0) {
                        foreach ($res as $var) {
                            $url = "" . $website . "/wp-json/wc/v3/products/" . $prdct_res[0]->product_id . "/variations/" . $var->variation_id . "";

                            $headers = array(
                                'Authorization' => 'Basic ' . base64_encode($consumer_key . ':' . $consumer_secret)
                            );
                            $data = array(
                                'stock_quantity' => $var->stock,
                            );

                            $curl = curl_init();
                            curl_setopt($curl, CURLOPT_URL, $url);
                            curl_setopt($curl, CURLOPT_POST, true);
                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($curl, CURLOPT_TIMEOUT, 30);

                            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
//for debug only!
                            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($curl, CURLOPT_USERPWD, "$consumer_key:$consumer_secret");
                            $resp = curl_exec($curl);
                            $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                            curl_close($curl);
                        }
                    }
                    $response = ['res' => true, 'msg' => "Sync Successfully", 'data' => ""];
                }

                if ($types == 'shopify') {

                    $API_KEY = $syncs->api_key;
                    $STORE_URL = $syncs->website;
                    $PASSWORD = $syncs->api_password;

                    $putUrl = 'https://' . $API_KEY . ':' . $PASSWORD . '@' . $STORE_URL . '/admin/api/2022-07/inventory_levels/set.json';

                    $qry = "SELECT * FROM product_variations WHERE website='" . $syncs->website . "' AND product_id='" . $product->id . "'";
                    $res = DB::select($qry);
                    if (count($res) > 0) {
                        foreach ($res as $var) {

                            $payload = array(
                                "location_id" => 36132814934,
                                "inventory_item_id" => $var->inventory_item_id,
                                "available" => $var->stock
                            );
                            $payload = json_encode($payload, JSON_NUMERIC_CHECK);

                            $session = curl_init();
                            curl_setopt($session, CURLOPT_URL, $putUrl);
                            curl_setopt($session, CURLOPT_CONNECTTIMEOUT, 30); //seconds to allow for connection
                            curl_setopt($session, CURLOPT_TIMEOUT, 30); //seconds to allow for cURL commands
                            curl_setopt($session, CURLOPT_HEADER, true); //include header info in return value ? 
                            curl_setopt($session, CURLOPT_RETURNTRANSFER, true); //return response as a string
//curl_setopt($session, CURLOPT_PUT, 1); 
                            curl_setopt($session, CURLOPT_POSTFIELDS, $payload);
                            curl_setopt($session, CURLOPT_CUSTOMREQUEST, 'POST');
                            curl_setopt($session, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
                            $data = curl_exec($session);
                            curl_close($session);
                        }
                    }

                    $qry = "SELECT * FROM product_variations WHERE website='" . $syncs->website . "' AND product_id='" . $product->id . "'";
                    $res = DB::select($qry);
                    if (count($res) == 1) {
                        foreach ($res as $var) {
                            $prdct_qry = "SELECT * FROM products WHERE id='" . $product->id . "'";
                            $prdct_res = DB::select($prdct_qry);


                            $payload = array(
                                "location_id" => 36132814934,
                                "inventory_item_id" => $var->inventory_item_id,
                                "available" => $prdct_res[0]->stock
                            );
                            $payload = json_encode($payload, JSON_NUMERIC_CHECK);

                            $session = curl_init();
                            curl_setopt($session, CURLOPT_URL, $putUrl);
                            curl_setopt($session, CURLOPT_CONNECTTIMEOUT, 30); //seconds to allow for connection
                            curl_setopt($session, CURLOPT_TIMEOUT, 30); //seconds to allow for cURL commands
                            curl_setopt($session, CURLOPT_HEADER, true); //include header info in return value ? 
                            curl_setopt($session, CURLOPT_RETURNTRANSFER, true); //return response as a string
//curl_setopt($session, CURLOPT_PUT, 1); 
                            curl_setopt($session, CURLOPT_POSTFIELDS, $payload);
                            curl_setopt($session, CURLOPT_CUSTOMREQUEST, 'POST');
                            curl_setopt($session, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
                            $data = curl_exec($session);
                            curl_close($session);
                        }
                    }

                    //$response = ['res' => true, 'msg' => "Sync Successfully", 'data' => ""];
                }
            }
        }
        return true;
    }

}
