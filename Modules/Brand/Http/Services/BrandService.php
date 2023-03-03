<?php

namespace Modules\Brand\Http\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
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

        $response = [
            'res' => true,
            'msg' => '',
            'data' => $brand
        ];

        return $response;
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
        $request->brand_slug = Str::slug($request->brand_name, '-');

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

            //$brand->brand_slug = Str::slug($brand->brand_name, '-');
            //$brand->active = 1;
            //$brand->save();

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
                $status = $brand->save();
            }

            $profilePhoto = $request->profile_photo;
            if (isset($profilePhoto) && $profilePhoto != "") {
                $brand->profile_photo = $this->imageUpload($brandId, $profilePhoto, null, false);
                $status = $brand->save();
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
                $status = $brand->save();
            }
            if ($request->file('upload_contact_list')) {
                $fileName = Str::random(10) . '_cstmrs.' . $request->file('upload_contact_list')->extension();
                $request->file('upload_contact_list')->move($brandAbsPath, $fileName);
                $brand->upload_contact_list = $brandRelPath . $fileName;
                $status = $brand->save();
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
     * Update the specified resource in storage.
     * @param int $brand
     * @param int $id
     * @return Stringable
     */
    private function imageUpload($brand, $image, $previousFile, $replaceable)
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
