<?php

namespace Modules\Brand\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Brand\Entities\Catalog;
use Modules\Brand\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Product\Entities\Category;
use File;
use Mail;
use DB;

class BrandController extends Controller
{

    private $brandUploadPath = "";

    public function __construct()
    {
        $this->brandUploadPath = '/uploads/brands/';
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
            $user = User::create(['email' => $data['email'], 'first_name' => $data['first_name'], 'last_name' => $data['last_name'], 'password' => Hash::make($data['password']), 'role' => 'brand']);
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
        $brand_id = $brand->id;


        if (isset($request->first_name) && isset($request->last_name)) {
            $user = User::find($request->user_id);
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->save();
        }

        $brandUploadPath = $this->brandUploadPath . $brand_id;
        if (!file_exists(public_path() . $brandUploadPath)) {
            mkdir(public_path() . $brandUploadPath, 0777, true);
        }

        $featured_image = $request->featured_image;
        if (isset($featured_image) && $featured_image != "") {
            $brand->featured_image = $this->imageUpload($brand_id, $featured_image, null, false);
            $status = $brand->save();
        }

        $profile_photo = $request->profile_photo;
        if (isset($profile_photo) && $profile_photo != "") {
            $brand->profile_photo = $this->imageUpload($brand_id, $profile_photo, null, false);
            $status = $brand->save();
        }

        $cover_image = $request->cover_image;
        if (isset($cover_image) && $cover_image != "") {
            $brand->cover_image = $this->imageUpload($brand_id, $cover_image, null, true);
        }

        $wholesle_xlsx = $request->upload_wholesale_xlsx;
        if (isset($wholesle_xlsx) && !empty($wholesle_xlsx)) {
            $excel_file_names = $_FILES["upload_wholesale_xlsx"]["name"];
            if (count($excel_file_names) > 0) {
                $folderPath = public_path() . $brandUploadPath . "/";
                for ($i = 0; $i < count($excel_file_names); $i++) {
                    $file_name = $excel_file_names[$i];
                    $tmp_arr = explode(".", $file_name);
                    $extension = end($tmp_arr);
                    $file_url = Str::random(10) . '_prices.' . $extension;
                    move_uploaded_file($_FILES["upload_wholesale_xlsx"]["tmp_name"][$i], $folderPath . $file_url);
                    $catalog = new Catalog();
                    $catalog->brand_id = $brand->id;
                    $catalog->filename = $brandUploadPath . "/" . $file_url;
                    $catalog->save();
                }
            }
        }

        $upload_zip = $request->upload_zip;
        if (isset($upload_zip) && $upload_zip != '') {
            $zip_file_name = $_FILES["upload_zip"]["name"];
            $folderPath = public_path() . $brandUploadPath . "/";
            $file_name = $zip_file_name;
            $tmp_arr = explode(".", $file_name);
            $extension = end($tmp_arr);
            $original_file_name = pathinfo($file_name, PATHINFO_FILENAME);
            $file_url = Str::random(10) . '_photos.' . $extension;
            move_uploaded_file($_FILES["upload_zip"]["tmp_name"], $folderPath . $file_url);
            $brand->upload_zip = $brandUploadPath . "/" . $file_url;
            $status = $brand->save();
        }

        $upload_contact_list = $request->upload_contact_list;
        if (isset($upload_contact_list) && $upload_contact_list != '') {
            $csv_file_name = $_FILES["upload_contact_list"]["name"];
            $folderPath = public_path() . $brandUploadPath . "/";
            $file_name = $csv_file_name;
            $tmp_arr = explode(".", $file_name);
            $extension = end($tmp_arr);
            $original_file_name = pathinfo($file_name, PATHINFO_FILENAME);
            $file_url = Str::random(10) . '_cstmrs.' . $extension;
            move_uploaded_file($_FILES["upload_contact_list"]["tmp_name"], $folderPath . $file_url);
            $brand->upload_contact_list = $brandUploadPath . "/" . $file_url;
            $status = $brand->save();
        }

        $response = ['res' => true, 'msg' => "", 'data' => $data];
        return response()->json($response);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $user = User::find($id);
        $brand = Brand::where('user_id', $user->id)->first();


        $brand->email = $user->email;
        $brand->verified = $user->verified;
        $brand->profile_photo = $brand->profile_photo != '' ? asset('public') . '/' . $brand->profile_photo : asset('public/admin/dist/img/profile-photo.png');
        $brand->featured_image = $brand->featured_image != '' ? asset('public') . '/' . $brand->featured_image : asset('public/admin/dist/img/featured-image.png');
        $brand->cover_image = $brand->cover_image != '' ? asset('public') . '/' . $brand->cover_image : asset('public/admin/dist/img/cover-image.png');
        $brand->logo_image = $brand->logo_image != '' ? asset('public') . '/' . $brand->logo_image : asset('public/admin/dist/img/logo-image.png');
        $brand->tools_used = $brand->tools_used != '' ? explode(',', $brand->tools_used) : array();
        $brand->tag_shop_page = $brand->tag_shop_page != '' ? explode(',', $brand->tag_shop_page) : array();

        $response = ['res' => true, 'msg' => "", 'data' => $brand];
        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request)
    {

        $user_id = request()->user_id;
        $brand = Brand::where('user_id', $request->user_id)->first();
        $brand_id = $brand->id;
        $request->brand_slug = Str::slug($request->brand_name, '-');
        $validator = Validator::make($request->all(), [
            'email' => 'string|email|unique:users,email,' . $user_id . ',id',
            'brand_slug' => 'string|unique:brands,brand_slug,' . $brand_id . ',id'
        ]);
        if ($validator->fails()) {
            $response = ['res' => false, 'msg' => $validator->errors()->first(), 'data' => ""];
        } else {

            $brand = Brand::updateOrCreate(['user_id' => request()->user_id], $request->except(['email', 'featured_image', 'profile_photo', 'cover_image', 'logo_image']));
            if (isset($request->email)) {
                $user = User::find($user_id);
                $user->email = $request->email;
                $user->save();
            }
            $profile_photo = $request->profile_photo;
            if (isset($profile_photo) && $profile_photo != "") {
                $brand->profile_photo = $this->imageUpload($brand_id, $profile_photo, $brand->profile_photo, true);
            }

            $cover_image = $request->cover_image;
            if (isset($cover_image) && $cover_image != "") {
                $brand->cover_image = $this->imageUpload($brand_id, $cover_image, $brand->cover_image, true);
            }

            $featured_image = $request->featured_image;
            if (isset($featured_image) && $featured_image != "") {
                $brand->featured_image = $this->imageUpload($brand_id, $featured_image, $brand->featured_image, true);
            }

            $logo_image = $request->logo_image;
            if (isset($logo_image) && $logo_image != "") {
                $brand->logo_image = $this->imageUpload($brand_id, $logo_image, $brand->logo_image, true);
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
     * Update the specified resource in storage.
     * @param int $brand
     * @param int $id
     */
    private function imageUpload($brand, $image, $previousFile, $replaceable)
    {

        $brandUploadPath = $this->brandUploadPath . $brand;
        if (!file_exists(public_path() . $brandUploadPath)) {
            mkdir(public_path() . $brandUploadPath, 0777, true);
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
        File::put(public_path() . $brandUploadPath . "/" . $imageName, base64_decode($image_64));
        return $brandUploadPath . "/" . $imageName;
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function category()
    {
        $categories = Category::where('parent_id', '0')->where('status', '0')->orderBy('name', 'ASC')->get();
        $allcategory = array();
        if (count($categories) > 0) {
            foreach ($categories as $category) {
                $allcategory[] = array(
                    'category' => $category->name,
                    'cat_id' => $category->id
                );
            }
        }
        $response = ['res' => true, 'msg' => "", 'data' => $allcategory];
        return response()->json($response);
    }

}
