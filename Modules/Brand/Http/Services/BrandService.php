<?php

namespace Modules\Brand\Http\Services;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Modules\Country\Entities\Country;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Brand\Entities\Catalog;


class BrandService
{
    protected Brand $brand;
    protected User $user;
    private string $brandAbsPath = "";
    private string $brandRelPath = "";

    public function __construct()
    {
        $this->brandAbsPath = public_path('uploads/brands');
        $this->brandRelPath = 'uploads/brands/';
    }

    /**
     * Save a new Brand
     *
     * @param array $requestData
     * @return array
     */
    public function store(array $requestData): array
    {

        $requestData["role"] = User :: ROLE_BRAND;
        $requestData["verified"] = 1;
        $user = $this->createUser($requestData);
        $requestData['user_id'] = $user->id;
        $requestData = Arr::except($requestData, ['email', 'password', 'first_name', 'last_name', 'role', 'verified']);
        $brand = $this->createBrand($requestData);

        return [
            'res' => true,
            'msg' => '',
            'data' => $brand
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
     * Create a new Brand
     *
     * @param array $brandData
     * @return Brand
     */
    public function createBrand(array $brandData): Brand
    {

        //set Brand data
        $brandData["brand_key"] = 'bmc_' . Str::lower(Str::random(10));

        $slug = Str::slug($brandData["brand_name"], '-');
        $count = Brand::where(DB::raw('lower(brand_name)'), strtolower($brandData["brand_name"]))->count();
        if ($count > 0) {
            $slug = $slug . '-' . $count;
        }
        $brandData["brand_slug"] = $slug;
        $brandData["bazaar_direct_link"] = $slug;


        //create Brand
        $brand = new Brand();
        $brand->fill($brandData);
        $brand->save();

        return $brand;
    }

    /**
     * Get a listing of the Brands
     *
     * @param $requestData
     * @return array
     */
    public function getBrands($requestData): array
    {

        $user = User::find($requestData->user_id);
        if ($user) {
            $brandUsers = User::where('country_id', $user->country_id)->where('role', 'brand')->get();
        } else {
            $brandUsers = User::where('role', 'brand')->get();
        }

        if ($brandUsers) {
            foreach ($brandUsers as $brandUser) {
                $brand = Brand::where('user_id', $brandUser['id'])->where('go_live', '2')->first();
                if ($brand) {
                    $data[] = array(
                        'brand_key' => $brand->bazaar_direct_link,
                        'brand_id' => $brand->id,
                        'brand_name' => $brand->brand_name,
                        'brand_logo' => $brand->logo_image != '' ? asset('public') . '/' . $brand->logo_image : asset('public/img/logo-image.png'),
                    );
                }

            }
        }

        return ['res' => true, 'msg' => "", 'data' => $data];
    }

    /**
     * Get the specified Brand
     *
     * @param int $userId
     * @return array
     */
    public function get(int $userId): array
    {

        $user = User::find($userId);
        $brand = Brand::where('user_id', $user->id)->first();

        // return error if no Brand found
        if (!$brand) {
            return [
                'res' => false,
                'msg' => 'Brand not found !',
                'data' => ""
            ];
        }

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

        return [
            'res' => true,
            'msg' => '',
            'data' => $brand
        ];
    }

    /**
     * Get the specified Brand's shop details
     *
     * @param string $brandKey
     * @return array
     */
    public function getShop(string $brandKey): array
    {

        $brand = Brand::where('bazaar_direct_link', $brandKey)->first();

        // return error if no Brand found
        if (!$brand) {
            return [
                'res' => false,
                'msg' => 'Brand not found !',
                'data' => ""
            ];
        }

        $brand->profile_photo = $brand->profile_photo != '' ? asset('public') . '/' . $brand->profile_photo : asset('public/img/profile-photo.png');
        $brand->featured_image = $brand->featured_image != '' ? asset('public') . '/' . $brand->featured_image : asset('public/img/featured-image.png');
        $brand->cover_image = $brand->cover_image != '' ? asset('public') . '/' . $brand->cover_image : asset('public/img/cover-image.png');
        $brand->logo_image = $brand->logo_image != '' ? asset('public') . '/' . $brand->logo_image : asset('public/img/logo-image.png');
        $brand->tools_used = $brand->tools_used != '' ? explode(',', $brand->tools_used) : array();
        $brand->tag_shop_page = $brand->tag_shop_page != '' ? explode(',', $brand->tag_shop_page) : array();

        //country
        $country = Country::where('id', $brand->country)->first();
        $brand->country = $country->name;
        //headquarter
        $headquarteredCountry = Country::where('id', $brand->headquatered)->first();
        $brand->headquatered = $headquarteredCountry->name;
        //shipped from
        $productShippedCountry = Country::where('id', $brand->product_shipped)->first();
        $brand->product_shipped = $productShippedCountry->name;

        return [
            'res' => true,
            'msg' => '',
            'data' => $brand
        ];
    }

    /**
     * Update the specified Brand.
     *
     * @param $request
     * @return array
     */
    public function update($request): array
    {
        $data = (array)$request->all();
        $request->bazaar_direct_link = Str::slug($request->bazaar_direct_link, '-');
        $existBrand = Brand::where('user_id', $request->user_id)->first();

        if ($existBrand) {
            if ($request->bazaar_direct_link && $request->bazaar_direct_link != $existBrand->bazaar_direct_link) {
                $existBrandLink = Brand::where('bazaar_direct_link', $request->bazaar_direct_link)->where('user_id', '<>', $existBrand->bazaar_direct_link)->first();

                if ($existBrandLink) {
                    $response = ['res' => false, 'msg' => 'Direct link exists!', 'data' => ''];
                    return $response;
                }
                $existBrand->bazaar_direct_link = $request->bazaar_direct_link;
                $existBrand->save();
            }
        }

        try {

            $brand = Brand::updateOrCreate(['user_id' => request()->user_id], $request->except(['email', 'password', 'first_name', 'last_name', 'featured_image', 'profile_photo', 'cover_image', 'bazaar_direct_link']));
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
                $brand->save();
            }

            $profilePhoto = $request->profile_photo;
            if (isset($profilePhoto) && $profilePhoto != "") {
                $brand->profile_photo = $this->imageUpload($brandId, $profilePhoto, null, false);
                $brand->save();
            }

            $coverImage = $request->cover_image;
            if (isset($coverImage) && $coverImage != "") {
                $brand->cover_image = $this->imageUpload($brandId, $coverImage, null, true);
                $brand->save();
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

                $fileName = Str::random(10) . '_photos.' . $request->file('upload_zip')->extension();
                $request->file('upload_zip')->move($brandAbsPath, $fileName);
                $brand->upload_zip = $brandRelPath . $fileName;
                $brand->save();
            }
            if ($request->file('upload_contact_list')) {
                $fileName = Str::random(10) . '_cstmrs.' . $request->file('upload_contact_list')->extension();
                $request->file('upload_contact_list')->move($brandAbsPath, $fileName);
                $brand->upload_contact_list = $brandRelPath . $fileName;
                $brand->save();
            }
            $response = ['res' => true, 'msg' => "", 'data' => $data];
        } catch (Exception $e) {
            $errorCode = $e->getCode();
            if ($errorCode == 23000) {
                $errorMessage = "direct link already exists";
            } else {
                $errorMessage = $e->getMessage();//"something went wrong, please try again";
            }
            $response = ['res' => false, 'msg' => $errorMessage, 'data' => $errorCode];

        }
        return $response;
    }

    /**
     * Save image from base64 string.
     *
     * @param int $brand
     * @param $image
     * @param $previousFile
     * @param $replaceable
     * @return Stringable|string
     */
    private function imageUpload(int $brand, $image, $previousFile, $replaceable): Stringable|string
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
        $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
        $image_64 = str_replace($replace, '', $image_64);
        $image_64 = str_replace(' ', '+', $image_64);
        $imageName = Str::random(10) . '.' . 'png';
        File::put($brandAbsPath . $imageName, base64_decode($image_64));
        return $brandRelPath . $imageName;
    }

    /**
     * Update account details of the specified Brand.
     *
     * @param array $requestData
     * @return array
     */
    public function updateAccount(array $requestData): array
    {

        $user = User::find($requestData['user_id']);
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

        $brand = Brand::where('user_id', $requestData['user_id'])->first();
        $brand->country_code = $requestData['country_code'];
        $brand->phone_number = $requestData['phone_number'];
        $brand->save();

        return ['res' => true, 'msg' => "Successfully updated your account", 'data' => ''];

    }

    /**
     * Update shop details of the specified Brand.
     *
     * @param array $requestData
     * @return array
     */
    public function updateShop(array $requestData): array
    {
        $userId = $requestData['user_id'];
        $brand = Brand::where('user_id', $userId)->first();
        $brandId = $brand->id;

        $brand = Brand::updateOrCreate(['user_id' => $requestData['user_id']], Arr::except($requestData, ['email', 'featured_image', 'profile_photo', 'cover_image', 'logo_image']));
        if (isset($requestData['email'])) {
            $user = User::find($userId);
            $user->email = $requestData['email'];
            $user->save();
        }

        if (!filter_var($requestData['profile_photo'], FILTER_VALIDATE_URL)) {
            $brand->profile_photo = $this->imageUpload($brandId, $requestData['profile_photo'], null, false);
        }
        if (!filter_var($requestData['cover_image'], FILTER_VALIDATE_URL)) {
            $brand->cover_image = $this->imageUpload($brandId, $requestData['cover_image'], null, false);
        }
        if (!filter_var($requestData['featured_image'], FILTER_VALIDATE_URL)) {
            $brand->featured_image = $this->imageUpload($brandId, $requestData['featured_image'], null, false);
        }
        if (!filter_var($requestData['logo_image'], FILTER_VALIDATE_URL)) {
            $brand->logo_image = $this->imageUpload($brandId, $requestData['logo_image'], null, false);
        }
        $brand->first_visit = '1';
        $status = $brand->save();

        if ($status) {
            $response = ['res' => true, 'msg' => "Successfully updated your shop", 'data' => ''];
        } else {
            $response = ['res' => false, 'msg' => "Please try again!", 'data' => ''];
        }

        return $response;
    }

    /**
     * Update info details of the specified Brand.
     *
     * @param array $requestData
     * @return array
     */
    public function updateInfo(array $requestData): array
    {

        $brand = Brand::where('user_id', $requestData['user_id'])->first();
        if (!filter_var($requestData['profile_photo'], FILTER_VALIDATE_URL)) {
            $requestData['profile_photo'] = $this->imageUpload($brand->id, $requestData['profile_photo'], null, false);
        } else {
            $requestData['profile_photo'] = $brand->profile_photo;
        }
        $brand->fill($requestData);
        $brand->save();

        return ['res' => true, 'msg' => "Successfully updated your account", 'data' => ''];
    }

    /**
     * Update the specified Brand's status to live.
     *
     * @param int $brandId
     * @return array
     */
    public function liveShop(int $brandId): array
    {
        $brand = Brand::find($brandId);
        $brand->go_live = '2';
        $brand->save();

        return [
            'res' => true,
            'msg' => 'Your shop is now live !',
            'data' => ""
        ];
    }

    /**
     * Remove the specified Brand from storage.
     *
     * @param string $brandKey
     * @return array
     */
    public function delete(string $brandKey): array
    {
        $brand = Brand::where('Brand_key', $brandKey)->first();

        // return error if no Brand found
        if (!$brand) {
            return [
                'res' => false,
                'msg' => 'Brand not found !',
                'data' => ""
            ];
        }

        $brand->delete();

        return [
            'res' => true,
            'msg' => 'Brand successfully deleted',
            'data' => ""
        ];
    }
}
