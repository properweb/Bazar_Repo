<?php

namespace Modules\Brand\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Str;
use Modules\Brand\Entities\Catalog;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Retailer\Entities\Retailer;
use Modules\Cart\Entities\Cart;
use Modules\Brand\Http\Requests\StoreBrandRequest;
use Modules\Brand\Http\Requests\UpdateBrandRequest;
use Modules\Brand\Http\Services\BrandService;
use File;
use DB;
use Exception;

class BrandController extends Controller
{

    private $brandAbsPath = "";

    private $brandRelPath = "";

    private BrandService $brandService;

    public function __construct(BrandService $brandService)
    {
        $this->brandService = $brandService;
        $this->brandAbsPath = public_path('uploads/brands');
        $this->brandRelPath = 'uploads/brands/';
    }




    /**
     * Store a newly created brand in storage
     *
     * @param StoreBrandRequest $request
     * @return mixed
     */
    public function store(StoreBrandRequest $request)
    {
        $response = $this->brandService->store($request->validated());

        return response()->json($response);

    }

    /**
     * Store a newly created brand in storage
     *
     * @param UpdateBrandRequest $request
     * @return mixed
     */
    public function update(UpdateBrandRequest $request)
    {

        $response = $this->brandService->update($request);

        return response()->json($response);

    }

    /**
     * @param $id
     * @return mixed
     */
    public function all($id)
    {
        $user = User::find($id);
        if ($user) {
            $brandUsers = User::where('country_id', $user->country_id)->where('role', 'brand')->get();
        }

        if ($brandUsers) {
            foreach ($brandUsers as $brandv) {
                $brand = Brand::where('user_id', $brandv['id'])->first();
                    if(!empty($brand))
                    {
                    $data[] = array(
                    'brand_key' => $brand->bazaar_direct_link,
                    'brand_id' => $brand->id,
                    'brand_name' => $brand->brand_name,
                    'brand_logo' => $brand->logo_image != '' ? asset('public') . '/' . $brand->logo_image : asset('public/img/logo-image.png'),
                    );
                    }

            }
        }
        $response = ['res' => true, 'msg' => "", 'data' => $data];
        return response()->json($response);
    }

    /**
     * @param $userId
     * @return JsonResponse
     */
    public function show(int $userId): JsonResponse
    {
        $response = $this->brandService->get($userId);

        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function updateAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|regex:/^[a-zA-Z]+$/u|max:255',
            'last_name' => 'required|regex:/^[a-zA-Z]+$/u|max:255',
            'phone_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
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
                        'new_password' => [
                            'required',
                            'different:old_password',
                            Password::min(8)
                                ->letters()
                                ->mixedCase()
                                ->numbers()
                                ->symbols()
                        ],
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

    /**
     * @param Request $request
     * @return mixed
     */
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

    /**
     * @param Request $request
     * @return mixed
     */
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

    /**
     * @param Request $request
     * @return mixed
     */
    public function customers(Request $request)
    {
        $customers = [];
        $data = [];
        $brand = Brand::where('user_id', $request->user_id)->first();
        if ($brand) {
            $brandCustomers = BrandCustomer::where('brand_id', $brand->user_id)->get();

            if ($brandCustomers) {
                foreach ($brandCustomers as $customer) {
                    $customerDetails = User::find($customer->user_id);
                    $retailerDetails = Retailer::where('user_id', $customer->user_id)->first();
                    $cart_amount = Cart::where('brand_id', $brand->id)->where('user_id', $customerDetails->id)->where('order_id', '!=', null)->sum('amount');
                    $ordered_amount = Cart::where('brand_id', $brand->id)->where('user_id', $customerDetails->id)->where('order_id', '!=', null)->sum('amount');
                    $store_name = $retailerDetails->store_name ?? '';
                    $customers[] = array(
                        'name' => $customerDetails->first_name . ' ' . $customerDetails->last_name,
                        'email' => $customerDetails->email,
                        'store_name' => $store_name,
                        'cart_amount' => $cart_amount,
                        'ordered_amount' => $ordered_amount
                    );
                }
            }
            $data = $customers;
            $response = ['res' => true, 'msg' => "", 'data' => $data];
            return response()->json($response);
        } else {
            $response = ['res' => false, 'msg' => "No record found", 'data' => ""];
            return response()->json($response);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function addCustomer(Request $request)
    {
        $data = [];
        $brand = Brand::where('user_id', $request->user_id)->first();
        if ($brand) {
            $customers = $request->customers;
            if ($customers) {
                foreach ($customers as $customer) {
                    $contact_name = $customer['contact_name'];
                    $name_arr = explode(' ', $contact_name);
                    $firstName = $name_arr[0];
                    unset($name_arr[0]);
                    $lastName = implode(' ', $name_arr);
                    $user = User::create(['email' => $customer['email_address'], 'first_name' => $firstName, 'last_name' => $lastName, 'password' => Hash::make('123456'), 'role' => 'retailer']);
                    if ($user) {
                        $userId = $user->id;
                        $newRetailer = new Retailer;
                        $newRetailer->user_id = $userId;
                        $newRetailer->retailer_key = 'r_' . Str::lower(Str::random(10));
                        $newRetailer->store_name = $customer['store_name'];
                        $newRetailer->save();
                        $newBrandCustomer = new BrandCustomer;
                        $newBrandCustomer->brand_id = $brand->id;
                        $newBrandCustomer->user_id = $userId;
                        $newBrandCustomer->save();
                    }
                }
            }
            $response = ['res' => true, 'msg' => "Inserted successfully", 'data' => ""];
            return response()->json($response);
        } else {
            $response = ['res' => false, 'msg' => "No record found", 'data' => ""];
            return response()->json($response);
        }
    }

}
