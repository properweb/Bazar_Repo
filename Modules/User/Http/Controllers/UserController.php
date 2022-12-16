<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Mail;
use DB;

class UserController extends Controller
{


    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email_address' => 'string|email|required',
            'password' => 'required|min:6',
        ]);
        if ($validator->fails()) {
            $response = ['res' => false, 'msg' => $validator->errors()->first(), 'data' => ""];
        } else {
            $user = User::where('email', $request->email_address)->first();
            if ($user) {
                if (Hash::check($request->password, $user->password)) { //Hash::check($input, $hash)
                    $stepCount = 0;
                    $brandData = [];

                    if ($user->role == 'brand') {
                        $brand = Brand::where('user_id', $user->id)->first();
                        $stepCount = $brand ? (int)$brand->step_count : 0;
                        $brand->profile_photo = $brand->profile_photo != '' ? asset('public') . '/' . $brand->profile_photo : asset('public/admin/dist/img/profile-photo.png');
                        $brand->first_name = $user->first_name;
                        $brand->last_name = $user->last_name;
                        $brandData = $brand ? $brand : [];
                    }
                    $data = array(
                        "id" => $user->id,
                        "role" => $user->role,
                        "verified" => $user->verified,
                        "step_count" => $stepCount,
                        "vendor_data" => $brandData
                    );
                    $response = ['res' => true, 'msg' => "", 'data' => $data];
                } else {
                    $response = ['res' => false, 'msg' => "Your password is wrong!", 'data' => ""];
                }
            } else {
                $response = ['res' => false, 'msg' => "Email address cannot be found in our record!", 'data' => ""];
            }
        }
        return response()->json($response);
    }

    public function forgetPassword(Request $request)
    {
        $user = User::where('email', $request->email_address)->first();
        if ($user) {
            $token = Str::random(64);

            $user->token = $token;
            $user->save();
            $response = ['res' => true, 'msg' => "Please check your email, we sent a link to reset your password!", 'data' => ''];
        } else {
            $response = ['res' => false, 'msg' => "no record found", 'data' => ''];
        }
        return response()->json($response);
    }

    public function resetPassword(Request $request)
    {
        $user = User::where('token', $request->token)->first();
        $user->password = Hash::make($request->password);
        $user->save();
        $response = ['res' => true, 'msg' => "Your password reset successfully!", 'data' => ''];
        return response()->json($response);
    }


}
