<?php

namespace Modules\Retailer\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Modules\User\Entities\User;
use Modules\Retailer\Entities\Retailer;


class RetailerController extends Controller
{

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


}
