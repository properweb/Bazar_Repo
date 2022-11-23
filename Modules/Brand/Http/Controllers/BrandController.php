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
use File;
use Mail;
use DB;

class BrandController extends Controller {

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index() {
        return view('brand::index');
    }

    public function register(Request $request) {


        $validator = Validator::make($request->all(), [
                    'email' => 'string|email|required|unique:users,email',
                    'password' => 'required|min:6',
        ]);
        if ($validator->fails()) {
            $response = ['res' => false, 'msg' => $validator->errors()->first(), 'data' => ""];
        } else {
            $data = (array) $request->all();
            $user = User::create([ 'email' => $data['email'], 'first_name' => $data['first_name'], 'last_name' => $data['last_name'], 'password' => Hash::make($data['password']), 'role' => 'brand']);
            if ($user) {

                //$token = Str::random(64);
//                $url = url("/").'dev/verify-email/' . $token;
//                Mail::send('email.emailVerify', ['url' => $url, 'site_url' => 'https://demoupdates.com/updates/new-bazar/dev/', 'site_name' => 'BAZAR', 'name' => $user->first_name . ' ' . $user->last_name], function($message) use($user) {
//                    $message->to($user->email);
//                    $message->from("info@bazarcenter.ca");
//                    $message->subject('Bazar:Verify Email');
//                });
                //$user->token = $token;
                //$user->save();

                $userId = $user->id;
                $rand_key = 'b_' . Str::lower(Str::random(10));
                request()->merge(array(
                    'brand_key' => $rand_key,
                    'bazaar_direct_link' => $rand_key,
                ));
                $vendor = Brand::updateOrCreate(['user_id' => $userId], $request->except(['email', 'password', 'first_name', 'last_name']));
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

    public function create(Request $request) {
        $data = (array) $request->all();
        $request->bazaar_direct_link = Str::slug($request->bazaar_direct_link, '-');
        $request->brand_slug = Str::slug($request->brand_name, '-');
        $vendor = Brand::updateOrCreate(['user_id' => request()->user_id], $request->except(['email', 'password', 'first_name', 'last_name', 'featured_image', 'profile_photo', 'cover_image']));


        if (isset($request->first_name) && isset($request->last_name)) {
            $user = User::find($request->user_id);
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->save();
        }


        $vndr_upload_path = '/uploads/brands/' . request()->user_id;
        if (!file_exists(public_path() . $vndr_upload_path)) {
            mkdir(public_path() . $vndr_upload_path, 0777, true);
        }

        $featured_image = $request->featured_image;
        if (isset($featured_image) && $featured_image != "") {
            $image_64 = $featured_image; //your base64 encoded data
            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
            $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
            $image = str_replace($replace, '', $image_64);
            $image = str_replace(' ', '+', $image);
            $image_name = Str::random(10) . '.' . 'png';
            File::put(public_path() . $vndr_upload_path . "/" . $image_name, base64_decode($image));
            $vendor->featured_image = $vndr_upload_path . "/" . $image_name;
            $status = $vendor->save();
        }


        $profile_photo = $request->profile_photo;
        if (isset($profile_photo) && $profile_photo != "") {
            $image_64 = $profile_photo; //your base64 encoded data
            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
            $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
            $image = str_replace($replace, '', $image_64);
            $image = str_replace(' ', '+', $image);
            $image_name = Str::random(10) . '.' . 'png';
            File::put(public_path() . $vndr_upload_path . "/" . $image_name, base64_decode($image));
            $vendor->profile_photo = $vndr_upload_path . "/" . $image_name;
            $status = $vendor->save();
        }

        $cover_image = $request->cover_image;
        if (isset($cover_image) && $cover_image != "") {
            $image_64 = $cover_image; //your base64 encoded data
            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
            $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
            $image = str_replace($replace, '', $image_64);
            $image = str_replace(' ', '+', $image);
            $image_name = Str::random(10) . '.' . 'png';
            File::put(public_path() . $vndr_upload_path . "/" . $image_name, base64_decode($image));
            $vendor->cover_image = $vndr_upload_path . "/" . $image_name;
            $status = $vendor->save();
        }


        $wholesle_xlsx = $request->upload_wholesale_xlsx;
        if (isset($wholesle_xlsx) && !empty($wholesle_xlsx)) {
            $excel_file_names = $_FILES["upload_wholesale_xlsx"]["name"];
            if (count($excel_file_names) > 0) {
                $folderPath = public_path() . $vndr_upload_path . "/";
                for ($i = 0; $i < count($excel_file_names); $i++) {
                    $file_name = $excel_file_names[$i];
                    $tmp_arr = explode(".", $file_name);
                    $extension = end($tmp_arr);
                    $file_url = Str::random(10) . '_prices.' . $extension;
                    move_uploaded_file($_FILES["upload_wholesale_xlsx"]["tmp_name"][$i], $folderPath . $file_url);
                    $catalog = new Catalog();
                    $catalog->brand_id = $vendor->id;
                    $catalog->filename = $vndr_upload_path . "/" . $file_url;
                    $catalog->save();
                }
            }
        }

        $upload_zip = $request->upload_zip;
        if (isset($upload_zip) && $upload_zip != '') {
            $zip_file_name = $_FILES["upload_zip"]["name"];
            $folderPath = public_path() . $vndr_upload_path . "/";
            $file_name = $zip_file_name;
            $tmp_arr = explode(".", $file_name);
            $extension = end($tmp_arr);
            $original_file_name = pathinfo($file_name, PATHINFO_FILENAME);
            $file_url = Str::random(10) . '_photos.' . $extension;
            move_uploaded_file($_FILES["upload_zip"]["tmp_name"], $folderPath . $file_url);
            $vendor->upload_zip = $vndr_upload_path . "/" . $file_url;
            $status = $vendor->save();
        }

        $upload_contact_list = $request->upload_contact_list;
        if (isset($upload_contact_list) && $upload_contact_list != '') {
            $csv_file_name = $_FILES["upload_contact_list"]["name"];
            $folderPath = public_path() . $vndr_upload_path . "/";
            $file_name = $csv_file_name;
            $tmp_arr = explode(".", $file_name);
            $extension = end($tmp_arr);
            $original_file_name = pathinfo($file_name, PATHINFO_FILENAME);
            $file_url = Str::random(10) . '_cstmrs.' . $extension;
            move_uploaded_file($_FILES["upload_contact_list"]["tmp_name"], $folderPath . $file_url);
            $vendor->upload_contact_list = $vndr_upload_path . "/" . $file_url;
            $status = $vendor->save();
        }

//        if ($vendor->step_count == 12) {
//            mail("sushobhon@properbounce.com","My subject","Bazar Test Email");
//            Mail::send('email.signUp', ['site_url' => 'https://demoupdates.com/updates/new-bazar/dev/', 'site_name' => 'BAZAR', 'name' => $request->first_name . ' ' . $request->last_name], function($message) use($request) {
//                $message->to('sushobhon@properbounce.com');
//                $message->from("sender@demoupdates.com");
//                $message->subject('Sign Up');
//            });
//        }


        $response = ['res' => true, 'msg' => "", 'data' => $data];
        return response()->json($response);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id) {
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
    public function update(Request $request) {

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

            $vendor = Brand::updateOrCreate(['user_id' => request()->user_id], $request->except(['email', 'featured_image', 'profile_photo', 'cover_image', 'logo_image']));
            if (isset($request->email)) {
                $user = User::find($user_id);
                $user->email = $request->email;
                $user->save();
            }
            $profile_photo = $request->profile_photo;
            if (isset($profile_photo) && $profile_photo != "") {

                $temp_string = '/uploads/brands/' . request()->user_id;
                if (!file_exists(public_path() . $temp_string)) {
                    mkdir(public_path() . $temp_string, 0777, true);
                }
                if ($vendor->profile_photo != "") {
                    $unlink_url = public_path() . $temp_string . '/' . $vendor->profile_photo;
                    if (file_exists($unlink_url)) {
                        unlink($unlink_url);
                    }
                }

                $image_64 = $profile_photo; //your base64 encoded data
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $image_name = Str::random(10) . '.' . 'png';
                File::put(public_path() . $temp_string . "/" . $image_name, base64_decode($image));
                $vendor->profile_photo = $temp_string . "/" . $image_name;
            }

            $cover_image = $request->cover_image;
            if (isset($cover_image) && $cover_image != "") {
                $temp_string = '/uploads/brands/' . request()->user_id;
                if (!file_exists(public_path() . $temp_string)) {
                    mkdir(public_path() . $temp_string, 0777, true);
                }
                if ($vendor->cover_image != "") {
                    $unlink_url = public_path() . $temp_string . '/' . $vendor->cover_image;
                    if (file_exists($unlink_url)) {
                        unlink($unlink_url);
                    }
                }
                $image_64 = $cover_image; //your base64 encoded data
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $image_name = Str::random(10) . '.' . 'png';
                File::put(public_path() . $temp_string . "/" . $image_name, base64_decode($image));
                $vendor->cover_image = $temp_string . "/" . $image_name;
            }

            $featured_image = $request->featured_image;
            if (isset($featured_image) && $featured_image != "") {
                $temp_string = '/uploads/brands/' . request()->user_id;
                if (!file_exists(public_path() . $temp_string)) {
                    mkdir(public_path() . $temp_string, 0777, true);
                }
                if ($vendor->featured_image != "") {
                    $unlink_url = public_path() . $temp_string . '/' . $vendor->featured_image;
                    if (file_exists($unlink_url)) {
                        unlink($unlink_url);
                    }
                }
                $image_64 = $featured_image; //your base64 encoded data
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $image_name = Str::random(10) . '.' . 'png';
                File::put(public_path() . $temp_string . "/" . $image_name, base64_decode($image));
                $vendor->featured_image = $temp_string . "/" . $image_name;
            }

            $logo_image = $request->logo_image;
            if (isset($logo_image) && $logo_image != "") {
                $temp_string = '/uploads/brands/' . request()->user_id;
                if (!file_exists(public_path() . $temp_string)) {
                    mkdir(public_path() . $temp_string, 0777, true);
                }
                if ($vendor->logo_image != "") {
                    $unlink_url = public_path() . $temp_string . '/' . $vendor->logo_image;
                    if (file_exists($unlink_url)) {
                        unlink($unlink_url);
                    }
                }
                $image_64 = $logo_image; //your base64 encoded data
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $image_name = Str::random(10) . '.' . 'png';
                File::put(public_path() . $temp_string . "/" . $image_name, base64_decode($image));
                $vendor->logo_image = $temp_string . "/" . $image_name;
            }

            $vendor->first_visit = '1';
            $status = $vendor->save();
            if ($status) {
                $response = ['res' => true, 'msg' => "Successfully updated your account", 'data' => ''];
            } else {
                $response = ['res' => false, 'msg' => "Please try again!", 'data' => ''];
            }
        }

        return response()->json($response);
    }

    public function category() {
        $states = DB::table('category')
                ->where('parent_id', 0)
                ->orderBy('name', 'ASC')
                ->get();

        foreach ($states as $state) {
            $data[] = array(
                'cat_id' => $state->id,
                'category' => $state->name
            );
        }
        $response = ['res' => true, 'msg' => "", 'data' => $data];
        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id) {
        //
    }

}
