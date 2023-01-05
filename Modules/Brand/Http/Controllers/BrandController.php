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
use Modules\Cart\Entities\Cart;
use File;
use DB;

/**
 *
 */
class BrandController extends Controller
{

    /**
     * @var
     */
    private $brandAbsPath = "";
    /**
     * @var string
     */
    private $brandRelPath = "";

    /**
     *
     */
    public function __construct()
    {
        $this->brandAbsPath = public_path('uploads/brands');
        $this->brandRelPath = 'uploads/brands/';
    }


    /**
     * @param Request $request
     * @return mixed
     */
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

    /**
     * @param Request $request
     * @return mixed
     */
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

    /**
     * @param $id
     * @return mixed
     */
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

    /**
     * @param Request $request
     * @return mixed
     */
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
     * @param $id
     * @return mixed
     */
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

    /**
     * Update the specified resource in storage.
     * @param int $brand
     * @param int $id
     * @return Stringable
     */
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
