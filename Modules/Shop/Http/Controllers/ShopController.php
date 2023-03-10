<?php

namespace Modules\Shop\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\User\Entities\User;
use Modules\User\Entities\UserRecentView;
use Modules\Brand\Entities\Brand;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductImage;
use Modules\Product\Entities\Video;
use Modules\Product\Entities\ProductVariation;
use Modules\Product\Entities\ProductPrepack;
use Modules\Product\Entities\Category;
use Modules\Country\Entities\Country;
use Modules\Wishlist\Entities\Wishlist;
use DB;

class ShopController extends Controller {

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function brand(Request $request, $id) {
        $data = [];
        $brand = Brand::where('bazaar_direct_link', $id)->first();
        if ($brand) {
            $brand->profile_photo = $brand->profile_photo != '' ? asset('public') . '/' . $brand->profile_photo : asset('public/img/profile-photo.png');
            $brand->featured_image = $brand->featured_image != '' ? asset('public') . '/' . $brand->featured_image : asset('public/img/featured-image.png');
            $brand->cover_image = $brand->cover_image != '' ? asset('public') . '/' . $brand->cover_image : asset('public/img/cover-image.png');
            $brand->logo_image = $brand->logo_image != '' ? asset('public') . '/' . $brand->logo_image : asset('public/img/logo-image.png');
            $brand->tools_used = $brand->tools_used != '' ? explode(',', $brand->tools_used) : array();
            $brand->tag_shop_page = $brand->tag_shop_page != '' ? explode(',', $brand->tag_shop_page) : array();
            //country
            $country = Country::where('id', $brand->country)->first();
            $brand->country = $country->name;
            //headquater
            $headquatered = Country::where('id', $brand->headquatered)->first();
            $brand->headquatered = $headquatered->name;
            //shipped from
            $productShipped = Country::where('id', $brand->product_shipped)->first();
            $brand->product_shipped = $productShipped->name;
            $data = $brand;
        }

        $response = ['res' => true, 'msg' => "", 'data' => $data];
        return response()->json($response);
    }

    public function products(Request $request) {

        $product_arr = [];
        $categories = [];
        $allProductsCount = 0;
        $newProductsCount = 0;
        $brandDetails = Brand::where('bazaar_direct_link', $request->brand_id)->where('go_live', '2')->first();
        if ($brandDetails) {
            $brndUsrId = $brandDetails->user_id;
            //all products count
            $allProductsCount = Product::where('user_id', $brndUsrId)->where('status', 'publish')->count();

            //new products count
            $newProductsCount = Product::where('user_id', $brndUsrId)->where('status', 'publish')->where('created_at', '>', now()->subDays(7)->endOfDay())->count();
            $categories = [];
            $categoryRes = Product::select(DB::raw("count(*) as prdct_count"), "category")->where('status', 'publish')->where('user_id', $brndUsrId)->groupBy('category')->get();

            foreach ($categoryRes as $cat) {
                if ($cat->category != 0) {
                    $categoryDetails = Category::find($cat->category);
                    $maincategoryDetails = Category::where('id', $categoryDetails->parent_id)->where('parent_id', 0)->first();
                    $scatres = Product::select(DB::raw("count(*) as prdct_count"), "sub_category")->where('status', 'publish')->where('category', $cat->category)->where('user_id', $brndUsrId)->groupBy('sub_category')->get();
                    $subCategories = [];
                    foreach ($scatres as $scat) {
                        $scategoryDetails = Category::where('id', $scat->sub_category)->first();
                        $subCategories[] = array(
                            "pcount" => $scat->prdct_count,
                            "name" => $scategoryDetails->name,
                            "slug" => $maincategoryDetails->slug . '|' . $categoryDetails->slug . '|' . $scategoryDetails->slug,
                        );
                    }
                    $cat_array = array(
                        "pcount" => $cat->prdct_count,
                        "name" => $categoryDetails->name,
                        "slug" => $maincategoryDetails->slug . '|' . $categoryDetails->slug,
                        "subcategories" => $subCategories
                    );
                } else {
                    $cat_array = array(
                        "pcount" => $cat->prdct_count,
                        "name" => 'Uncategorized',
                        "slug" => 'uncategorized',
                        "subcategories" => []
                    );
                }

                $categories[] = $cat_array;
            }
            $allPrdctQuery = Product::where('user_id', $brndUsrId)->where('status', 'publish');
            switch ($request->sort_key) {
                case 1:
                    $allPrdctQuery->orderBy('order_by', 'ASC');
                    break;
                case 2:
                    $allPrdctQuery->orderBy('updated_at', 'DESC');
                    break;
                case 3:
                    $allPrdctQuery->orderBy('usd_retail_price', 'ASC');
                    break;
                case 4:
                    $allPrdctQuery->orderBy('usd_retail_price', 'DESC');
                    break;
                default:
                    $allPrdctQuery->orderBy('order_by', 'ASC');
                    break;
            }
            switch ($request->sort_cat) {
                case 'all':
                    break;
                case 'new':
                    $allPrdctQuery->where('created_at', '>', now()->subDays(7)->endOfDay());
                    break;
                case 'uncategorized':
                    $allPrdctQuery->where('category', 0);
                    break;
                default:
                    if ($request->sort_cat != '') {
                        if (strpos($request->sort_cat, '|') !== false) {
                            $cat_arr = explode('|', $request->sort_cat);
                            if (isset($cat_arr[0]) && $cat_arr[0] != '') {
                                $maincategoryDetails = Category::where('slug', $cat_arr[0])->where('parent_id', 0)->first();
                                $allPrdctQuery->where('main_category', $maincategoryDetails->id);
                            }
                            if (isset($cat_arr[1]) && $cat_arr[1] != '') {
                                $categoryDetails = Category::where('slug', $cat_arr[1])->where('parent_id', $maincategoryDetails->id)->first();
                                $allPrdctQuery->where('category', $categoryDetails->id);
                            }
                            if (isset($cat_arr[2]) && $cat_arr[2] != '') {
                                $subcategoryDetails = Category::where('slug', $cat_arr[2])->where('parent_id', $categoryDetails->id)->first();
                                $allPrdctQuery->where('sub_category', $subcategoryDetails->id);
                            }
                        }
                    }
                    break;
            }

            $products = $allPrdctQuery->get();
            if ($products) {
                foreach ($products as $v) {
                    $stock = $v->stock;
                    $prdctOptionsCount = ProductVariation::where('product_id', $v->id)->where('status', '1')->count();
                    //return count of product options if any
                    if ($prdctOptionsCount > 0) {
                        $prdctOptionsCount = ProductVariation::where('product_id', $v->id)->where('status', '1')->sum('stock');
                        $stock = $prdctOptionsCount;
                    }


                    $product_arr[] = array(
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
                        'usd_wholesale_price' => $v->usd_wholesale_price,
                        'usd_retail_price' => $v->usd_retail_price,
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
            "products" => $product_arr,
        );

        $response = ['res' => true, 'msg' => "", 'data' => $data];
        return response()->json($response);
    }

    public function product(Request $request) {

        $data = array();

        $productDetails = Product::where('product_key', $request->id)->first();
        if ($productDetails) {
            $wishList = Wishlist::where('product_id', $productDetails->id)->where('user_id', $request->user_id)->where('cart_id', null)->where('variant_id', null)->first();


            if(!empty($wishList))
            {
                $wishlistId = $wishList->id;
            }
            else
            {
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
                $data['brand_logo'] = $brandDetails->logo_image != '' ? asset('public') . '/' . $brandDetails->logo_image : asset('public/img/logo-image.png');
                $data['brand_direct_link'] = $brandDetails->bazaar_direct_link;
                $data['brand_avg_lead_time'] = $brandDetails->avg_lead_time;
                //shipped from
                $productShipped = DB::table('countries')->where('id', $brandDetails->product_shipped)->first();
                $data['shipped_from'] = $productShipped->name;
            }
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

                    $preWishList = Wishlist::where('product_id', $productDetails->id)->where('user_id', $request->user_id)->where('cart_id', null)->where('variant_id', $var->id)->first();
                    if(!empty($preWishList))
                    {
                        $preWishListId = $preWishList->id;
                    }
                    else
                    {
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

                    $variationWishList = Wishlist::where('product_id', $productDetails->id)->where('user_id', $request->user_id)->where('cart_id', null)->where('variant_id', $var->id)->first();
                    if(!empty($variationWishList))
                    {
                        $variationWishId = $variationWishList->id;
                    }
                    else
                    {
                        $variationWishId = '';
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
                        'variationWishId' => $variationWishId
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
                    $values[0][] = (object) ["display" => $value1, "value" => $value1];
                }
            }

            if (!empty($values2)) {
                foreach ($values2 as $value2) {
                    $values[1][] = (object) ["display" => $value2, "value" => $value2];
                }
            }

            if (!empty($values3)) {
                foreach ($values3 as $value3) {
                    $values[2][] = (object) ["display" => $value3, "value" => $value3];
                }
            }

            $variationOptions = [];
            $variationColors = [];
            if (!empty($options)) {
                foreach ($options as $ok => $option) {
                    switch ($ok) {
                        case 0:
                            $values = $values1;
                            break;
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

            $relatedProducts = Product::where('user_id', $productDetails->user_id)->where('id', '!=', $productDetails->id)->where('main_category', $productDetails->main_category)->where('status', 'publish')->inRandomOrder()->limit(9)->get();
            $data['related_products'] = $relatedProducts;

            $recentViewedProdutcs = [];
            $retailerDet = User::where('id', $request->user_id)->where('role', 'retailer')->first();
            if ($retailerDet) {
                $recent_views = UserRecentView::where('user_id', $request->user_id)->where('product_id', '!=', $productDetails->id)->orderBy('id', 'DESC')->get();
                if ($recent_views) {
                    foreach ($recent_views as $view) {
                        $productDet = Product::find($view->product_id);
                        if($productDet) {
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
                $last_viewed = UserRecentView::where('user_id', $request->user_id)->orderBy('id', 'DESC')->first();
                if ($last_viewed) {
                    if ($last_viewed->product_id != $productDetails->id) {
                        $UserRecentView = new UserRecentView();
                        $UserRecentView->user_id = $request->user_id;
                        $UserRecentView->product_id = $productDetails->id;
                        $UserRecentView->save();
                    }
                } else {
                    $UserRecentView = new UserRecentView();
                    $UserRecentView->user_id = $request->user_id;
                    $UserRecentView->product_id = $productDetails->id;
                    $UserRecentView->save();
                }
            }
            $data['rcntviwd_produtcs'] = $recentViewedProdutcs;

            $response = ['res' => true, 'msg' => "", 'data' => $data];
        } else {
            $response = ['res' => true, 'msg' => "No record found", 'data' => ''];
        }
        return response()->json($response);
    }

}
