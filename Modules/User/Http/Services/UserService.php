<?php

namespace Modules\User\Http\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;

class UserService
{
    protected User $user;

    /**
     * Sign In a user
     *
     * @param array $requestData
     * @return array
     */
    public function login(array $requestData): array
    {
        //dd($requestData);
        if (Auth::attempt(["email" => $requestData['email'], "password" => $requestData['password']])) {
            $stepCount = 0;
            $brandData = [];
            $user = Auth::user();

            if ($user->role === User::ROLE_BRAND) {
                $brand = Brand::where('user_id', $user->id)->first();
                $stepCount = $brand ? (int)$brand->step_count : 0;
                $brand->profile_photo = !empty($brand->profile_photo) ? asset('public') . '/' . $brand->profile_photo : asset('public/admin/dist/img/profile-photo.png');
                $brand->first_name = $user->first_name;
                $brand->last_name = $user->last_name;
                $brandData = $brand ? $brand : [];
                $userKey = $brand->brand_key;
            } else {
                $retailerDetails = Retailer::where('user_id', $user->id)->first();
                $userKey = $retailerDetails->retailer_key;
            }
            $token = $user->createToken('bazarAuth')->accessToken;
            $data = array(
                "id" => $user->id,
                "role" => $user->role,
                "user_key" => $userKey,
                "verified" => $user->verified,
                "step_count" => $stepCount,
                "vendor_data" => $brandData,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            );
            return [
                'res' => true,
                'msg' => '',
                'data' => $data
            ];
        } else {
            return [
                'res' => false,
                'msg' => '"Your password is wrong!',
                'data' => ""
            ];
        }
    }

    /**
     * Create new user
     *
     * @param Request $request
     * @return array
     */
    public function forgetPassword(Request $request): array
    {
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $token = Str::random(64);
            $appUrl = config('app.url');
            $appName = config('app.name');
            $url = 'https://staging1.bazarcenter.ca/reset-password/' . $token;
            Mail::send('email.forgetPassword', ['url' => $url, 'site_url' => 'https://staging1.bazarcenter.ca', 'site_name' => $appName, 'name' => $user->first_name . ' ' . $user->last_name], function ($message) use ($user) {
                $message->to($user->email);
                $message->from("sender@demoupdates.com");
                $message->subject('Bazar:Reset Password');
            });
            $user->token = $token;
            $user->save();
            return ['res' => true, 'msg' => "Please check your email, we sent a link to reset your password!", 'data' => ''];
        } else {
            return ['res' => false, 'msg' => "no record found", 'data' => ''];
        }
    }

    /**
     * Update the specified user in storage.
     *
     * @param array $requestData
     * @return array
     */
    public function resetPassword(array $requestData): array
    {
        $user = User::where('token', $requestData['token'])->first();

        // return error if no user found
        if (!$user) {
            return [
                'res' => false,
                'msg' => 'User not found !',
                'data' => ""
            ];
        }

        $user->password = Hash::make($requestData['password']);
        $user->save();

        return [
            'res' => true,
            'msg' => 'Your password updated successfully',
            'data' => ''
        ];
    }
}
