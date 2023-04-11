<?php

namespace Modules\Brand\Http\Services;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Modules\Country\Entities\Country;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductImage;
use Modules\Product\Entities\ProductVariation;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Brand\Entities\Catalog;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;


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
                    $reader = new ReaderXlsx();
                    $spreadsheet = $reader->load($brandAbsPath . $fileName);
                    $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                    unset($sheet[2]);
                    if (!empty($sheet)) {
                        foreach ($sheet as $data) {
                            $name = $data['A'];
                            $description = $data['D'];
                            $country = $data['E'];
                            $caseQuantity = $data['F'];
                            $minOrderQty = $data['G'];
                            $sku = $data['I'];
                            $usdWholesale = (float)$data['P'];
                            $usdRetail = (float)$data['Q'];
                            $cadWholesale = (float)$data['R'];
                            $cadRetail = (float)$data['S'];
                            $gbrWholesale = (float)$data['T'];
                            $gbrRetail = (float)$data['U'];
                            $eurWholesale = (float)$data['V'];
                            $eurRetail = (float)$data['W'];
                            $usdTester = (float)$data['X'];
                            $fabricContent = $data['AC'];
                            $careInstruction = $data['AD'];
                            $season = $data['AE'];
                            $occasion = $data['AF'];
                            $aesthetic = $data['AG'];
                            $fit = $data['AH'];
                            $preorder = $data['AL'];
                            $productShip = $data['AM'];
                            $productEndShip = $data['AN'];
                            $productDeadline = $data['AO'];
                            $image1 = $data['Y'];
                            $image2 = $data['Z'];
                            $image3 = $data['AA'];
                            $image4 = $data['AB'];

                            $qryCountry = Country::where("name", $country)->first();
                            if (!empty($qryCountry)) {
                                $countryId = $qryCountry->id;
                            } else {
                                $countryId = 0;
                            }

                            $productSlug = Str::slug($name, '-');
                            $qryProduct = Product::where('slug', $productSlug)->first();
                            if (empty($qryProduct)) {
                                $productKey = 'p_' . Str::lower(Str::random(10));
                                $productSlug = Str::slug($name, '-');
                                $featuredImage = '';
                                $productImages = [];
                                if (!empty($image1)) {
                                    $productImages[] = strpos($image1, 'http') !== false ? $image1 : asset('public') . '/uploads/products/' . $image1;
                                    $featuredImage = strpos($image1, 'http') !== false ? $image1 : asset('public') . '/uploads/products/' . $image1;
                                }
                                if (!empty($image2)) {
                                    $productImages[] = strpos($image2, 'http') !== false ? $image2 : asset('public') . '/uploads/products/' . $image2;
                                }
                                if (!empty($image3)) {
                                    $productImages[] = strpos($image3, 'http') !== false ? $image3: asset('public') . '/uploads/products/' . $image3;
                                }
                                if (!empty($image4)) {
                                    $productImages[] = strpos($image4, 'http') !== false ? $image4 : asset('public') . '/uploads/products/' . $image4;
                                }


                                $product = new Product();
                                $product->product_key = $productKey;
                                $product->slug = $productSlug;
                                $product->name = $name;
                                $product->user_id = $request->user_id;
                                $product->status = "unpublish";
                                $product->description = addslashes($description);
                                $product->country = $countryId;
                                $product->case_quantity = $caseQuantity ?? 0;
                                $product->min_order_qty = $minOrderQty ?? 0;
                                $product->sku = $sku;
                                $product->usd_wholesale_price = $usdWholesale ?? 0;
                                $product->usd_retail_price = $usdRetail ?? 0;
                                $product->cad_wholesale_price = $cadWholesale ?? 0;
                                $product->cad_retail_price = $cadRetail ?? 0;
                                $product->eur_wholesale_price = $eurWholesale ?? 0;
                                $product->eur_retail_price = $eurRetail ?? 0;
                                $product->gbr_wholesale_price = $gbrWholesale ?? 0;
                                $product->gbr_retail_price = $gbrRetail ?? 0;
                                $product->usd_tester_price = $usdTester ?? 0;
                                $product->care_instruction = $careInstruction;
                                $product->season = $season;
                                $product->Occasion = $occasion;
                                $product->Aesthetic = $aesthetic;
                                $product->Fit = $fit;
                                $product->Preorder = $preorder;
                                $product->featured_image = $featuredImage;
                                $product->country = $countryId;
                                $product->fabric_content = $fabricContent;
                                $product->product_shipdate = date('Y-m-d', strtotime($productShip));
                                $product->product_endshipdate = date('Y-m-d', strtotime($productEndShip));
                                $product->product_deadline = date('Y-m-d', strtotime($productDeadline));
                                $product->created_at = date('Y-m-d H:i:s');
                                $product->updated_at = date('Y-m-d H:i:s');
                                $product->save();
                                $lastInsertId = $product->id;

                                if (!empty($productImages)) {
                                    foreach ($productImages as $imgK => $img) {
                                        $featureKey = $imgK == 0 ? 1 : 0;
                                        $productImage = new ProductImage();
                                        $productImage->product_id = $lastInsertId;
                                        $productImage->images = $img;
                                        $productImage->feature_key = $featureKey;
                                        $productImage->save();
                                    }
                                }

                                $optName1 = str_replace("'", '"', $data['J']);
                                $optValue1 = str_replace("'", '"', $data['K']);
                                $optName2 = str_replace("'", '"', $data['L']);
                                $optValue2 = str_replace("'", '"', $data['M']);
                                $optName3 = str_replace("'", '"', $data['N']);
                                $optValue3 = str_replace("'", '"', $data['O']);
                                $optionTypes = 0;
                                if ($optName1 != '' && strtolower($optName1) != 'optional') {
                                    $optionTypes++;
                                }
                                if ($optName2 != '' && strtolower($optName2) != 'optional') {
                                    $optionTypes++;
                                }
                                if ($optName3 != '' && strtolower($optName3) != 'optional') {
                                    $optionTypes++;
                                }
                                $variations = [];


                                if ($optionTypes > 0) {
                                    $option1Values = explode(',', $optValue1);
                                    $option2Values = explode(',', $optValue2);
                                    $option3Values = explode(',', $optValue3);
                                    if (!empty($option3Values)) {
                                        foreach ($option3Values as $ok3 => $ov3) {
                                            if (!empty($option2Values)) {
                                                foreach ($option2Values as $ok2 => $ov2) {
                                                    if (!empty($option1Values)) {
                                                        foreach ($option1Values as $ok1 => $ov1) {
                                                            $variations[] = array(
                                                                'option1' => $optName1, 'option2' => $optName2, 'option3' => $optName3, 'value1' => $ov1, 'value2' => $ov2, 'value3' => $ov3, 'swatch_image' => '', 'sku' => '', 'wholesale_price' => $usdWholesale, 'retail_price' => $usdRetail, 'inventory' => 0, 'weight' => 0, 'length' => 0, 'length_unit' => '', 'width_unit' => '', 'height_unit' => '', 'width' => 0, 'height' => 0, 'dimension_unit' => '', 'weight_unit' => '', 'tariff_code' => 0
                                                            );
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                if (is_countable($variations) && count($variations) > 0) {
                                    foreach ($variations as $vars) {
                                        $vars['product_id'] = $lastInsertId;
                                        $vars['variant_key'] = 'v_' . Str::lower(Str::random(10));
                                        $vars['price'] = $vars['wholesale_price'];
                                        $vars['cad_wholesale_price'] = $cadWholesale;
                                        $vars['cad_retail_price'] = $cadRetail;
                                        $vars['eur_wholesale_price'] = $eurWholesale;
                                        $vars['eur_retail_price'] = $eurRetail;
                                        $productVariation = new ProductVariation();
                                        $productVariation->fill($vars);
                                        $productVariation->save();
                                    }
                                }
                            }
                        }
                    }
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
