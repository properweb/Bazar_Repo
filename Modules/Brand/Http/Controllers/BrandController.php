<?php

namespace Modules\Brand\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Brand\Entities\Catalog;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Retailer\Entities\Retailer;
use Modules\Product\Entities\Products;
use Modules\Cart\Entities\Cart;
use Modules\Order\Entities\Order;
use File;
use Mail;
use DB;

class BrandController extends Controller
{

    private $brandAbsPath = "";
    private $brandRelPath = "";

    public function __construct()
    {
        $this->brandAbsPath = public_path('uploads/brands');
        $this->brandRelPath = 'uploads/brands/';
    }

    public function index()
    {
        return view('brand::index');
    }

    public function register(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'email' => 'string|email|required|unique:users,email',
            'password' => 'required|min:6',
        ]);
        if ($validator->fails()) {
            $response = ['res' => false, 'msg' => $validator->errors()->first(), 'data' => ""];
        } else {
            $data = (array)$request->all();
            $user = User::create(['email' => $data['email'], 'first_name' => $data['first_name'], 'last_name' => $data['last_name'], 'password' => Hash::make($data['password']), 'role' => 'brand', 'verified' => '1']);
            if ($user) {
                $userId = $user->id;
                $rand_key = 'b_' . Str::lower(Str::random(10));
                request()->merge(array(
                    'brand_key' => $rand_key,
                    'bazaar_direct_link' => $rand_key,
                ));
                Brand::updateOrCreate(['user_id' => $userId], $request->except(['email', 'password', 'first_name', 'last_name']));
                $data['vendor_id'] = $userId;
                $data['brand_key'] = $request->brand_key;
                $data['bazaar_direct_link'] = $request->bazaar_direct_link;
                $response = ['res' => true, 'msg' => "", 'data' => $data];
            } else {
                $response = ['res' => false, 'msg' => "Please try again!", 'data' => ""];
            }
        }
        return response()->json($response);
    }

    public function create(Request $request)
    {
        $data = (array)$request->all();
        $request->bazaar_direct_link = Str::slug($request->bazaar_direct_link, '-');
        $request->brand_slug = Str::slug($request->brand_name, '-');
        $brand = Brand::updateOrCreate(['user_id' => request()->user_id], $request->except(['email', 'password', 'first_name', 'last_name', 'featured_image', 'profile_photo', 'cover_image']));
        $brandId = $brand->id;


        if (isset($request->first_name) && isset($request->last_name)) {
            $user = User::find($request->user_id);
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->save();
        }

        $brandAbsPath = $this->brandAbsPath . "/" . $brandId . "/";
        $brandRelPath = $this->brandRelPath . $brandId . "/";

        if (!file_exists($brandAbsPath)) {
            mkdir($brandAbsPath, 0777, true);
        }

        $featuredImage = $request->featured_image;
        if (isset($featuredImage) && $featuredImage != "") {
            $brand->featured_image = $this->imageUpload($brandId, $featuredImage, null, false);
            $status = $brand->save();
        }

        $profilePhoto = $request->profile_photo;
        if (isset($profilePhoto) && $profilePhoto != "") {
            $brand->profile_photo = $this->imageUpload($brandId, $profilePhoto, null, false);
            $status = $brand->save();
        }

        $coverImage = $request->cover_image;
        if (isset($coverImage) && $coverImage != "") {
            $brand->cover_image = $this->imageUpload($brandId, $coverImage, null, true);
        }

        if ($request->file('upload_wholesale_xlsx')) {
            foreach ($request->file('upload_wholesale_xlsx') as $key => $file) {
                $fileName = Str::random(10) . '_prices.' . $file->extension();
                $file->move($brandAbsPath, $fileName);
                $catalog = new Catalog();
                $catalog->brand_id = $brand->id;
                $catalog->filename = $brandRelPath . $fileName;
                $catalog->save();
            }
        }
        if ($request->file('upload_zip')) {
            $fileName = Str::random(10) . '_photos.' . $file->extension();
            $request->file('upload_zip')->move($brandAbsPath, $fileName);
            $brand->upload_zip = $brandRelPath . $fileName;
            $status = $brand->save();
        }
        if ($request->file('upload_contact_list')) {
            $fileName = Str::random(10) . '_cstmrs.' . $file->extension();
            $request->file('upload_contact_list')->move($brandAbsPath, $fileName);
            $brand->upload_contact_list = $brandRelPath . $fileName;
            $status = $brand->save();
        }

        $response = ['res' => true, 'msg' => "", 'data' => $data];
        return response()->json($response);
    }

    public function edit($id)
    {
        $user = User::find($id);
        $brand = Brand::where('user_id', $user->id)->first();

        $brand->first_name = $user->first_name;
        $brand->last_name = $user->last_name;
        $brand->email = $user->email;
        $brand->verified = $user->verified;
        $brand->profile_photo = $brand->profile_photo != '' ? asset('public') . '/' . $brand->profile_photo : asset('public/img/profile-photo.png');
        $brand->featured_image = $brand->featured_image != '' ? asset('public') . '/' . $brand->featured_image : asset('public/img/featured-image.png');
        $brand->cover_image = $brand->cover_image != '' ? asset('public') . '/' . $brand->cover_image : asset('public/img/cover-image.png');
        $brand->logo_image = $brand->logo_image != '' ? asset('public') . '/' . $brand->logo_image : asset('public/img/logo-image.png');
        $brand->tools_used = $brand->tools_used != '' ? explode(',', $brand->tools_used) : array();
        $brand->tag_shop_page = $brand->tag_shop_page != '' ? explode(',', $brand->tag_shop_page) : array();

        $response = ['res' => true, 'msg' => "", 'data' => $brand];
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
            $brand = Brand::where('user_id', $request->user_id)->first();
            $brand->country_code = $request->country_code;
            $brand->phone_number = $request->phone_number;
            $brand->save();

            $status = $user->save();
            if ($status) {
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

    public function updateShop(Request $request)
    {
        $userId = request()->user_id;
        $brand = Brand::where('user_id', $request->user_id)->first();
        $brandId = $brand->id;
        $request->brand_slug = Str::slug($request->brand_name, '-');
        $validator = Validator::make($request->all(), [
            'email' => 'string|email|unique:users,email,' . $userId . ',id',
            'brand_slug' => 'string|unique:brands,brand_slug,' . $brandId . ',id'
        ]);
        if ($validator->fails()) {
            $response = ['res' => false, 'msg' => $validator->errors()->first(), 'data' => ""];
        } else {

            $brand = Brand::updateOrCreate(['user_id' => request()->user_id], $request->except(['email', 'featured_image', 'profile_photo', 'cover_image', 'logo_image']));
            if (isset($request->email)) {
                $user = User::find($userId);
                $user->email = $request->email;
                $user->save();
            }
            $profilePhoto = $request->profile_photo;
            if (isset($profilePhoto) && $profilePhoto != "") {
                $brand->profile_photo = $this->imageUpload($brandId, $profilePhoto, $brand->profile_photo, true);
            }

            $coverImage = $request->cover_image;
            if (isset($coverImage) && $coverImage != "") {
                $brand->cover_image = $this->imageUpload($brandId, $coverImage, $brand->cover_image, true);
            }

            $featuredImage = $request->featured_image;
            if (isset($featuredImage) && $featuredImage != "") {
                $brand->featured_image = $this->imageUpload($brandId, $featuredImage, $brand->featured_image, true);
            }

            $logoImage = $request->logo_image;
            if (isset($logoImage) && $logoImage != "") {
                $brand->logo_image = $this->imageUpload($brandId, $logoImage, $brand->logo_image, true);
            }

            $brand->first_visit = '1';
            $status = $brand->save();
            if ($status) {
                $response = ['res' => true, 'msg' => "Successfully updated your account", 'data' => ''];
            } else {
                $response = ['res' => false, 'msg' => "Please try again!", 'data' => ''];
            }
        }

        return response()->json($response);
    }

    public function goLive(Request $request)
    {

        $brand = Brand::where('user_id', $request->user_id)->first();
        if ($brand) {
            $user = User::where('id', $request->user_id)->first();
            if ($user && $user->verified == '1') {
                $token = Str::random(64);
                $brandName = $brand->brand_name;

                $brand->go_live = '2';
                $brand->save();
                $response = ['res' => true, 'msg' => "We will notify you once your shop is activated", 'data' => ''];
            } else {
                $response = ['res' => false, 'msg' => "Please verify your email first", 'data' => ''];
            }
        } else {
            $response = ['res' => false, 'msg' => "Please try again!", 'data' => ''];
        }

        return response()->json($response);
    }

    public function all($id)
    {
        $user = User::find($id);
        if ($user) {
            $brandUsers = User::where('country_id', $user->country_id)->where('role', 'brand')->get()->toArray();
        }
        if ($brandUsers) {
            foreach ($brandUsers as $brandk => $brandv) {
                $brand = Brand::where('user_id', $brandv['id'])->first();
                $data[] = array(
                    'brand_key' => $brand->bazaar_direct_link,
                    'brand_id' => $brand->id,
                    'brand_name' => $brand->brand_name,
                    'brand_logo' => $brand->logo_image != '' ? asset('public') . '/' . $brand->logo_image : asset('public/img/logo-image.png'),
                );
            }
        }
        $response = ['res' => true, 'msg' => "", 'data' => $data];
        return response()->json($response);
    }

    public function orders(Request $request)
    {
        $rorders = [];
        $data = [];
        $brand = Brand::where('user_id', $request->user_id)->first();
        if ($brand) {
            $orders = Cart::where('brand_id', $brand->user_id)->where('order_id', '!=', null)->get();

            //different types orders count
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

    public function order(Request $request, $order_number)
    {
        $orders = [];
        $data = [];
        $order = Order::where('order_number', $order_number)->first();

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

    public function ordersPackingSlip(Request $request)
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
                        foreach ($cart as $cartitem) {
                            $product = Products::where('id', $cartitem->product_id)->first();
                            $cartitem->product_id = $product->id;
                            $cartitem->product_name = $product->name;
                            $cartitem->product_price = (float)$cartitem->price;
                            $cartitem->product_qty = (int)$cartitem->quantity;
                            $cartitem->product_image = $product->featured_image != '' ? $product->featured_image : asset('public/admin/dist/img/logo-image.png');
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

    public function acceptOrder(Request $request)
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

    public function changeDateOrder(Request $request)
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

    public function changeAddressOrder(Request $request)
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

    public function cancelOrder(Request $request)
    {
        $order = Order::find($request->order_id);
        if ($order) {

            $order->status = 'cancelled';
            $order->cancel_reason_title = $request->cancel_reason_title;
            $order->cancel_reason_desc = $request->cancel_reason_desc;
            $order->save();
            $brand = Brand::where('user_id', $order->brand_id)->first();
            $prdct_arr = Cart::where('order_id', $order->id)->get();


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


    public function splitOrder(Request $request)
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
                    //update cart with order id
                    $cart_new_amnt = $cart_new_qty * $citem->price;
                    Cart::where('id', $cart_id)->update(['quantity' => $quantity, 'amount' => $cart_new_amnt]);
                    // copying the old record
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

    public function updateOrder(Request $request)
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

    public function ordersCSV(Request $request)
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


    private function imageUpload($brand, $image, $previousFile, $replaceable)
    {

        $brandAbsPath = $this->brandAbsPath . '/' . $brand . '/';
        $brandRelPath = $this->brandRelPath . $brand . '/';

        if (!file_exists($brandAbsPath)) {
            mkdir($brandAbsPath, 0777, true);
        }

        if ($replaceable && $previousFile !== null) {
            $unlinkUrl = public_path() . $previousFile;
            if (file_exists($unlinkUrl)) {
                unlink($unlinkUrl);
            }
        }

        $image_64 = $image; //your base64 encoded data
        $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
        $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
        $image_64 = str_replace($replace, '', $image_64);
        $image_64 = str_replace(' ', '+', $image_64);
        $imageName = Str::random(10) . '.' . 'png';
        File::put($brandAbsPath . $imageName, base64_decode($image_64));
        return $brandRelPath . $imageName;
    }

}
