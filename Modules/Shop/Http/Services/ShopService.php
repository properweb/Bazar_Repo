<?php

namespace Modules\Shop\Http\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Brand\Entities\Brand;
use Modules\Retailer\Entities\Retailer;
use Modules\Category\Entities\Category;
use Modules\Country\Entities\Country;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductImage;
use Modules\Product\Entities\Video;
use Modules\Product\Entities\ProductVariation;
use Modules\Product\Entities\ProductPrepack;
use Modules\User\Entities\User;
use Modules\User\Entities\UserRecentView;
use Modules\Wishlist\Entities\Wishlist;
use Modules\Cart\Entities\Cart;
use Modules\Shop\Entities\Testimonial;
use Modules\Promotion\Entities\Promotion;
use Modules\Promotion\Entities\PromotionProduct;


class ShopService
{

    public function __construct()
    {
    }

    /**
     * Get a listing of the products by brand
     *
     * @param $request
     * @return array
     */
    public function getBrandProducts($request): array
    {
        $productArray = [];
        $categories = [];
        $allProductsCount = 0;
        $newProductsCount = 0;
        $brandDetails = Brand::where('bazaar_direct_link', $request->brand_id)->where('go_live', '2')->first();
        if ($brandDetails) {
            $userId = $brandDetails->user_id;
            //all products count
            $allProductsCount = Product::where('user_id', $userId)->where('status', 'publish')->count();

            //new products count
            $newProductsCount = Product::where('user_id', $userId)->where('status', 'publish')->where('created_at', '>', now()->subDays(7)->endOfDay())->count();

            $productCategories = Product::select(DB::raw("count(*) as prdct_count"), "category")->where('status', 'publish')->where('user_id', $userId)->groupBy('category')->get();

            foreach ($productCategories as $productCategory) {
                if ($productCategory->category != 0) {
                    $categoryDetails = Category::find($productCategory->category);
                    $mainCategoryDetails = Category::where('id', $categoryDetails->parent_id)->where('parent_id', 0)->first();
                    $productSubCategories = Product::select(DB::raw("count(*) as prdct_count"), "sub_category")->where('status', 'publish')->where('category', $productCategory->category)->where('user_id', $userId)->groupBy('sub_category')->get();
                    $subCategories = [];
                    foreach ($productSubCategories as $productSubCategory) {
                        $subCategoryDetails = Category::find($productSubCategory->sub_category);
                        $subCategories[] = array(
                            "pcount" => $productSubCategory->prdct_count,
                            "name" => $subCategoryDetails->title,
                            "slug" => $mainCategoryDetails->slug . '|' . $categoryDetails->slug . '|' . $subCategoryDetails->slug,
                        );
                    }
                    $categoryArray = array(
                        "pcount" => $productCategory->prdct_count,
                        "name" => $categoryDetails->title,
                        "slug" => $mainCategoryDetails->slug . '|' . $categoryDetails->slug,
                        "subcategories" => $subCategories
                    );
                } else {
                    $categoryArray = array(
                        "pcount" => $productCategory->prdct_count,
                        "name" => 'Uncategorized',
                        "slug" => 'uncategorized',
                        "subcategories" => []
                    );
                }

                $categories[] = $categoryArray;
            }
            $allProductQuery = Product::where('user_id', $userId)->where('status', 'publish');
            switch ($request->sort_key) {
                case 2:
                    $allProductQuery->orderBy('updated_at', 'DESC');
                    break;
                case 3:
                    $allProductQuery->orderBy('usd_retail_price', 'ASC');
                    break;
                case 4:
                    $allProductQuery->orderBy('usd_retail_price', 'DESC');
                    break;
                default:
                    $allProductQuery->orderBy('order_by', 'ASC');
                    break;
            }
            switch ($request->sort_cat) {
                case 'all':
                    break;
                case 'new':
                    $allProductQuery->where('created_at', '>', now()->subDays(7)->endOfDay());
                    break;
                case 'uncategorized':
                    $allProductQuery->where('category', 0);
                    break;
                default:
                    if ($request->sort_cat != '') {
                        if (str_contains($request->sort_cat, '|')) {
                            $cat_arr = explode('|', $request->sort_cat);
                            if (isset($cat_arr[0]) && $cat_arr[0] != '') {
                                $mainCategoryDetails = Category::where('slug', $cat_arr[0])->where('parent_id', 0)->first();
                                $allProductQuery->where('main_category', $mainCategoryDetails->id);
                                if (isset($cat_arr[1]) && $cat_arr[1] != '') {
                                    $categoryDetails = Category::where('slug', $cat_arr[1])->where('parent_id', $mainCategoryDetails->id)->first();
                                    $allProductQuery->where('category', $categoryDetails->id);
                                    if (isset($cat_arr[2]) && $cat_arr[2] != '') {
                                        $subcategoryDetails = Category::where('slug', $cat_arr[2])->where('parent_id', $categoryDetails->id)->first();
                                        $allProductQuery->where('sub_category', $subcategoryDetails->id);
                                    }
                                }
                            }
                        }
                    }
                    break;
            }

            $products = $allProductQuery->get();
            if ($products) {
                foreach ($products as $v) {
                    $stock = $v->stock;
                    $usdWholesalePrice = $v->usd_wholesale_price ?? 0;
                    $usdRetailPrice = $v->usd_retail_price ?? 0;
                    $productOptionsCount = ProductVariation::where('product_id', $v->id)->where('status', '1')->count();
                    if ($productOptionsCount > 0) {
                        $productOptionsCount = ProductVariation::where('product_id', $v->id)->where('status', '1')->sum('stock');
                        $stock = $productOptionsCount;
                        $productFirstVariation = ProductVariation::where('product_id', $v->id)->where('status', '1')->first();
                        $usdWholesalePrice = $productFirstVariation->price ?? 0;
                        $usdRetailPrice = $productFirstVariation->retail_price ?? 0;
                    }

                    $productArray[] = array(
                        'id' => $v->id,
                        'product_key' => $v->product_key,
                        'name' => $v->name,
                        'category' => $v->category,
                        'status' => $v->status,
                        'description' => strip_tags($v->description),
                        'country' => $v->country,
                        'case_quantity' => $v->case_quantity,
                        'min_order_qty' => $v->min_order_qty,
                        'min_order_qty_type' => $v->min_order_qty_type,
                        'sku' => $v->sku,
                        'usd_wholesale_price' => $usdWholesalePrice,
                        'usd_retail_price' => $usdRetailPrice,
                        'cad_wholesale_price' => $v->cad_wholesale_price,
                        'cad_retail_price' => $v->cad_retail_price,
                        'gbr_wholesale_price' => $v->gbr_wholesale_price,
                        'gbr_retail_price' => $v->gbr_retail_price,
                        'eur_wholesale_price' => $v->eur_wholesale_price,
                        'eur_retail_price' => $v->eur_retail_price,
                        'usd_tester_price' => $v->usd_tester_price,
                        'fabric_content' => $v->fabric_content,
                        'care_instruction' => $v->care_instruction,
                        'season' => $v->season,
                        'Occasion' => $v->Occasion,
                        'Aesthetic' => $v->Aesthetic,
                        'Fit' => $v->Fit,
                        'Secondary_Occasion' => $v->Secondary_Occasion,
                        'Secondary_Aesthetic' => $v->Secondary_Aesthetic,
                        'Secondary_Fit' => $v->Secondary_Fit,
                        'Preorder' => $v->Preorder,
                        'slug' => $v->slug,
                        'featured_image' => $v->featured_image,
                        'stock' => $stock,
                        'default_currency' => $v->default_currency

                    );
                }
            }
        }


        $data = array(
            "categories" => $categories,
            "allprdcts_count" => $allProductsCount,
            "newprdcts_count" => $newProductsCount,
            "products" => $productArray,
        );

        return ['res' => true, 'msg' => "", 'data' => $data];
    }

    /**
     * Get a listing of the products by category
     *
     * @param $request
     * @return array
     */
    public function getCategoryProducts($request): array
    {
        $products = [];
        $categories = [];
        $filterBrand = $request->brandSort ?? [];
        $filterLeadTime = $request->leadTimeSort ?? 0;
        $filterMinOrder = $request->minOrderSort ?? 0;
        $filterBrandValues = $request->valuesSort ?? [];
        $filterLocation = $request->valuesSort ?? [];
        $filterPromotion = $request->valuesSort ?? [];
        $sortType = $request->sortKey;
        $fetchProducts = Product::where('status', 'publish');


        if ($request->main_category) {
            $mainCategory = Category::where('parent_id', 0)->where('status', '1')->where('title', $request->main_category)->first();
            if ($mainCategory) {
                $fetchProducts->where('main_category', $mainCategory->id);
                $categoriesByMainCategory = Category::where('parent_id', $mainCategory->id)->where('status', '1')->get();
                if ($categoriesByMainCategory) {
                    foreach ($categoriesByMainCategory as $category) {
                        $categories[] = array(
                            "id" => $category->id,
                            "title" => $category->title,
                            "image" => $category->image != '' ? asset('public') . '/' . $category->image : asset('public/img/nav-category-image.png'),
                        );
                    }
                }
            }
            if ($request->category) {
                $category = Category::where('parent_id', $mainCategory->id)->where('status', '1')->where('title', $request->category)->first();
                if ($category) {
                    $fetchProducts->where('category', $category->id);
                }
                if ($request->sub_category) {
                    $subCategory = Category::where('parent_id', $category->id)->where('status', '1')->where('title', $request->sub_category)->first();
                    if ($subCategory) {
                        $fetchProducts->where('category', $subCategory->id);
                    }
                }
            }
        }

        if (!empty($filterBrand)) {
            $fetchProducts->whereIn('user_id', $filterBrand);
        }
        if ($sortType == 'new') {
            $fetchProducts->orderBy('created_at', 'DESC');
        }
        $resultedProducts = $fetchProducts->get();
        if ($resultedProducts) {
            foreach ($resultedProducts as $resultedProduct) {
                $brandDetails = Brand::where('user_id', $resultedProduct->user_id)->first();

                if ($filterLeadTime != 0 && $brandDetails->avg_lead_time > $filterLeadTime) {
                    continue;
                }
                if ($filterMinOrder != 0 && $brandDetails->first_order_min > $filterMinOrder) {
                    continue;
                }

                $stock = $resultedProduct->stock;
                $usdWholesalePrice = $resultedProduct->usd_wholesale_price ?? 0;
                $usdRetailPrice = $resultedProduct->usd_retail_price ?? 0;
                $productOptionsCount = ProductVariation::where('product_id', $resultedProduct->id)->where('status', '1')->count();
                if ($productOptionsCount > 0) {
                    $productOptionsCount = ProductVariation::where('product_id', $resultedProduct->id)->where('status', '1')->sum('stock');
                    $stock = $productOptionsCount;
                    $productFirstVariation = ProductVariation::where('product_id', $resultedProduct->id)->where('status', '1')->first();
                    $usdWholesalePrice = $productFirstVariation->price ?? 0;
                    $usdRetailPrice = $productFirstVariation->retail_price ?? 0;
                }

                $products[] = array(
                    'id' => $resultedProduct->id,
                    'product_key' => $resultedProduct->product_key,
                    'name' => $resultedProduct->name,
                    'slug' => $resultedProduct->slug,
                    'brand_name' => $brandDetails->brand_name,
                    'sku' => $resultedProduct->sku,
                    'usd_wholesale_price' => $usdWholesalePrice,
                    'usd_retail_price' => $usdRetailPrice,
                    'featured_image' => $resultedProduct->featured_image,
                    'stock' => $stock,
                    'default_currency' => $resultedProduct->default_currency
                );

            }
        }
        $data = array(
            "products" => $products,
            "categories" => $categories,
        );

        return ['res' => true, 'msg' => "", 'data' => $data];
    }

    /**
     * Get a listing of the filters for products
     *
     * @param $request
     * @return array
     */
    public function getProductFilters($request): array
    {
        $brands = [];
        $countries = [];

        $allProductQuery = Product::where('status', 'publish');
        if ($request->main_category) {
            $mainCategory = Category::where('parent_id', 0)->where('status', '1')->where('title', $request->main_category)->first();
            if ($mainCategory) {
                $allProductQuery->where('main_category', $mainCategory->id);
            }
            if ($request->category) {
                $category = Category::where('parent_id', $mainCategory->id)->where('status', '1')->where('title', $request->category)->first();
                if ($category) {
                    $allProductQuery->where('category', $category->id);
                }
                if ($request->sub_category) {
                    $subCategory = Category::where('parent_id', $category->id)->where('status', '1')->where('title', $request->sub_category)->first();
                    if ($subCategory) {
                        $allProductQuery->where('category', $subCategory->id);
                    }
                }
            }
        }
        switch ($request->sort_key) {
            case 2:
                $allProductQuery->orderBy('updated_at', 'DESC');
                break;
            case 3:
                $allProductQuery->orderBy('usd_retail_price', 'ASC');
                break;
            case 4:
                $allProductQuery->orderBy('usd_retail_price', 'DESC');
                break;
            default:
                $allProductQuery->orderBy('order_by', 'ASC');
                break;
        }
        switch ($request->sort_cat) {
            case 'all':
                break;
            case 'new':
                $allProductQuery->where('created_at', '>', now()->subDays(7)->endOfDay());
                break;
            case 'uncategorized':
                $allProductQuery->where('category', 0);
                break;
            default:
                if ($request->sort_cat != '') {
                    if (str_contains($request->sort_cat, '|')) {
                        $cat_arr = explode('|', $request->sort_cat);
                        if (isset($cat_arr[0]) && $cat_arr[0] != '') {
                            $mainCategoryDetails = Category::where('slug', $cat_arr[0])->where('parent_id', 0)->first();
                            $allProductQuery->where('main_category', $mainCategoryDetails->id);
                            if (isset($cat_arr[1]) && $cat_arr[1] != '') {
                                $categoryDetails = Category::where('slug', $cat_arr[1])->where('parent_id', $mainCategoryDetails->id)->first();
                                $allProductQuery->where('category', $categoryDetails->id);
                                if (isset($cat_arr[2]) && $cat_arr[2] != '') {
                                    $subcategoryDetails = Category::where('slug', $cat_arr[2])->where('parent_id', $categoryDetails->id)->first();
                                    $allProductQuery->where('sub_category', $subcategoryDetails->id);
                                }
                            }
                        }
                    }
                }
                break;
        }

        $products = $allProductQuery->get();
        if ($products) {
            foreach ($products as $v) {

                if (!array_key_exists($v->country, $brands)) {
                    $countryDetails = Country::find($v->country);
                    $countries[$v->country] = array(
                        "id" => $v->country,
                        "value" => $countryDetails->name
                    );
                }

                if (!array_key_exists($v->user_id, $brands)) {
                    $brandDetails = Brand::where('user_id', $v->user_id)->first();
                    $brands[$v->user_id] = array(
                        "id" => $v->user_id,
                        "value" => $brandDetails->brand_name
                    );
                }
            }
        }

        $data = array(
            "brands" => array_values($brands),
            "countries" => array_values($countries),
        );

        return ['res' => true, 'msg' => "", 'data' => $data];
    }

    /**
     * Get product details by product id
     *
     * @param int $productId
     * @return array
     */
    public function getProduct(int $productId): array
    {

        $data = array();
        $user = auth()->user();
        $productDetails = Product::find($productId);

        if ($productDetails) {
            if ($user) {
                $wishList = Wishlist::where('product_id', $productDetails->id)->where('user_id', $user->id)->where('cart_id', null)->first();
            }
            if (!empty($wishList)) {
                $wishlistId = $wishList->id;
            } else {
                $wishlistId = '';
            }
            $data['id'] = $productDetails->id;
            $data['name'] = $productDetails->name;
            $data['description'] = $productDetails->description;
            $data['usd_wholesale_price'] = $productDetails->usd_wholesale_price;
            $data['usd_retail_price'] = $productDetails->usd_retail_price;
            $data['case_quantity'] = $productDetails->case_quantity;
            $data['min_order_qty'] = $productDetails->min_order_qty;
            $data['default_currency'] = $productDetails->default_currency;
            $data['sell_type'] = $productDetails->sell_type;
            $data['wishlistId'] = $wishlistId;
            $brandDetails = Brand::where('user_id', $productDetails->user_id)->first();
            if ($brandDetails) {
                $data['brand_name'] = $brandDetails->brand_name;
                $data['brand_id'] = $productDetails->user_id;
                $data['brand_logo'] = $brandDetails->logo_image != '' ? asset('public') . '/' . $brandDetails->logo_image : asset('public/img/logo-image.png');
                $data['brand_direct_link'] = $brandDetails->bazaar_direct_link;
                $data['brand_avg_lead_time'] = $brandDetails->avg_lead_time;
                //shipped from
                $productShipped = Country::find($brandDetails->product_shipped);
                $data['shipped_from'] = $productShipped ? $productShipped->name : '';
            }

            $promotionDiscountAmount = 0;
            $promotion = Promotion::where('user_id', $productDetails->user_id)
                ->where('promotion_type', 'product')
                ->where('status', 'active')
                ->where('from_date', '<=', date('Y-m-d'))
                ->where('to_date', '>=', date('Y-m-d'))
                ->first();
            if ($promotion) {
                $productPromotion = PromotionProduct::where('promotion_id', $promotion->id)
                    ->where('product_id', $productDetails->id)
                    ->first();
                if ($productPromotion) {
                    $discountedPrice = $productDetails->usd_wholesale_price * ($promotion->discount_amount / 100);
                    $promotionDiscountAmount = $productDetails->usd_wholesale_price - round($discountedPrice, 2);
                }
            }

            $data['discounted_price'] = $promotionDiscountAmount;
            //shop-wide promotion banner
            $promotions = Promotion::where('user_id', $productDetails->user_id)
                ->where('promotion_type', 'order')
                ->where('status', 'active')
                ->where('from_date', '<=', date('Y-m-d'))
                ->where('to_date', '>=', date('Y-m-d'))
                ->orderBy('discount_amount', 'ASC')
                ->get();
            $shopPromotion = '';
            if (!empty($promotions)) {
                $promotionDetails = Promotion::where('user_id', $productDetails->user_id)
                    ->where('promotion_type', 'order')
                    ->where('status', 'active')
                    ->where('from_date', '<=', date('Y-m-d'))
                    ->where('to_date', '>=', date('Y-m-d'))
                    ->orderBy('discount_amount', 'DESC')
                    ->first();
                if ($promotionDetails) {
                    if ($promotionDetails->discount_type === 1) {
                        $maxDiscountAmount = $promotionDetails->discount_amount . '%';
                    } else {
                        $maxDiscountAmount = '$' . $promotionDetails->discount_amount;
                    }
                    if (count($promotions) > 1) {
                        $shopPromotion = 'Up to ' . $maxDiscountAmount . ' off';
                    } else {
                        $shopPromotion = $maxDiscountAmount . ' off orders $' . $promotionDetails->ordered_amount . '+';
                    }
                }
            }
            $data['shop_wide_promotion'] = $shopPromotion;

            $productImages = ProductImage::where('product_id', $productDetails->id)->get();
            $productVideos = Video::where('product_id', $productDetails->id)->get()->toArray();
            $images = array();
            if (!empty($productImages)) {
                foreach ($productImages as $img) {
                    $images[] = array(
                        'image_id' => $img->id,
                        'image' => $img->images,
                        'feature_key' => $img->feature_key,
                    );
                }
            }
            $data['images'] = $images;
            $data['videos'] = $productVideos;
            $productVariations = ProductVariation::where('product_id', $productDetails->id)->where('status', '1')->get();
            $productPrepacks = ProductPrepack::where('product_id', $productDetails->id)->where('active', '1')->get();
            if (!empty($productPrepacks)) {
                $prepackVar = array();
                foreach ($productPrepacks as $key => $var) {
                    if ($user) {
                        $preWishList = Wishlist::where('product_id', $productDetails->id)->where('user_id', $user->id)->where('cart_id', null)->where('variant_id', $var->id)->first();
                    }
                    if (!empty($preWishList)) {
                        $preWishListId = $preWishList->id;
                    } else {
                        $preWishListId = '';
                    }

                    $prepackVar[] = array(
                        'id' => $var->id,
                        'style' => $var->style,
                        'pack_name' => $var->pack_name,
                        'size_ratio' => $var->size_ratio,
                        'size_range' => $var->size_range,
                        'packs_price' => $var->packs_price,
                        'active' => $var->active,
                        'created_at' => $var->created_at,
                        'updated_at' => $var->updated_at,
                        'variationWishId' => $preWishListId
                    );


                }
            }

            $data['prepacks'] = $prepackVar;


            $allvariations = array();
            $swatches = array();
            $swatch_imgs = array();
            $options = array();
            $values1 = array();
            $values2 = array();
            $values3 = array();
            $variations = array();
            if (!empty($productVariations)) {
                foreach ($productVariations as $key => $var) {
                    $values = [];
                    if ($var->value1 != '') {
                        array_push($values, $var->value1);
                    }
                    if ($var->value2 != '') {
                        array_push($values, $var->value2);
                    }
                    if ($var->value3 != '') {
                        array_push($values, $var->value3);
                    }
                    $values_str = implode('_', $values);
                    if ($user) {
                        $variationWishList = Wishlist::where('product_id', $productDetails->id)->where('user_id', $user->id)->where('cart_id', null)->where('variant_id', $var->id)->first();
                    }
                    if (!empty($variationWishList)) {
                        $variationWishId = $variationWishList->id;
                    } else {
                        $variationWishId = '';
                    }
                    $promotionDiscountAmount = 0;
                    if ($productPromotion) {
                        $discountedPrice = $var->price * ($promotion->discount_amount / 100);
                        $promotionDiscountAmount = $var->price - round($discountedPrice, 2);
                    }

                    $variations[$values_str] = array(
                        'variant_id' => $var->id,
                        'option1' => ucfirst(strtolower($var->options1)),
                        'option2' => ucfirst(strtolower($var->options2)),
                        'option3' => ucfirst(strtolower($var->options3)),
                        'value1' => $var->value1,
                        'value2' => $var->value2,
                        'value3' => $var->value3,
                        'sku' => $var->sku,
                        'wholesale_price' => $var->price,
                        'retail_price' => $var->retail_price,
                        'inventory' => $var->stock,
                        'preview_images' => $var->image,
                        'swatch_image' => $var->swatch_image,
                        'values' => $values,
                        'variationWishId' => $variationWishId,
                        'discounted_price' => $promotionDiscountAmount
                    );
                    if ($var->options1 != null && $var->value1 != null) {
                        $option = ucfirst(strtolower($var->options1));
                        $allvariations[$key][$option] = $var->value1;
                        if (!in_array($option, $options)) {
                            array_push($options, $option);
                        }
                        if (!in_array($var->value1, $values1) && $var->value1 != null) {
                            array_push($values1, $var->value1);
                        }
                    }
                    if ($var->options2 != null && $var->value2 != null) {
                        $option = ucfirst(strtolower($var->options2));
                        $allvariations[$key][$option] = $var->value2;
                        if (!in_array($option, $options)) {
                            array_push($options, $option);
                        }
                        if (!in_array($var->value2, $values2)) {
                            array_push($values2, $var->value2);
                        }
                    }
                    if ($var->options3 != null && $var->value3 != null) {
                        $option = ucfirst(strtolower($var->options3));
                        $allvariations[$key][$option] = $var->value3;
                        if (!in_array($option, $options)) {
                            array_push($options, $option);
                        }
                        if (!in_array($var->value3, $values3)) {
                            array_push($values3, $var->value3);
                        }
                    }

                    if (!in_array($var->swatch_image, $swatch_imgs) && $var->swatch_image != '') {
                        array_push($swatch_imgs, $var->swatch_image);
                    }
                }
            }
            $data['variations'] = $variations;

            if (in_array('Color', $options)) {
                $key = array_search('Color', $options);
                switch ($key) {
                    case 0:
                        $colors = $values1;
                        break;
                    case 1:
                        $colors = $values2;
                        break;
                    case 2:
                        $colors = $values3;
                        break;
                    default:
                        break;
                }

                if (!empty($colors)) {
                    foreach ($colors as $ck => $color) {
                        $swatches[] = ["name" => $color, "img" => isset($swatch_imgs[$ck]) ? $swatch_imgs[$ck] : ''];
                    }
                }
            }

            $values = [];

            if (!empty($values1)) {
                foreach ($values1 as $value1) {
                    $values[0][] = (object)["display" => $value1, "value" => $value1];
                }
            }

            if (!empty($values2)) {
                foreach ($values2 as $value2) {
                    $values[1][] = (object)["display" => $value2, "value" => $value2];
                }
            }

            if (!empty($values3)) {
                foreach ($values3 as $value3) {
                    $values[2][] = (object)["display" => $value3, "value" => $value3];
                }
            }

            $variationOptions = [];
            $variationColors = [];
            if (!empty($options)) {
                foreach ($options as $ok => $option) {
                    switch ($ok) {
                        case 1:
                            $values = $values2;
                            break;
                        case 2:
                            $values = $values3;
                            break;
                        default:
                            $values = $values1;
                            break;
                    }
                    if ($option == 'Color') {
                        $variationColors = $swatches;
                    }
                    $variationOptions[] = array(
                        'name' => $option,
                        'options' => $values
                    );
                }
            }
            $data['options'] = $options;
            $data['variation_options'] = $variationOptions;
            $data['variation_colors'] = $variationColors;
            $relatedProducts = [];
            if ($user) {
                $relatedProducts = Product::where('user_id', $productDetails->user_id)->where('id', '!=', $productDetails->id)->where('main_category', $productDetails->main_category)->where('status', 'publish')->inRandomOrder()->limit(9)->get();
            }
            $data['related_products'] = $relatedProducts;

            $recentViewedProdutcs = [];
            if ($user) {
                $retailerDet = User::where('id', $user->id)->where('role', 'retailer')->first();
                if ($retailerDet) {
                    $recent_views = UserRecentView::where('user_id', $user->id)->where('product_id', '!=', $productDetails->id)->orderBy('id', 'DESC')->get();
                    if ($recent_views) {
                        foreach ($recent_views as $view) {
                            $productDet = Product::find($view->product_id);
                            if ($productDet) {

                                $recentViewedProdutcs[] = array(
                                    'id' => $productDet->id,
                                    'product_key' => $productDet->product_key,
                                    'name' => $productDet->name,
                                    'status' => $productDet->status,
                                    'country' => $productDet->country,
                                    'case_quantity' => $productDet->case_quantity,
                                    'min_order_qty' => $productDet->min_order_qty,
                                    'min_order_qty_type' => $productDet->min_order_qty_type,
                                    'sku' => $productDet->sku,
                                    'usd_wholesale_price' => $productDet->usd_wholesale_price,
                                    'usd_retail_price' => $productDet->usd_retail_price,
                                    'cad_wholesale_price' => $productDet->cad_wholesale_price,
                                    'cad_retail_price' => $productDet->cad_retail_price,
                                    'gbr_wholesale_price' => $productDet->gbr_wholesale_price,
                                    'gbr_retail_price' => $productDet->gbr_retail_price,
                                    'eur_wholesale_price' => $productDet->eur_wholesale_price,
                                    'eur_retail_price' => $productDet->eur_retail_price,
                                    'usd_tester_price' => $productDet->usd_tester_price,
                                    'slug' => $productDet->slug,
                                    'featured_image' => $productDet->featured_image,
                                    'stock' => $productDet->stock

                                );
                            }
                        }
                    }
                    $last_viewed = UserRecentView::where('user_id', $user->id)->orderBy('id', 'DESC')->first();
                    if ($last_viewed) {
                        if ($last_viewed->product_id != $productDetails->id) {
                            $UserRecentView = new UserRecentView();
                            $UserRecentView->user_id = $user->id;
                            $UserRecentView->product_id = $productDetails->id;
                            $UserRecentView->save();
                        }
                    } else {
                        $UserRecentView = new UserRecentView();
                        $UserRecentView->user_id = $user->id;
                        $UserRecentView->product_id = $productDetails->id;
                        $UserRecentView->save();
                    }
                }
            }
            $data['rcntviwd_produtcs'] = $recentViewedProdutcs;

            return ['res' => true, 'msg' => "", 'data' => $data];
        } else {
            return ['res' => false, 'msg' => "Product not found !", 'data' => ''];
        }
    }

    /**
     * Get a listing of new Brands
     *
     * @return array
     */
    public function getNewBrands(): array
    {
        $data = [];
        $brandUsers = User::where('role', 'brand')->where('created_at', '>', now()->subDays(45)->endOfDay())->get();

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
     * Search brands or products
     *
     * @param Object $request
     * @return array
     */
    public function search(object $request): array
    {
        $product = [];
        $getBrand = [];

        $results = Product::leftJoin('categories as main', 'products.main_category', '=', 'main.id')
            ->leftJoin('categories as sub', 'products.category', '=', 'sub.id')
            ->leftJoin('categories as child', 'products.sub_category', '=', 'child.id')
            ->where('products.status', 'publish')
            ->where('products.name', 'like', '%'.$request->search.'%')
            ->orWhere('main.title', 'like', '%'.$request->search.'%' )
            ->orWhere('sub.title', 'like', '%'.$request->search.'%' )
            ->orWhere('child.title', 'like', '%'.$request->search.'%' )
            ->get(['products.name']);
        if ($results) {
            foreach ($results as $result) {
                $product[] = array(
                    'name' => $result->name,
                );
            }
        }
        $brands = Brand::where('brand_name', 'like', '%'.$request->search.'%')
            ->get();
        if ($brands) {
            foreach ($brands as $brand) {
                $getBrand[] = array(
                    'name' => $brand->brand_name,
                    'bazaar_direct_link'=> $brand->bazaar_direct_link,
                    'brand_logo' => $brand->logo_image != '' ? asset('public') . '/' . $brand->logo_image : asset('public/img/logo-image.png'),
                );
            }
        }

        return ['res' => true, 'msg' => "", 'product' => $product, 'brand' => $getBrand];
    }

    /**
     * Search brands or products
     *
     * @param Object $request
     * @return array
     */
    public function searchResult(object $request): array
    {
        $products = [];
        $results = Product::leftJoin('categories as main', 'products.main_category', '=', 'main.id')
            ->leftJoin('categories as sub', 'products.category', '=', 'sub.id')
            ->leftJoin('categories as child', 'products.sub_category', '=', 'child.id')
            ->where('products.status', 'publish')
            ->where('products.name', 'like', '%'.$request->search.'%')
            ->orWhere('main.title', 'like', '%'.$request->search.'%' )
            ->orWhere('sub.title', 'like', '%'.$request->search.'%' )
            ->orWhere('child.title', 'like', '%'.$request->search.'%' )
            ->get();;
        if (!empty($results)) {
            foreach ($results as $resultedProduct) {
                $brandDetails = Brand::where('user_id', $resultedProduct->user_id)->first();
                $stock = $resultedProduct->stock;
                $usdWholesalePrice = $resultedProduct->usd_wholesale_price ?? 0;
                $usdRetailPrice = $resultedProduct->usd_retail_price ?? 0;
                $productOptionsCount = ProductVariation::where('product_id', $resultedProduct->id)->where('status', '1')->count();
                if ($productOptionsCount > 0) {
                    $productOptionsCount = ProductVariation::where('product_id', $resultedProduct->id)->where('status', '1')->sum('stock');
                    $stock = $productOptionsCount;
                    $productFirstVariation = ProductVariation::where('product_id', $resultedProduct->id)->where('status', '1')->first();
                    $usdWholesalePrice = $productFirstVariation->price ?? 0;
                    $usdRetailPrice = $productFirstVariation->retail_price ?? 0;
                }

                $products[] = array(
                    'id' => $resultedProduct->id,
                    'product_key' => $resultedProduct->product_key,
                    'name' => $resultedProduct->name,
                    'slug' => $resultedProduct->slug,
                    'brand_name' => $brandDetails->brand_name,
                    'sku' => $resultedProduct->sku,
                    'usd_wholesale_price' => $usdWholesalePrice,
                    'usd_retail_price' => $usdRetailPrice,
                    'featured_image' => $resultedProduct->featured_image,
                    'stock' => $stock,
                    'default_currency' => $resultedProduct->default_currency
                );

            }
        }

        return ['res' => true, 'msg' => "", 'product' => $products];
    }

    /**
     * Get a listing of the product categories featured in home
     *
     * @return array
     */
    public function getTrendingCategories(): array
    {
        $recentCategories = [];
        $recentOrderedProducts = Cart::where('created_at', '>', now()->subDays(45)->endOfDay())->get();
        if ($recentOrderedProducts) {
            foreach ($recentOrderedProducts as $cartProduct) {
                $productDetails = Product::find($cartProduct->product_id);
                if ($productDetails) {
                    if (!in_array($productDetails->category, $recentCategories)) {
                        $recentCategories[] = $productDetails->category;
                    }
                }
            }
        }
        $featuredCategories = Category::where('id', $recentCategories)->where('status', '1')->get();
        if ($featuredCategories) {
            foreach ($featuredCategories as $featuredCategory) {
                $category = '';
                if ($featuredCategory->parent_id != 0) {
                    $parentCategory = Category::find($featuredCategory->parent_id);
                    if ($parentCategory->parent_id != 0) {
                        $parentParentCategory = Category::find($parentCategory->parent_id);
                        $mainCategory = $parentParentCategory->title;
                        $category = $parentCategory->title;
                        $categoryType = 'sub-category';
                    } else {
                        $category = $featuredCategory->title;
                        $mainCategory = $parentCategory->title;
                        $categoryType = 'category';
                    }
                } else {
                    $mainCategory = $featuredCategory->title;
                    $categoryType = 'main-category';
                }

                $data[] = array(
                    "id" => $featuredCategory->id,
                    "title" => $featuredCategory->title,
                    "main_category" => $mainCategory,
                    "category" => $category,
                    "cat_type" => $categoryType,
                    "image" => $featuredCategory->image != '' ? asset('public') . '/' . $featuredCategory->image : asset('public/img/featured-brand-image.png'),
                );

            }
        }

        return ['res' => true, 'msg' => "", 'data' => $data];
    }

    /**
     * Get a listing of the product categories featured in home
     *
     * @return array
     */
    public function getTestimonials(): array
    {
        $testimonials = Testimonial::get();
        if ($testimonials) {
            foreach ($testimonials as $testimonial) {
                $testimonial->image = $testimonial->image != '' ? asset('public') . '/' . $testimonial->image : asset('public/img/testimonial.png');
            }
        }

        return ['res' => true, 'msg' => "", 'data' => $testimonials];
    }

    /**
     * Get a listing of the brand reviews
     *
     * @param Request $request
     * @return array
     */
    public function getBrandReviews(Request $request): array
    {
        $reviews = [];
        $totalReviewsCount = 0;
        $product = Product::where('product_key', $request->product_key)->first();
        $brand = Brand::where('user_id', $product->user_id)->first();
        $orderReviewQuery = DB::raw("(SELECT * FROM order_reviews WHERE status='1') as r");// Raw query is needed as nested query using for this function with alias.
        $orderQuery = DB::table('orders as o')
            ->select('r.*')
            ->join($orderReviewQuery, 'r.order_id', '=', 'o.id')
            ->where('o.brand_id', $brand->id);
        $totalReviewsCount = $orderQuery->count();
        $orderReviews = $orderQuery->paginate(5);
        if (!empty($orderReviews)) {
            foreach ($orderReviews as $orderReview) {
                $retailer = Retailer::where('user_id', $orderReview->user_id)->first();
                $reviews[] = array(
                    'store_name' => $retailer->store_name,
                    'rate' => $orderReview->rate,
                    'review' => $orderReview->review,
                    'created_at' => date("d.m.Y", strtotime($orderReview->created_at)),
                );
            }
        }

        $data = array(
            "reviews" => $reviews,
            "total_reviews" => $totalReviewsCount
        );

        return ['res' => true, 'msg' => "", 'data' => $data];
    }
}