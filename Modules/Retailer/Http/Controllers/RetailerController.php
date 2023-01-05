<?php

namespace Modules\Retailer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Modules\Retailer\Entities\Retailer;
use Modules\User\Entities\User;


/**
 *
 */
class RetailerController extends Controller
{

    /**
     * @param Request $request
     * @return mixed
     */
    public function register(Request $request)
    {
        if ($request->retailer_id) {
            $validator = Validator::make($request->all(), []);
        } else {
            $validator = Validator::make($request->all(), [
                'email' => 'string|email|required|unique:users,email',
                'password' => 'required|min:6',
            ]);
        }
        if ($validator->fails()) {
            $response = ['res' => false, 'msg' => $validator->errors()->first(), 'data' => ""];
        } else {
            $data = (array)$request->all();
            if ($request->retailer_id) {
                $user = User::find($request->retailer_id);
            } else {
                $user = User::create(['email' => $data['email'], 'first_name' => $data['first_name'], 'last_name' => $data['last_name'], 'password' => Hash::make($data['password']), 'role' => 'retailer']);
            }
            if ($user) {
                $userId = $user->id;
                $retailer = Retailer::where('user_id', $user->id)->first();
                $retailerKey = $retailer ? $retailer->retailer_key : 'r_' . Str::lower(Str::random(10));
                $storeDesc = $request->store_desc && !empty($request->store_desc) ? implode(',', $request->store_desc) : '';
                $storeTags = $request->store_tags && !empty($request->store_tags) ? implode(',', $request->store_tags) : '';
                $storeCats = $request->store_cats && !empty($request->store_cats) ? implode(',', $request->store_cats) : '';
                request()->merge(array(
                    'retailer_key' => $retailerKey,
                    'store_desc' => $storeDesc,
                    'store_tags' => $storeTags,
                    'store_cats' => $storeCats,
                ));

                $retailer = Retailer::updateOrCreate(['user_id' => $userId], $request->except(['email', 'password', 'first_name', 'last_name', 'retailer_id']));
                $data['retailer_id'] = $userId;
                $data['retailer_key'] = $request->retailer_key;
                $response = ['res' => true, 'msg' => "Registered successfully!", 'data' => $data];
            } else {
                $response = ['res' => false, 'msg' => "Please try again!", 'data' => ""];
            }
        }
        return response()->json($response);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        $user = User::find($id);
        $retailer = Retailer::where('user_id', $user->id)->first();

        $retailer->first_name = $user->first_name;
        $retailer->last_name = $user->last_name;
        $retailer->email = $user->email;
        $retailer->verified = $user->verified;

        $response = ['res' => true, 'msg' => "", 'data' => $retailer];
        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request)
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
            $status = $user->save();
            if ($status) {
                $retailer = Retailer::where('user_id', $request->user_id)->first();
                $retailer->country_code = $request->country_code;
                $retailer->country = $request->country;
                $retailer->phone_number = $request->phone_number;
                $retailer->language = $request->language;
                $retailer->store_name = $request->store_name;
                $retailer->store_type = $request->store_type;
                $retailer->sign_up_for_email = $request->sign_up_for_email;
                $retailer->website_url = $request->website_url;
                $retailer->save();
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
}
