<?php

namespace Modules\Frontend\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Brand\Entities\User;
use Modules\Brand\Entities\Brand;
use Mail;
use DB;

class FrontendController extends Controller {

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function countries() {
        $redis = Redis::connection();
        $existredis = Redis::exists("countries");
        if ($existredis > 0) {
            $cached = Redis::get("countries");
            $allfetch = json_decode($cached, false);
            $response = ['res' => true, 'msg' => "", 'data' => $allfetch];
        }else {
            $countries = DB::table('countries')->orderBy('name', 'ASC')->get();
            foreach ($countries as $country) {
                $result_array[] = array(
                    'id' => $country->id,
                    'country_code' => $country->shortname,
                    'country_name' => $country->name,
                    'phone_code' => $country->phonecode
                );
            }
            $allfetch = $redis->set('countries', json_encode($result_array));
            $response = ['res' => true, 'msg' => "", 'data' => $result_array];
        }

        return response()->json($response);
    }

    /**
     * Show the specified resource.
     * @param int $country
     * @return Renderable
     */
    public function states($country) {
        $states = DB::table('states')
                ->where('country_id', $country)
                ->orderBy('name', 'ASC')
                ->get();

        foreach ($states as $state) {
            $data[] = array(
                'id' => $state->id,
                'state_name' => $state->name
            );
        }
        $response = ['res' => true, 'msg' => "", 'data' => $data];
        return response()->json($response);
    }

    /**
     * Show the specified resource.
     * @param int $state
     * @return Renderable
     */
    public function cities($state) {
        $cities = DB::table('cities')
                ->where('state_id', $state)
                ->orderBy('name', 'ASC')
                ->get();

        foreach ($cities as $city) {
            $data[] = array(
                'id' => $city->id,
                'city_name' => $city->name
            );
        }
        $response = ['res' => true, 'msg' => "", 'data' => $data];
        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function login(Request $request) {

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
                    $step_count = 0;
                    $vendor_data = [];

                    if ($user->role == 'brand') {
                        $vendor = Brand::where('user_id', $user->id)->first();
                        $step_count = $vendor ? (int) $vendor->step_count : 0;
                        $vendor->profile_photo = $vendor->profile_photo != '' ? asset('public') . '/' . $vendor->profile_photo : asset('public/admin/dist/img/profile-photo.png');
                        $vendor->first_name = $user->first_name;
                        $vendor->last_name = $user->last_name;
                        $vendor_data = $vendor ? $vendor : [];
                    }
                    $data = array(
                        "id" => $user->id,
                        "role" => $user->role,
                        "verified" => $user->verified,
                        "step_count" => $step_count,
                        "vendor_data" => $vendor_data
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

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request) {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function brandEdit($id) {
        $user = User::find($id);
        $brand = Brand::where('user_id', $user->id)->first();
        $user->country_code = $brand->country_code;
        $user->phone_number = $brand->phone_number;
        $response = ['res' => true, 'msg' => "", 'data' => $user];
        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function brandUpdate(Request $request) {
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
//            $token = Str::random(64);
//            $url = url("/") . 'dev/verify-email/' . $token;
//            Mail::send('email.emailVerify', ['url' => $url, 'site_url' => url("/"), 'site_name' => 'BAZAR', 'name' => $user->first_name . ' ' . $user->last_name], function($message) use($user) {
//                $message->to($user->email);
//                $message->from("info@bazarcenter.ca");
//                $message->subject('Bazar:Verify Email');
//            });
            //$user->token = $token;
            //$user->save();
            //$user->password = Hash::make(request()->new_password);

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
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id) {
        return view('frontend::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id) {
        //
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
