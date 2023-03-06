<?php

namespace Modules\Brand\Http\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Modules\Brand\Entities\Catalog;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use File;


class BrandService
{
    protected Brand $brand;
    protected User $user;
    private $brandAbsPath = "";
    private $brandRelPath = "";

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

        $allBrandsCount = Brand::where('user_id', $requestData->user_id)->count();
        $draftBrandsCount = Brand::where('user_id', $requestData->user_id)->where('status', 'draft')->count();
        $scheduledBrandsCount = Brand::where('user_id', $requestData->user_id)->where('status', 'schedule')->count();
        $completedBrandsCount = Brand::where('user_id', $requestData->user_id)->where('status', 'completed')->count();
        $brands = Brand::where('user_id', $requestData->user_id);
        $status = strtolower($requestData->status);
        if ($status !== 'all') {
            $brands->where('status', $status);
        }
        $paginatedBrands = $brands->paginate(10);
        $filteredBrands = [];
        if ($paginatedBrands) {
            foreach ($paginatedBrands as $brand) {
                $filteredBrands[] = array(
                    'title' => $brand->title,
                    'Brand_key' => $brand->Brand_key,
                    'updated_at' => date("F j, Y, g:i a", strtotime($brand->updated_at)),
                );
            }
        }
        $data = array(
            "Brands" => $filteredBrands,
            "allBrandsCount" => $allBrandsCount,
            "draftBrandsCount" => $draftBrandsCount,
            "scheduledBrandsCount" => $scheduledBrandsCount,
            "completedBrandsCount" => $completedBrandsCount,
        );

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
     * @param $request
     * @return mixed
     */
    public function update($request)
    {
        $data = (array)$request->all();
        $request->bazaar_direct_link = Str::slug($request->bazaar_direct_link, '-');
        $slug = Str::slug($request->brand_name, '-');
        $count = Brand::where('slug', $slug)->where('user_id', '<>', $request->user_id)->count();
        if ($count > 0) {
            $slug = $slug . '-' . $count;
        }
        $request->brand_slug = $slug;
        $exstBrand = Brand::where('user_id', $request->user_id)->first();

        if ($exstBrand) {

            if ($request->bazaar_direct_link && $request->bazaar_direct_link != $exstBrand->bazaar_direct_link) {
                $exstBrandLink = Brand::where('bazaar_direct_link', $request->bazaar_direct_link)->where('user_id', '<>', $exstBrand->bazaar_direct_link)->first();

                if ($exstBrandLink) {
                    $response = ['res' => false, 'msg' => 'Direct link exists!', 'data' => ''];
                    return $response;
                }
                $exstBrand->bazaar_direct_link = $request->bazaar_direct_link;
                $exstBrand->save();
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
     * @return Stringable
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
        $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
        $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
        $image_64 = str_replace($replace, '', $image_64);
        $image_64 = str_replace(' ', '+', $image_64);
        $imageName = Str::random(10) . '.' . 'png';
        File::put($brandAbsPath . $imageName, base64_decode($image_64));
        return $brandRelPath . $imageName;
    }

    public function updateAccount($request): array
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|regex:/^[a-zA-Z]+$/u|max:255',
            'last_name' => 'required|regex:/^[a-zA-Z]+$/u|max:255',
            'phone_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
        ]);
        if ($validator->fails()) {
            $response = ['res' => false, 'msg' => $validator->errors()->first(), 'data' => ""];
        } else {

            $user = User::find($request->user_id);
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $brand = Brand::where('user_id', $request->user_id)->first();
            $brand->country_code = $request->country_code;
            $brand->phone_number = $request->phone_number;
            $brand->save();

            $status = $user->save();
            if ($status) {
                if ($request->new_password != '') {
                    $validator2 = Validator::make($request->all(), [
                        'old_password' => 'required',
                        'new_password' => [
                            'required',
                            'different:old_password',
                            Password::min(8)
                                ->letters()
                                ->mixedCase()
                                ->numbers()
                                ->symbols()
                        ],
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

        return $response;
    }

    /**
     * @param $request
     * @return array
     */
    public function updateShop($request): array
    {
        $userId = $request->user_id;
        $brand = Brand::where('user_id', $request->user_id)->first();
        $brandId = $brand->id;
        $request->brand_slug = Str::slug($request->brand_name, '-');
        $validator = Validator::make($request->all(), [
            'email' => 'string|email|unique:users,email,' . $userId . ',id',
            'brand_slug' => 'string|unique:brands,brand_slug,' . $brandId . ',id',
            'brand_name' => 'string|max:255',
            'website_url' => ['regex:/^(?!(http|https)\.)\w+(\.\w+)+$/'],
            'insta_handle' => ['regex:/^(?!.*\.\.)(?!.*\.$)[^\W][\w.]{0,29}$/'],
            'established_year' => 'digits:4|integer|min:1900|max:' . date('Y'),
            'first_order_min' => 'numeric|min:1|max:99999',
            're_order_min' => 'numeric|min:1|max:99999',
            'avg_lead_time' => 'numeric|min:1|max:180',
            'product_made' => 'integer|exists:countries,id',
            'headquatered' => 'integer|exists:countries,id',
            'shared_brd_story' => 'string|max:1500',
            'tag_shop_page' => 'string|max:1500',
        ]);
        if ($validator->fails()) {
            $response = ['res' => false, 'msg' => $validator->errors()->first(), 'data' => ""];
        } else {

            $brand = Brand::updateOrCreate(['user_id' => request()->user_id], $request->except(['email', 'featured_image', 'profile_photo', 'cover_image', 'logo_image']));
            if (isset($request->email)) {
                $user = User::find($userId);
                $user->email = $request->email;
                $user->save();
            }
            $profilePhoto = $request->profile_photo;
            if (isset($profilePhoto) && $profilePhoto != "") {
                $brand->profile_photo = $this->imageUpload($brandId, $profilePhoto, $brand->profile_photo, true);
            }

            $coverImage = $request->cover_image;
            if (isset($coverImage) && $coverImage != "") {
                $brand->cover_image = $this->imageUpload($brandId, $coverImage, $brand->cover_image, true);
            }

            $featuredImage = $request->featured_image;
            if (isset($featuredImage) && $featuredImage != "") {
                $brand->featured_image = $this->imageUpload($brandId, $featuredImage, $brand->featured_image, true);
            }

            $logoImage = $request->logo_image;
            if (isset($logoImage) && $logoImage != "") {
                $brand->logo_image = $this->imageUpload($brandId, $logoImage, $brand->logo_image, true);
            }

            $brand->first_visit = '1';
            $status = $brand->save();
            if ($status) {
                $response = ['res' => true, 'msg' => "Successfully updated your account", 'data' => ''];
            } else {
                $response = ['res' => false, 'msg' => "Please try again!", 'data' => ''];
            }
        }

        return $response;
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
