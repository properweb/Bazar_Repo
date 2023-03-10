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
     * Get list of brands
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {

        $response = $this->brandService->getBrands($request);

        return response()->json($response);
    }


    /**
     * Store a newly created brand in storage
     *
     * @param StoreBrandRequest $request
     * @return JsonResponse
     */
    public function store(StoreBrandRequest $request): JsonResponse
    {
        $response = $this->brandService->store($request->validated());

        return response()->json($response);

    }

    /**
     * Store a newly created brand in storage
     *
     * @param UpdateBrandRequest $request
     * @return JsonResponse
     */
    public function update(UpdateBrandRequest $request): JsonResponse
    {

        $response = $this->brandService->update($request);

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
     * @param string $brandKey
     * @return JsonResponse
     */
    public function showShop(string $brandKey): JsonResponse
    {
        $response = $this->brandService->getShop($brandKey);
        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function updateAccount(Request $request)
    {
        $response = $this->brandService->updateAccount($request);
        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function updateShop(Request $request)
    {
        $response = $this->brandService->updateShop($request);
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
