<?php

namespace Modules\Retailer\Http\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Modules\User\Entities\User;
use Modules\Retailer\Entities\Retailer;
use DB;


class RetailerService
{
    protected Retailer $retailer;
    protected User $user;

    public function __construct()
    {

    }

    /**
     * Save a new Retailer
     *
     * @param array $requestData
     * @return array
     */
    public function store(array $requestData): array
    {

        $requestData["role"] = User :: ROLE_RETAILER;
        $requestData["verified"] = 1;
        $user = $this->createUser($requestData);
        $requestData['user_id'] = $user->id;
        $requestData = Arr::except($requestData, ['email', 'password', 'first_name', 'last_name', 'role', 'verified']);
        $retailer = $this->createRetailer($requestData);

        return [
            'res' => true,
            'msg' => '',
            'data' => $retailer
        ];
    }

    /**
     * Create a new User
     *
     * @param array $userData
     * @return User
     */
    public function createUser(array $userData): User
    {
        $userData["password"] = Hash::make($userData['password']);
        //create User
        $user = new User();
        $user->fill($userData);
        $user->save();

        return $user;
    }

    /**
     * Create a new Retailer
     *
     * @param array $retailerData
     * @return Retailer
     */
    public function createRetailer(array $retailerData): Retailer
    {

        //set Retailer data
        $retailerData["retailer_key"] = 'r_' . Str::lower(Str::random(10));
        $retailerData["store_desc"] = !empty($retailerData["store_desc"]) ? implode(',', $retailerData["store_desc"]) : '';
        $retailerData["store_tags"] = !empty($retailerData["store_tags"]) ? implode(',', $retailerData["store_tags"]) : '';
        $retailerData["store_cats"] = !empty($retailerData["store_cats"]) ? implode(',', $retailerData["store_cats"]) : '';
        //create Retailer
        $retailer = new Retailer();
        $retailer->fill($retailerData);
        $retailer->save();

        return $retailer;
    }

    /**
     * Get the specified Retailer
     *
     * @param int $retailerId
     * @return array
     */
    public function get(int $retailerId): array
    {
        $retailer = Retailer::find($retailerId);
        $user = User::find($retailer->user_id);

        $country = DB::table('countries')->where('id', $retailer->country)->first();
        $state = DB::table('states')->where('id', $retailer->state)->first();
        $town = DB::table('cities')->where('id', $retailer->town)->first();
        $retailer->first_name = $user->first_name;
        $retailer->last_name = $user->last_name;
        $retailer->email = $user->email;
        $retailer->verified = $user->verified;
        $retailer->state_name = $state ? $state->name : '';
        $retailer->country_name = $country->name;
        $retailer->town_name = $town ? $town->name : '';

        return [
            'res' => true,
            'msg' => '',
            'data' => $retailer
        ];
    }

    /**
     * Update account details of the specified Retailer.
     *
     * @param array $requestData
     * @return array
     */
    public function update(array $requestData): array
    {

        $user = auth()->user();
        $user->first_name = $requestData['first_name'];
        $user->last_name = $requestData['last_name'];
        if (!empty($requestData['new_password'])) {
            if (Hash::check($requestData['old_password'], $user->password)) {
                $user->password = Hash::make($requestData['new_password']);
            } else {
                return ['res' => false, 'msg' => 'old password does not match our record.', 'data' => ""];
            }
        }
        $user->save();

        $retailer = Retailer::where('user_id', $user->id)->first();
        $retailer->country = $requestData['country'];
        $retailer->country_code = $requestData['country_code'];
        $retailer->phone_number = $requestData['phone_number'];
        $retailer->store_name = $requestData['store_name'];
        $retailer->store_type = $requestData['store_type'];
        $retailer->website_url = $requestData['website_url'];
        $retailer->save();

        return ['res' => true, 'msg' => "Successfully updated your account", 'data' => ''];

    }

}
