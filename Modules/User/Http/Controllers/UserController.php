<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Mail;
use DB;

class UserController extends Controller
{

    public function __construct()
    {
        //$this->middleware('auth:sanctum', ['except' => ['login']]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => [
                'required',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ]
        ]);
        if ($validator->fails()) {
            $response = ['res' => false, 'msg' => $validator->errors()->first(), 'data' => ""];
        } else {
            $user = User::where('email', $request->email)->first();

            if ($user) {

                if (Auth::attempt($request->only('email', 'password'))) {
                    //Auth::login($user);

                    //$credentials = $request->only('email', 'password');

                    //$token = Auth::attempt($credentials);
//                    $token = auth()->attempt($validator->validated())
//
//                    if (!$token) {
//                        return response()->json(['res' => false, 'msg' => "", 'data' => ""], 401);
//                    }
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
                    $authUser = Auth::user();
                    //$authUser->tokens()->where('name', 'bazarAuth')->delete();
                    $token = $authUser->createToken('bazarAuth')->accessToken;
                    //$token = Auth::login($user);
                    //$token = $user->createToken('API Token')->accessToken;
                    $data = array(
                        "id" => $user->id,
                        "role" => $user->role,
                        "verified" => $user->verified,
                        "step_count" => $stepCount,
                        "vendor_data" => $brandData,
                        'authorisation' => [
                            'token' => $token,
                            'type' => 'bearer',
                        ]
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
//            $url = 'https://demoupdates.com/updates/new-bazar/dev/reset-password/' . $token;
//            Mail::send('email.forgetPassword', ['url' => $url, 'site_url' => 'https://demoupdates.com/updates/new-bazar/dev/', 'site_name' => 'BAZAR', 'name' => $user->first_name . ' ' . $user->last_name], function($message) use($user) {
//                $message->to($user->email);
//                $message->from("sender@demoupdates.com");
//                $message->subject('Bazar:Reset Password');
//            });
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
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => [
                'required',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ]
        ]);
        if ($validator->fails()) {
            $response = ['res' => false, 'msg' => $validator->errors()->first(), 'data' => ""];
        } else {
            $user = User::where('email', $request->email)->first();
            $user->password = Hash::make($request->password);
            $user->save();
            $response = ['res' => true, 'msg' => "Your password reset successfully!", 'data' => $user->password];
        }

        return response()->json($response);
    }


}
