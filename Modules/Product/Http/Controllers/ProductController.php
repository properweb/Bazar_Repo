<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redis;
use DB;
use File;
use Session;
use Illuminate\Support\Str;
use Modules\Product\Entities\Products;
use Modules\Product\Entities\Video;
use Modules\Product\Entities\ProductVariation;
use Modules\Product\Entities\ProductImage;
use Modules\Product\Entities\ProductPrepack;
use Modules\Product\Entities\Category;

class ProductController extends Controller
{
    public function index()
    {
        return view('product::index');
    }

    public function create(Request $request)
    {
        $vndr_upload_path = '/uploads/products/';
        $folderPath = public_path() . $vndr_upload_path;
        $product_images = $_FILES['product_images'];
        $usd_wholesale_price = $request->input('usd_wholesale_price') && !in_array($request->input('usd_wholesale_price'), array('undefined', 'null')) ? $request->input('usd_wholesale_price') : 0;
        $usd_retail_price = $request->input('usd_retail_price') && !in_array($request->input('usd_wholesale_price'), array('undefined', 'null')) ? $request->input('usd_retail_price') : 0;
        $cad_wholesale_price = $request->input('cad_wholesale_price') && !in_array($request->input('cad_wholesale_price'), array('undefined', 'null')) ? $request->input('cad_wholesale_price') : 0;
        $cad_retail_price = $request->input('cad_retail_price') && !in_array($request->input('cad_retail_price'), array('undefined', 'null')) ? $request->input('cad_retail_price') : 0;
        $gbp_wholesale_price = $request->input('gbp_wholesale_price') && !in_array($request->input('gbp_wholesale_price'), array('undefined', 'null')) ? $request->input('gbp_wholesale_price') : 0;
        $gbp_retail_price = $request->input('gbp_retail_price') && !in_array($request->input('gbp_retail_price'), array('undefined', 'null')) ? $request->input('gbp_retail_price') : 0;
        $eur_wholesale_price = $request->input('eur_wholesale_price') && !in_array($request->input('eur_wholesale_price'), array('undefined', 'null')) ? $request->input('eur_wholesale_price') : 0;
        $eur_retail_price = $request->input('eur_retail_price') && !in_array($request->input('eur_retail_price'), array('undefined', 'null')) ? $request->input('eur_retail_price') : 0;
        $aud_wholesale_price = $request->input('aud_wholesale_price') && !in_array($request->input('aud_wholesale_price'), array('undefined', 'null')) ? $request->input('aud_wholesale_price') : 0;
        $aud_retail_price = $request->input('aud_retail_price') && !in_array($request->input('aud_retail_price'), array('undefined', 'null')) ? $request->input('aud_retail_price') : 0;
        $tester_price = $request->input('testers_price') && !in_array($request->input('testers_price'), array('undefined', 'null')) ? $request->input('testers_price') : 0;
        $shipping_tariff_code = $request->input('shipping_tariff_code') && !in_array($request->input('shipping_tariff_code'), array('undefined', 'null')) ? $request->input('shipping_tariff_code') : '';
        $case_quantity = $request->input('order_case_qty') ? $request->input('order_case_qty') : 0;
        $min_case_quantity = $request->input('order_min_case_qty') ? $request->input('order_min_case_qty') : 0;
        $sell_type = $request->input('sell_type');
        $prepack_type = $sell_type == 3 ? $request->input('prepack_type') : 1;
        if ($request->input('shipping_inventory') == 'undefined') {
            $stock = 0;
        } else {
            $stock = $request->input('shipping_inventory');
        }
        $main_category = '';
        $category = '';
        $outside_us = $request->input('outside_us') == 'true' ? 1 : 0;
        $sub_category = $request->input('product_type');
        $sub_category_details = Category::where('id', $sub_category)->first();

        if ($sub_category_details) {
            $category_details = Category::where('id', $sub_category_details->parent_id)->first();
            $category = $category_details->id;
            $main_category = $category_details->parent_id;
        }
        $user_id = $request->input('user_id');
        $product_key = 'p_' . Str::lower(Str::random(10));
        $product_slug = Str::slug($request->input('product_name'));

        $ProductAdd = new Products();
        $ProductAdd->product_key = $product_key;
        $ProductAdd->slug = $product_slug;
        $ProductAdd->name = addslashes($request->input('product_name'));
        $ProductAdd->user_id = $request->input('user_id');
        $ProductAdd->main_category = $main_category;
        $ProductAdd->category = $category;
        $ProductAdd->sub_category = $sub_category;
        $ProductAdd->status = "publish";
        $ProductAdd->description = addslashes($request->input('description'));
        $ProductAdd->country = $request->input('product_made');
        $ProductAdd->case_quantity = $case_quantity;
        $ProductAdd->min_order_qty = $min_case_quantity;
        $ProductAdd->sku = $request->input('shipping_sku');
        $ProductAdd->usd_wholesale_price = $usd_wholesale_price;
        $ProductAdd->usd_retail_price = $usd_retail_price;
        $ProductAdd->cad_wholesale_price = $cad_wholesale_price;
        $ProductAdd->cad_retail_price = $cad_retail_price;
        $ProductAdd->gbp_wholesale_price = $gbp_wholesale_price;
        $ProductAdd->gbp_retail_price = $gbp_retail_price;
        $ProductAdd->eur_wholesale_price = $eur_wholesale_price;
        $ProductAdd->eur_retail_price = $eur_retail_price;
        $ProductAdd->gbr_wholesale_price = $aud_wholesale_price;
        $ProductAdd->gbr_retail_price = $aud_retail_price;
        $ProductAdd->usd_tester_price = $tester_price;
        $ProductAdd->created_date = date('Y-m-d');
        $ProductAdd->dimension_unit = $request->input('dimension_unit');
        $ProductAdd->is_bestseller = $request->input('is_bestseller');
        $ProductAdd->shipping_height = $request->input('shipping_height');
        $ProductAdd->stock = $stock;
        $ProductAdd->shipping_length = $request->input('shipping_length');
        $ProductAdd->shipping_weight = $request->input('shipping_weight');
        $ProductAdd->shipping_width = $request->input('shipping_width');
        $ProductAdd->weight_unit = $request->input('weight_unit');
        $ProductAdd->reatailers_inst = addslashes($request->input('reatailers_inst'));
        $ProductAdd->reatailer_input_limit = $request->input('reatailer_input_limit');
        $ProductAdd->retailer_min_qty = $request->input('retailer_min_qty');
        $ProductAdd->retailer_add_charge = $request->input('retailer_add_charge');
        $ProductAdd->product_shipdate = date('Y-m-d', strtotime($request->input('product_shipdate')));
        $ProductAdd->product_endshipdate = date('Y-m-d', strtotime($request->input('product_endshipdate')));
        $ProductAdd->product_deadline = date('Y-m-d', strtotime($request->input('product_deadline')));
        $ProductAdd->out_of_stock = $request->input('out_of_stock');
        $ProductAdd->keep_product = $request->input('keep_product');
        $ProductAdd->sell_type = $sell_type;
        $ProductAdd->prepack_type = $prepack_type;
        $ProductAdd->outside_us = $outside_us;
        $ProductAdd->tariff_code = $shipping_tariff_code;
        $ProductAdd->created_at = date('Y-m-d H:i:s');
        $ProductAdd->updated_at = date('Y-m-d H:i:s');
        $ProductAdd->save();

        $product_last_id = DB::getPdo()->lastInsertId();
        if (isset($_FILES['video_url']['name']) && is_countable($_FILES['video_url']['name']) && count($_FILES['video_url']['name']) > 0) {
            for ($i = 0; $i < count($_FILES['video_url']['name']); $i++) {
                $target_path = public_path() . $vndr_upload_path . "/";
                $ext = pathinfo($_FILES['video_url']['name'][$i], PATHINFO_EXTENSION);
                $img = rand() . time() . '.' . $ext;
                $target_path = $target_path . $img;
                move_uploaded_file($_FILES['video_url']['tmp_name'][$i], $target_path);
                if ($ext != "") {
                    $imgurl = url('/') . '/public/uploads/products/' . $img;
                } else {
                    $imgurl = '';
                }
                $VideoAdd = new Video();
                $VideoAdd->product_id = $product_last_id;
                $VideoAdd->video_url = $imgurl;
                $VideoAdd->save();
            }
        }

        $variation_images = [];
        $featured_key = isset($request->featured_image) && $request->featured_image != "" ? (int)$request->featured_image : 0;
        $target_path = public_path() . $vndr_upload_path . "/";
        $folderPath = public_path() . $vndr_upload_path . "/";
        $excel_file_names = $_FILES['product_images']['name'];
        if (is_countable($excel_file_names) && count($excel_file_names) > 0) {
            $folderPath = public_path() . $vndr_upload_path . "/";
            for ($i = 0; $i < count($excel_file_names); $i++) {
                $file_name = $excel_file_names[$i];
                $tmp_arr = explode(".", $file_name);
                $extension = end($tmp_arr);
                $file_url = Str::random(10) . '.' . $extension;
                move_uploaded_file($_FILES["product_images"]["tmp_name"][$i], $folderPath . $file_url);
                if ($extension != "") {
                    $productimgurl = url('/') . '/public/uploads/products/' . $file_url;
                } else {
                    $productimgurl = '';
                }
                $Imagedd = new ProductImage();
                $Imagedd->product_id = $product_last_id;
                $Imagedd->images = $productimgurl;
                $Imagedd->image_sort = $i;
                $Imagedd->feature_key = 0;
                $Imagedd->save();
                $variation_images[] = $productimgurl;
            }
        }
        ProductImage::where('image_sort', $featured_key)->where('product_id', $product_last_id)
            ->update([
                'feature_key' => 1
            ]);
        $product_image = ProductImage::where('product_id', $product_last_id)->where('image_sort', $featured_key)->first();
        $featured_image = $product_image->images;
        Products::where('id', $product_last_id)
            ->update([
                'featured_image' => $featured_image
            ]);
        $option_types = explode(',', $request->input('option_type'));
        $color_key = in_array('Color', $option_types) ? array_search("Color", $option_types) + 1 : 0;
        $colors = array();
        $swatches = array();
        $color_options = json_decode($request->input('colorOptionItems'), true);
        if (is_countable($color_options) && count($color_options) > 0) {
            foreach ($color_options as $color) {
                if (!in_array($color['name'], $colors)) {
                    $colors[] = $color['name'];
                    $swatch_image = $color["img"];
                    if (isset($swatch_image) && $swatch_image != "") {
                        $image_64 = $swatch_image;
                        $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                        $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                        $image = str_replace($replace, '', $image_64);
                        $image = str_replace(' ', '+', $image);
                        $image_name = Str::random(10) . '.' . 'png';
                        File::put(public_path() . $vndr_upload_path . "/" . $image_name, base64_decode($image));
                        $swatch_image = url('/') . '/public/uploads/products/' . $image_name;
                        $swatches[] = $swatch_image;
                    }
                }
            }
        }
        $variations = json_decode($request->input('variations'), true);

        if (is_countable($variations) && count($variations) > 0) {
            foreach ($variations as $vars) {
                $simage = '';
                if ($color_key > 0) {
                    $color_val = $vars['value' . $color_key];
                    $color_k = in_array($color_val, $colors) ? array_search($color_val, $colors) : 0;
                    $simage = $swatches[$color_k];
                }
                $image_index = (int)$vars["image_index"];
                $vimage = $variation_images[$image_index] ?? '';

                $cad_wholesale_price = $vars['cad_wholesale_price'] && !in_array($vars['cad_wholesale_price'], array('undefined', 'null')) ? $vars['cad_wholesale_price'] : 0;
                $cad_retail_price = $vars['cad_retail_price'] && !in_array($vars['cad_retail_price'], array('undefined', 'null')) ? $vars['cad_retail_price'] : 0;
                $gbp_wholesale_price = $vars['gbp_wholesale_price'] && !in_array($vars['gbp_wholesale_price'], array('undefined', 'null')) ? $vars['gbp_wholesale_price'] : 0;
                $gbp_retail_price = $vars['gbp_retail_price'] && !in_array($vars['gbp_retail_price'], array('undefined', 'null')) ? $vars['gbp_retail_price'] : 0;
                $eur_wholesale_price = $vars['eur_wholesale_price'] && !in_array($vars['eur_wholesale_price'], array('undefined', 'null')) ? $vars['eur_wholesale_price'] : 0;
                $eur_retail_price = $vars['eur_retail_price'] && !in_array($vars['eur_retail_price'], array('undefined', 'null')) ? $vars['eur_retail_price'] : 0;
                $aud_wholesale_price = $vars['aud_wholesale_price'] && !in_array($vars['aud_wholesale_price'], array('undefined', 'null')) ? $vars['aud_wholesale_price'] : 0;
                $aud_retail_price = $vars['aud_retail_price'] && !in_array($vars['aud_retail_price'], array('undefined', 'null')) ? $vars['aud_retail_price'] : 0;

                $variant_key = 'v_' . Str::lower(Str::random(10));

                $productvariation = new ProductVariation();
                $productvariation->variant_key = $variant_key;
                $productvariation->swatch_image = $simage;
                $productvariation->image = $vimage;
                $productvariation->product_id = $product_last_id;
                $productvariation->price = $vars['wholesale_price'];
                $productvariation->options1 = $vars['option1'];
                $productvariation->options2 = $vars['option2'];
                $productvariation->options3 = $vars['option3'];
                $productvariation->sku = $vars['sku'];
                $productvariation->value1 = $vars['value1'];
                $productvariation->value2 = $vars['value2'];
                $productvariation->value3 = $vars['value3'];
                $productvariation->retail_price = $vars['retail_price'];
                $productvariation->cad_wholesale_price = $cad_wholesale_price;
                $productvariation->cad_retail_price = $cad_retail_price;
                $productvariation->gbp_wholesale_price = $gbp_wholesale_price;
                $productvariation->gbp_retail_price = $gbp_retail_price;
                $productvariation->eur_wholesale_price = $eur_wholesale_price;
                $productvariation->eur_retail_price = $eur_retail_price;
                $productvariation->aud_wholesale_price = $aud_wholesale_price;
                $productvariation->aud_retail_price = $aud_retail_price;
                $productvariation->stock = $vars['inventory'];
                $productvariation->weight = $vars['weight'];
                $productvariation->length = $vars['length'];
                $productvariation->length_unit = $vars['length_unit'];
                $productvariation->width_unit = $vars['width_unit'];
                $productvariation->height_unit = $vars['height_unit'];
                $productvariation->width = $vars['width'];
                $productvariation->height = $vars['height'];
                $productvariation->dimension_unit = $vars['dimension_unit'];
                $productvariation->weight_unit = $vars['weight_unit'];
                $productvariation->tariff_code = $vars['tariff_code'];
                $productvariation->save();
            }
        }

        if ($request->input('sell_type') == 3) {
            $pre_packs = json_decode($request->input('pre_packs'), true);
            if ($pre_packs) {
                Products::where('id', $product_last_id)
                    ->update([
                        'sell_type' => 3
                    ]);
                foreach ($pre_packs as $pre_pack) {
                    $active = $pre_pack['active'];
                    $packs_price = $pre_pack['packs_price'] && !in_array($pre_pack['packs_price'], array('', 'undefined', 'null')) ? $pre_pack['packs_price'] : 0;
                    $ProductPrepack = new ProductPrepack();
                    $ProductPrepack->product_id = $product_last_id;
                    $ProductPrepack->style = $pre_pack['style'];
                    $ProductPrepack->pack_name = $pre_pack['pack_name'];
                    $ProductPrepack->size_ratio = $pre_pack['size_ratio'];
                    $ProductPrepack->size_range = $pre_pack['size_range_value'];
                    $ProductPrepack->packs_price = $packs_price;
                    $ProductPrepack->active = $active;
                    $ProductPrepack->save();
                }
            }
        }
        $redis = Redis::connection();
        Redis::flushDB();

        $response = ['res' => true, 'msg' => "Added Successfully", 'data' => ""];

        return response()->json($response);
    }

    public function fetchProductBySort(Request $request)
    {
        $redis = Redis::connection();
        $result_array = array();
        $redis = Redis::connection();
        $search = $request->search_key && !in_array($request->search_key, array('undefined', 'null')) ? $request->search_key : '';
        $existredis = Redis::exists("brandproduct:fetchproductsort:" . $request->page . ":" . $search . ":" . $request->status . ":" . $request->sort_key . ":" . $request->user_id);
        if ($existredis > 0) {
            $cachedproducts = Redis::get("brandproduct:fetchproductsort:" . $request->page . ":" . $search . ":" . $request->status . ":" . $request->sort_key . ":" . $request->user_id);
            $allfetchproduct = json_decode($cachedproducts, false);
            $response = ['res' => true, 'msg' => "", 'data' => $allfetchproduct];
        } else {
            $aprdct_query = Products::where('user_id', $request->user_id);
            $all_products_count = $aprdct_query->count();
            $pprdct_query = Products::where('user_id', $request->user_id)->where('status', 'publish');
            $published_products_count = $pprdct_query->count();
            $upprdct_query = Products::where('user_id', $request->user_id)->where('status', 'unpublish');
            $unpublished_products_count = $upprdct_query->count();
            $query = Products::where('user_id', $request->user_id);
            switch ($request->status) {
                case 'publish':
                    $query->where('status', 'publish');
                    break;
                case 'unpublish':
                    $query->where('status', 'unpublish');
                    break;
                default:
                    break;
            }
            switch ($request->sort_key) {
                case 1:
                    $query->orderBy('name', 'ASC');
                    break;
                case 2:
                    $query->orderBy('name', 'DESC');
                    break;
                case 3:
                    $query->orderBy('updated_at', 'DESC');
                    $query->orderBy('id', 'DESC');
                    //$query->orderByRaw("TIME(updated_at) DESC");
                    break;
                default:
                    $query->orderBy('name', 'ASC');
                    break;
            }
            if ($request->search_key && $request->search_key != '' && !in_array($request->search_key, array('undefined', 'null'))) {
                $query->where('name', 'Like', '%' . $request->search_key . '%');
            }
            $products = $query->paginate(10);
            foreach ($products as $v) {
                $product_variations = ProductVariation::where('product_id', $v->id)->where('status', '1')->get();
                $product_variations_count = $product_variations->count();
                $availability = 'out of stock';
                if ($product_variations_count > 0) {
                    $variant_minprice = ProductVariation::where('product_id', $v->id)->min('price');
                    $price = $variant_minprice . '+';
                    $variant_stock = ProductVariation::where('product_id', $v->id)->sum('price');
                    $availability = $variant_stock > 0 ? 'in stock' : 'out of stock';
                } else {
                    $price = $v->usd_wholesale_price;
                    $availability = $v->stock > 0 ? 'in stock' : 'out of stock';
                }
                $result_array[] = array(
                    'id' => $v->id,
                    'product_key' => $v->product_key,
                    'name' => $v->name,
                    'category' => $v->category,
                    'status' => $v->status,
                    'sku' => $v->sku,
                    'usd_wholesale_price' => $v->usd_wholesale_price,
                    'usd_retail_price' => $v->usd_retail_price,
                    'slug' => $v->slug,
                    'featured_image' => $v->featured_image,
                    'stock' => $v->stock,
                    'default_currency' => $v->default_currency,
                    'options_count' => $product_variations_count > 0 ? $product_variations_count : 1,
                    'price' => $price,
                    'availability' => $availability,
                    'website' => $v->website,
                    'import_type' => $v->import_type,
                );
            }

            $data = array(
                "products" => $result_array,
                "pblshprdcts_count" => $published_products_count,
                "unpblshprdcts_count" => $unpublished_products_count,
                "allprdcts_count" => $all_products_count
            );

            $allfetchproduct = $redis->set("brandproduct:fetchproductsort:" . $request->page . ":" . $search . ":" . $request->status . ":" . $request->sort_key . ":" . $request->user_id, json_encode($data));

            $response = ['res' => true, 'msg' => "", 'data' => $data];
        }


        return response()->json($response);
    }

    public function productdetails(Request $request)
    {
        $result_array = array();

        $redis = Redis::connection();
        $existredis = Redis::exists("productdetails:" . $request->id);
        if ($existredis > 0) {
            $cachedproducts = Redis::get("productdetails:" . $request->id);
            $allfetchproduct = json_decode($cachedproducts, false);
            $response = ['res' => true, 'msg' => "", 'data' => $allfetchproduct];
        } else {
            $products = Products::where('id', $request->id)->first();
            $prev_product = Products::where('user_id', $products->user_id)->where('id', '<', $request->id)->orderBy('id', 'DESC')->first();
            $prev_product_id = $prev_product ? $prev_product->id : 0;
            $next_product = Products::where('user_id', $products->user_id)->where('id', '>', $request->id)->orderBy('id', 'ASC')->first();
            $next_product_id = $next_product ? $next_product->id : 0;
            $brand_details = Products::where('user_id', $products->user_id)->first();
            $bazaar_direct_link = $brand_details->bazaar_direct_link;
            $product_images = ProductImage::where('product_id', $request->id)->get();
            $product_videos = Video::where('product_id', $request->id)->get()->toArray();
            $allimage = array();
            if (!empty($product_images)) {
                foreach ($product_images as $img) {
                    $allimage[] = array(
                        'image' => $img->images,
                        'feature_key' => $img->feature_key,
                        'image_id' => $img->id
                    );
                }
            }
            $product_variations = ProductVariation::where('product_id', $request->id)->where('status', '1')->get();
            $product_prepacks = ProductPrepack::where('product_id', $request->id)->get();
            $pre_packs = [];
            $prepack_sizeranges = [];
            if (!empty($product_prepacks)) {
                foreach ($product_prepacks as $ppkey => $ppval) {
                    if (!in_array($ppval->size_range, $prepack_sizeranges)) {
                        $prepack_sizeranges[] = $ppval->size_range;
                    }
                    $pre_packs[] = array(
                        'id' => $ppval->id,
                        'product_id' => $ppval->product_id,
                        'style' => $ppval->style,
                        'pack_name' => $ppval->pack_name,
                        'size_ratio' => $ppval->size_ratio,
                        'size_range_value' => $ppval->size_range,
                        'packs_price' => $ppval->packs_price,
                        'active' => $ppval->active,
                        'status' => 'published',
                    );
                }
            }
            if (!empty($pre_packs)) {
                foreach ($pre_packs as $pkey => $pval) {
                    $pre_packs[$pkey]['size_range'] = $prepack_sizeranges;
                }
            }

            $allvariations = array();
            $swatches = array();
            $swatch_imgs = array();
            $options = array();
            $values1 = array();
            $values2 = array();
            $values3 = array();

            if (!empty($product_variations)) {
                foreach ($product_variations as $key => $var) {
                    $allvariations[$key] = array(
                        'variant_id' => $var->id,
                        'variant_key' => $var->variant_key,
                        'option1' => $var->options1,
                        'option2' => $var->options2,
                        'option3' => $var->options3,
                        'value1' => $var->value1,
                        'value2' => $var->value2,
                        'value3' => $var->value3,
                        'sku' => $var->sku,
                        'usd_wholesale_price' => $var->price,
                        'usd_retail_price' => $var->retail_price,
                        'cad_wholesale_price' => $var->cad_wholesale_price,
                        'cad_retail_price' => $var->cad_retail_price,
                        'aud_wholesale_price' => $var->aud_wholesale_price,
                        'aud_retail_price' => $var->aud_retail_price,
                        'eur_wholesale_price' => $var->eur_wholesale_price,
                        'eur_retail_price' => $var->eur_retail_price,
                        'gbp_wholesale_price' => $var->gbp_wholesale_price,
                        'gbp_retail_price' => $var->gbp_retail_price,
                        'inventory' => $var->stock,
                        'weight' => $var->weight,
                        'length' => $var->length,
                        'weight_unit' => $var->weight_unit,
                        'length_unit' => $var->length_unit,
                        'width' => $var->width,
                        'height' => $var->height,
                        'width_unit' => $var->width_unit,
                        'height_unit' => $var->height_unit,
                        'dimension_unit' => $var->dimension_unit,
                        'tariff_code' => $var->tariff_code,
                        'preview_images' => $var->image,
                        'swatch_image' => $var->swatch_image,
                        'website' => $var->website,
                        'website_product_id' => $var->website_product_id,
                        'variation_id' => $var->variation_id,
                        'inventory_item_id' => $var->inventory_item_id,
                        'status' => 'published'
                    );
                    $variation_values = [];
                    $variation_options = [];
                    if ($var->options1 != null && $var->value1 != null) {
                        $option = ucfirst(strtolower($var->options1));
                        $allvariations[$key][$option] = $var->value1;
                        $variation_options[] = $option;
                        $variation_values[] = $var->value1;
                        if (!in_array($option, $options)) {
                            $options[] = $option;
                        }
                        if (!in_array($var->value1, $values1)) {
                            $values1[] = $var->value1;
                        }
                    }
                    if ($var->options2 != null && $var->value2 != null) {
                        $option = ucfirst(strtolower($var->options2));
                        $allvariations[$key][$option] = $var->value2;
                        $variation_options[] = $option;
                        $variation_values[] = $var->value2;
                        if (!in_array($option, $options)) {
                            $options[] = $option;
                        }
                        if (!in_array($var->value2, $values2)) {
                            $values2[] = $var->value2;
                        }
                    }
                    if ($var->options3 != null && $var->value3 != null) {
                        $option = ucfirst(strtolower($var->options3));
                        $allvariations[$key][$option] = $var->value3;
                        $variation_options[] = $option;
                        $variation_values[] = $var->value3;
                        if (!in_array($option, $options)) {
                            $options[] = $option;
                        }
                        if (!in_array($var->value3, $values3)) {
                            $values3[] = $var->value3;
                        }
                    }

                    $allvariations[$key]['variation_options'] = $variation_options;
                    $allvariations[$key]['variation_values'] = $variation_values;


                    if (!in_array($var->swatch_image, $swatch_imgs) && $var->swatch_image != '') {
                        $swatch_imgs[] = $var->swatch_image;
                    }
                }
            }

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
                        $swatches[] = (object)["name" => $color, "img" => $swatch_imgs[$ck] ?? ''];
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


            $qry = DB::table('product_attributes')
                ->select('product_attributes.*', 'product_attributes_keys.attributes')
                ->join('product_attributes_keys', 'product_attributes_keys.product_attributes_keys_id', '=', 'product_attributes.product_attributes_keys_id')
                ->where('product_attributes.product_id', '=', " . $request->id . ")
                ->get();

            $allattributes = array();
            if (count($qry) > 0) {
                foreach ($qry as $var) {
                    $allattributes[] = array(
                        'options' => $var->attributes,
                        'value' => $var->options
                    );
                }
            }

            $featured_image = ProductImage::where('product_id', $request->id)->where('feature_key', '1')->get()->first();
            $featured_image_key = ($featured_image) ? $featured_image->image_sort : 0;

            if ($products) {
                $result_array[] = array(
                    'id' => $products->id,
                    'bazaar_direct_link' => $bazaar_direct_link,
                    'name' => $products->name,
                    'category' => $products->sub_category,
                    'status' => $products->status,
                    'description' => strip_tags($products->description),
                    'country' => $products->country,
                    'case_quantity' => $products->case_quantity,
                    'min_order_qty' => $products->min_order_qty,
                    'min_order_qty_type' => $products->min_order_qty_type,
                    'sku' => $products->sku,
                    'usd_wholesale_price' => $products->usd_wholesale_price,
                    'usd_retail_price' => $products->usd_retail_price,
                    'cad_wholesale_price' => $products->cad_wholesale_price,
                    'cad_retail_price' => $products->cad_retail_price,
                    'gbr_wholesale_price' => $products->gbr_wholesale_price,
                    'gbr_retail_price' => $products->gbr_retail_price,
                    'eur_wholesale_price' => $products->eur_wholesale_price,
                    'eur_retail_price' => $products->eur_retail_price,
                    'gbp_wholesale_price' => $products->gbp_wholesale_price,
                    'gbp_retail_price' => $products->gbp_retail_price,
                    'usd_tester_price' => $products->usd_tester_price,
                    'fabric_content' => $products->fabric_content,
                    'care_instruction' => $products->care_instruction,
                    'season' => $products->season,
                    'Occasion' => $products->Occasion,
                    'Aesthetic' => $products->Aesthetic,
                    'Fit' => $products->Fit,
                    'Secondary_Occasion' => $products->Secondary_Occasion,
                    'Secondary_Aesthetic' => $products->Secondary_Aesthetic,
                    'Secondary_Fit' => $products->Secondary_Fit,
                    'Preorder' => $products->Preorder,
                    'slug' => $products->slug,
                    'featured_image' => $products->featured_image,
                    'featured_image_key' => $featured_image_key,
                    'allimage' => $allimage,
                    'allvariations' => $allvariations,
                    'option_type' => $options,
                    'option_value' => $values,
                    'swatches' => $swatches,
                    'allattributes' => $allattributes,
                    'dimension_unit' => $products->dimension_unit,
                    'is_bestseller' => $products->is_bestseller,
                    'shipping_height' => $products->shipping_height,
                    'stock' => $products->stock,
                    'shipping_length' => $products->shipping_length,
                    'tariff_code' => $products->tariff_code,
                    'shipping_weight' => $products->shipping_weight,
                    'shipping_width' => $products->shipping_width,
                    'weight_unit' => $products->weight_unit,
                    'reatailers_inst' => $products->reatailers_inst,
                    'reatailer_input_limit' => $products->reatailer_input_limit,
                    'retailer_min_qty' => $products->retailer_min_qty,
                    'retailer_add_charge' => $products->retailer_add_charge,
                    'product_shipdate' => $products->product_shipdate,
                    'product_endshipdate' => $products->product_endshipdate,
                    'product_deadline' => $products->product_deadline,
                    'keep_product' => $products->keep_product,
                    'out_of_stock' => $products->out_of_stock,
                    'videos' => $product_videos,
                    'default_currency' => $products->default_currency,
                    'outside_us' => $products->outside_us,
                    'sell_type' => $products->sell_type,
                    'prepack_type' => $products->prepack_type,
                    'pre_packs' => $pre_packs,
                    'prev_product_id' => $prev_product_id,
                    'next_product_id' => $next_product_id,
                );
            }
            $allfetchproduct = $redis->set("productdetails:" . $request->id, json_encode($result_array));
            $response = ['res' => true, 'msg' => "", 'data' => $result_array];
        }

        return response()->json($response);
    }

    public function updateproduct(Request $request)
    {
        $vndr_upload_path = '/uploads/products/';
        $product_id = $request->input('id');
        $product = Products::where('id', $request->input('id'))->first();

        $usd_wholesale_price = $request->input('usd_wholesale_price') && !in_array($request->input('usd_wholesale_price'), array('undefined', 'null')) ? $request->input('usd_wholesale_price') : 0;
        $usd_retail_price = $request->input('usd_retail_price') && !in_array($request->input('usd_wholesale_price'), array('undefined', 'null')) ? $request->input('usd_retail_price') : 0;
        $cad_wholesale_price = $request->input('cad_wholesale_price') && !in_array($request->input('cad_wholesale_price'), array('undefined', 'null')) ? $request->input('cad_wholesale_price') : 0;
        $cad_retail_price = $request->input('cad_retail_price') && !in_array($request->input('cad_retail_price'), array('undefined', 'null')) ? $request->input('cad_retail_price') : 0;
        $gbp_wholesale_price = $request->input('gbp_wholesale_price') && !in_array($request->input('gbp_wholesale_price'), array('undefined', 'null')) ? $request->input('gbp_wholesale_price') : 0;
        $gbp_retail_price = $request->input('gbp_retail_price') && !in_array($request->input('gbp_retail_price'), array('undefined', 'null')) ? $request->input('gbp_retail_price') : 0;
        $eur_wholesale_price = $request->input('eur_wholesale_price') && !in_array($request->input('eur_wholesale_price'), array('undefined', 'null')) ? $request->input('eur_wholesale_price') : 0;
        $eur_retail_price = $request->input('eur_retail_price') && !in_array($request->input('eur_retail_price'), array('undefined', 'null')) ? $request->input('eur_retail_price') : 0;
        $aud_wholesale_price = $request->input('aud_wholesale_price') && !in_array($request->input('aud_wholesale_price'), array('undefined', 'null')) ? $request->input('aud_wholesale_price') : 0;
        $aud_retail_price = $request->input('aud_retail_price') && !in_array($request->input('aud_retail_price'), array('undefined', 'null')) ? $request->input('aud_retail_price') : 0;
        $tester_price = $request->input('testers_price') && !in_array($request->input('testers_price'), array('undefined', 'null')) ? $request->input('testers_price') : 0;
        $shipping_tariff_code = $request->input('shipping_tariff_code') && !in_array($request->input('shipping_tariff_code'), array('undefined', 'null')) ? $request->input('shipping_tariff_code') : '';
        $case_quantity = $request->input('order_case_qty') ? $request->input('order_case_qty') : 0;
        $min_case_quantity = $request->input('order_min_case_qty') ? $request->input('order_min_case_qty') : 0;
        $sell_type = $request->input('sell_type');
        $prepack_type = $sell_type == 3 ? $request->input('prepack_type') : 1;
        if ($request->input('shipping_inventory') == 'undefined') {
            $stock = 0;
        } else {
            $stock = $request->input('shipping_inventory');
        }
        $main_category = '';
        $category = '';
        $outside_us = $request->input('outside_us') == 'true' ? 1 : 0;
        $sub_category = $request->input('product_type');
        $sub_category_details = Category::where('id', $sub_category)->first();

        if ($sub_category_details) {
            $category_details = Category::where('id', $sub_category_details->parent_id)->first();
            $category = $category_details->id;
            $main_category = $category_details->parent_id;
        }
        $user_id = $request->input('user_id');


        $data = array(
            'name' => addslashes($request->input('product_name')),
            'main_category' => $main_category,
            'category' => $category,
            'sub_category' => $sub_category,
            //'status' => $v->status,
            'description' => addslashes(strip_tags($request->input('description'))),
            'country' => $request->input('product_made'),
            'case_quantity' => $case_quantity,
            'min_order_qty' => $min_case_quantity,
            'min_order_qty_type' => $request->input('min_order_qty_type'),
            'sku' => $request->input('shipping_sku'),
            'usd_wholesale_price' => $usd_wholesale_price,
            'usd_retail_price' => $usd_retail_price,
            'cad_wholesale_price' => $cad_wholesale_price,
            'cad_retail_price' => $cad_retail_price,
            'gbr_wholesale_price' => $aud_wholesale_price,
            'gbr_retail_price' => $aud_retail_price,
            'eur_wholesale_price' => $eur_wholesale_price,
            'eur_retail_price' => $eur_retail_price,
            'gbp_wholesale_price' => $gbp_wholesale_price,
            'gbp_retail_price' => $gbp_retail_price,
            'usd_tester_price' => $request->input('usd_tester_price'),
            'dimension_unit' => $request->input('dimension_unit'),
            'is_bestseller' => $request->input('is_bestseller'),
            'shipping_height' => $request->input('shipping_height'),
            'stock' => $stock,
            'shipping_length' => $request->input('shipping_length'),
            'shipping_weight' => $request->input('shipping_weight'),
            'shipping_width' => $request->input('shipping_width'),
            'weight_unit' => $request->input('weight_unit'),
            'reatailers_inst' => addslashes($request->input('reatailers_inst')),
            'reatailer_input_limit' => $request->input('reatailer_input_limit'),
            'retailer_min_qty' => $request->input('retailer_min_qty'),
            'retailer_add_charge' => $request->input('retailer_add_charge'),
            'product_shipdate' => $request->input('product_shipdate'),
            'product_endshipdate' => $request->input('product_endshipdate'),
            'product_deadline' => $request->input('product_deadline'),
            'keep_product' => $request->input('keep_product'),
            'featured_image' => '',
            'out_of_stock' => $request->input('out_of_stock'),
            'updated_date' => date('Y-m-d'),
            'sell_type' => $sell_type,
            'prepack_type' => $prepack_type,
            'tariff_code' => $shipping_tariff_code,
            'outside_us' => $outside_us,
            'updated_at' => date('Y-m-d H:i:s'),
        );

        Products::where('id', $request->input('id'))->update($data);

        if (isset($_FILES['video_url']['name']) && is_countable($_FILES['video_url']['name']) && count($_FILES['video_url']['name']) > 0) {
            for ($i = 0; $i < count($_FILES['video_url']['name']); $i++) {
                $target_path = public_path() . $vndr_upload_path . "/";
                $ext = pathinfo($_FILES['video_url']['name'][$i], PATHINFO_EXTENSION);
                $img = rand() . time() . '.' . $ext;
                $target_path = $target_path . $img;
                move_uploaded_file($_FILES['video_url']['tmp_name'][$i], $target_path);
                if ($ext != "") {
                    $imgurl = url('/') . '/public/uploads/products/' . $img;
                } else {
                    $imgurl = '';
                }
                $VideoAdd = new Video();
                $VideoAdd->product_id = $product_id;
                $VideoAdd->video_url = $imgurl;
                $VideoAdd->save();
            }
        }

        $variation_images = [];
        $prev_product_images = ProductImage::where('product_id', $product_id)->get();
        if ($prev_product_images) {
            foreach ($prev_product_images as $previmg) {
                $variation_images[] = $previmg->images;
            }
        }

        $featured_key = isset($request->featured_image) && $request->featured_image != "" ? (int)$request->featured_image : 0;
        $target_path = public_path() . $vndr_upload_path . "/";
        $folderPath = public_path() . $vndr_upload_path . "/";

        if (isset($_FILES['product_images']['name'])) {
            $excel_file_names = $_FILES['product_images']['name'];
            $folderPath = public_path() . $vndr_upload_path . "/";
            for ($i = 0; $i < count($excel_file_names); $i++) {
                $file_name = $excel_file_names[$i];
                $tmp_arr = explode(".", $file_name);
                $extension = end($tmp_arr);
                $file_url = Str::random(10) . '.' . $extension;
                if (move_uploaded_file($_FILES["product_images"]["tmp_name"][$i], $folderPath . $file_url)) {
                    if ($extension != "") {
                        $productimgurl = url('/') . '/public/uploads/products/' . $file_url;
                    } else {
                        $productimgurl = '';
                    }
                    $Imagedd = new ProductImage();
                    $Imagedd->product_id = $product_id;
                    $Imagedd->images = $productimgurl;
                    $Imagedd->image_sort = $i;
                    $Imagedd->feature_key = 0;
                    $Imagedd->save();
                    $variation_images[] = $productimgurl;

                    $variation_images[] = $productimgurl;
                }
            }
        }

        ProductImage::where('image_sort', $featured_key)->where('product_id', $product_id)
            ->update([
                'feature_key' => 1
            ]);
        $product_image = ProductImage::where('product_id', $product_id)->where('image_sort', $featured_key)->first();
        $featured_image = $product_image->images;
        Products::where('id', $product_id)
            ->update([
                'featured_image' => $featured_image
            ]);
        $product_fet_img = ProductImage::where('product_id', $product_id)->where('feature_key', '1')->first();
        Products::where('id', $product_id)->update(array('featured_image' => $product_fet_img->images));


        $option_types = explode(',', $request->input('option_type'));
        $color_key = in_array('Color', $option_types) ? array_search("Color", $option_types) + 1 : 0;
        $colors = array();
        $swatches = array();
        $color_options = json_decode($request->input('colorOptionItems'), true);
        if (is_countable($color_options) && count($color_options) > 0) {
            foreach ($color_options as $color) {
                if (!in_array($color['name'], $colors)) {
                    $colors[] = $color['name'];
                    $swatch_image = $color["img"];
                    if (isset($swatch_image) && $swatch_image != "" && filter_var($swatch_image, FILTER_VALIDATE_URL) === false) {
                        $image_64 = $swatch_image; //your base64 encoded data
                        $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                        $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                        $image = str_replace($replace, '', $image_64);
                        $image = str_replace(' ', '+', $image);
                        $image_name = Str::random(10) . '.' . 'png';
                        File::put(public_path() . $vndr_upload_path . "/" . $image_name, base64_decode($image));
                        $swatch_image = url('/') . '/public/uploads/products/' . $image_name;
                        $swatches[] = $swatch_image;
                    } else {
                        $swatches[] = $swatch_image;
                    }
                }
            }
        }

        $variations = json_decode($request->input('variations'), true);

        if ($variations) {
            ProductVariation::where('product_id', $product_id)->delete();

            foreach ($variations as $vars) {
                $simage = '';
                if (count($color_options) > 0 && $color_key > 0) {
                    $color_val = $vars['value' . $color_key];
                    $color_k = in_array($color_val, $colors) ? array_search($color_val, $colors) : 0;
                    $simage = $swatches[$color_k];
                }

                if (isset($vars["image_index"])) {
                    $image_index = (int)$vars["image_index"];
                    $vimage = isset($variation_images[$image_index]) ? $variation_images[$image_index] : '';
                } else {
                    $vimage = $vars['preview_images'];
                }
                $wholesale_price = $vars['usd_wholesale_price'] && !in_array($vars['usd_wholesale_price'], array('undefined', 'null')) ? $vars['usd_wholesale_price'] : 0;
                $retail_price = $vars['usd_retail_price'] && !in_array($vars['usd_retail_price'], array('undefined', 'null')) ? $vars['usd_retail_price'] : 0;
                $cad_wholesale_price = $vars['cad_wholesale_price'] && !in_array($vars['cad_wholesale_price'], array('undefined', 'null')) ? $vars['cad_wholesale_price'] : 0;
                $cad_retail_price = $vars['cad_retail_price'] && !in_array($vars['cad_retail_price'], array('undefined', 'null')) ? $vars['cad_retail_price'] : 0;
                $gbp_wholesale_price = $vars['gbp_wholesale_price'] && !in_array($vars['gbp_wholesale_price'], array('undefined', 'null')) ? $vars['gbp_wholesale_price'] : 0;
                $gbp_retail_price = $vars['gbp_retail_price'] && !in_array($vars['gbp_retail_price'], array('undefined', 'null')) ? $vars['gbp_retail_price'] : 0;
                $eur_wholesale_price = $vars['eur_wholesale_price'] && !in_array($vars['eur_wholesale_price'], array('undefined', 'null')) ? $vars['eur_wholesale_price'] : 0;
                $eur_retail_price = $vars['eur_retail_price'] && !in_array($vars['eur_retail_price'], array('undefined', 'null')) ? $vars['eur_retail_price'] : 0;
                $aud_wholesale_price = $vars['aud_wholesale_price'] && !in_array($vars['aud_wholesale_price'], array('undefined', 'null')) ? $vars['aud_wholesale_price'] : 0;
                $aud_retail_price = $vars['aud_retail_price'] && !in_array($vars['aud_retail_price'], array('undefined', 'null')) ? $vars['aud_retail_price'] : 0;
                if ($vars['status'] == 'published') {
                    $variant_key = 'v_' . Str::lower(Str::random(10));
                    $productvariation = new ProductVariation();
                    $productvariation->variant_key = $variant_key;
                    $productvariation->swatch_image = $simage;
                    $productvariation->image = $vimage;
                    $productvariation->product_id = $product_id;
                    $productvariation->price = $wholesale_price;
                    $productvariation->options1 = $vars['option1'];
                    $productvariation->options2 = $vars['option2'];
                    $productvariation->options3 = $vars['option3'];
                    $productvariation->sku = $vars['sku'];
                    $productvariation->value1 = $vars['value1'];
                    $productvariation->value2 = $vars['value2'];
                    $productvariation->value3 = $vars['value3'];
                    $productvariation->retail_price = $retail_price;
                    $productvariation->cad_wholesale_price = $cad_wholesale_price;
                    $productvariation->cad_retail_price = $cad_retail_price;
                    $productvariation->gbp_wholesale_price = $gbp_wholesale_price;
                    $productvariation->gbp_retail_price = $gbp_retail_price;
                    $productvariation->eur_wholesale_price = $eur_wholesale_price;
                    $productvariation->eur_retail_price = $eur_retail_price;
                    $productvariation->aud_wholesale_price = $aud_wholesale_price;
                    $productvariation->aud_retail_price = $aud_retail_price;
                    $productvariation->stock = $vars['inventory'];
                    $productvariation->weight = $vars['weight'];
                    $productvariation->length = $vars['length'];
                    $productvariation->length_unit = $vars['length_unit'];
                    $productvariation->width_unit = $vars['width_unit'];
                    $productvariation->height_unit = $vars['height_unit'];
                    $productvariation->width = $vars['width'];
                    $productvariation->height = $vars['height'];
                    $productvariation->dimension_unit = $vars['dimension_unit'];
                    $productvariation->weight_unit = $vars['weight_unit'];
                    $productvariation->tariff_code = $vars['tariff_code'];
                    $productvariation->website = $vars['website'];
                    $productvariation->website_product_id = $vars['website_product_id'];
                    $productvariation->variation_id = $vars['variation_id'];
                    $productvariation->inventory_item_id = $vars['inventory_item_id'];

                    $productvariation->save();
                }
            }
        }
        if (is_countable($variations) && count($variations) == 1) {
            Products::where('id', $product_id)->update(array("stock" => $variations[0]['inventory']));
        }
        if (is_countable($variations) && count($variations) == 0) {

            ProductVariation::where('product_id', $product_id)->update(array('status' => 2));
        }

        //pre packs
        if ($request->input('sell_type') == 3) {
            $pre_packs = json_decode($request->input('pre_packs'), true);
            if ($pre_packs) {
                Products::where('id', $product_id)->update(array('sell_type' => '3'));
                foreach ($pre_packs as $pre_pack) {
                    $active = $pre_pack['active'];
                    if (isset($pre_pack['id']) && $pre_pack['id'] != '') {
                        if (isset($pre_pack['status']) && $pre_pack['status'] == 'deleted') {
                            ProductPrepack::where('id', $pre_pack['id'])->delete();
                        } else {
                            $packs_price = $pre_pack['packs_price'] && !in_array($pre_pack['packs_price'], array('', 'undefined', 'null')) ? $pre_pack['packs_price'] : 0;
                            ProductPrepack::where('id', $pre_pack['id'])
                                ->update([
                                    'style' => $pre_pack['style'],
                                    'pack_name' => $pre_pack['pack_name'],
                                    'size_ratio' => $pre_pack['size_ratio'],
                                    'size_range' => $pre_pack['size_range_value'],
                                    'packs_price' => $packs_price,
                                    'active' => $active

                                ]);
                        }
                    } else {
                        $packs_price = $pre_pack['packs_price'] && !in_array($pre_pack['packs_price'], array('', 'undefined', 'null')) ? $pre_pack['packs_price'] : 0;
                        $ProductPrepack = new ProductPrepack();
                        $ProductPrepack->product_id = $product_id;
                        $ProductPrepack->style = $pre_pack['style'];
                        $ProductPrepack->pack_name = $pre_pack['pack_name'];
                        $ProductPrepack->size_ratio = $pre_pack['size_ratio'];
                        $ProductPrepack->size_range = $pre_pack['size_range_value'];
                        $ProductPrepack->packs_price = $packs_price;
                        $ProductPrepack->active = $active;
                        $ProductPrepack->save();
                    }
                }
            }
        }
        Redis::connection();
        Redis::flushDB();

        $response = ['res' => true, 'msg' => "Updated Successfully", 'data' => ""];

        return response()->json($response);
    }


    public function Statusproduct(Request $req)
    {
        $ids = explode(",", $req->id);
        if ($req->status == 'publish') {
            $error_msg = 0;
            $product_details = Products::where('id', $req->id)->first();
            $res_images = ProductImage::where('product_id', $product_details->id)->get();
            $usd_wholesale_price = (float)$product_details->usd_wholesale_price;
            $usd_retail_price = (float)$product_details->usd_retail_price;
            $product_variations = ProductVariation::where('product_id', $req->id)->where('status', '1')->get();
            $product_variations_count = $product_variations->count();
            if ($product_variations_count > 0) {
                $product_variations->toArray();
                $usd_wholesale_price = (float)$product_variations[0]->price;
                $usd_retail_price = (float)$product_variations[0]->retail_price;
            }

            if ($product_details->name == '') {
                $error_msg++;
            } elseif ($product_details->main_category == 0 || $product_details->category == 0 || $product_details->sub_category == 0) {
                $error_msg++;
            } elseif ($product_details->country == 0 || in_array($product_details->country, array('undefined', 'null', ''))) {
                $error_msg++;
            } elseif (count($res_images) == 0) {
                $error_msg++;
            } elseif ($usd_wholesale_price == 0) {
                $error_msg++;
            } elseif ($usd_retail_price == 0) {
                $error_msg++;
            } elseif ($product_details->sell_type == '1' && (int)$product_details->case_quantity == 0) {
                $error_msg++;
            } elseif ((int)$product_details->min_order_qty == 0) {
                $error_msg++;
            }

            if ($error_msg == 0) {
                Products::whereIn("id", $ids)
                    ->update([
                        'status' => $req->status
                    ]);
                $response = ['res' => true, 'msg' => "Updated Successfully", 'data' => ""];
            } else {
                $response = ['res' => false, 'msg' => "Please fill all required fields", 'data' => ""];
            }
        } else {
            Products::whereIn("id", $ids)
                ->update([
                    'status' => $req->status
                ]);
            $response = ['res' => true, 'msg' => "Updated Successfully", 'data' => ""];
        }

        Redis::connection();
        Redis::flushDB();
        return response()->json($response);
    }

    public function deleteproduct(Request $req)
    {
        $ids = explode(",", $req->id);
        Products::whereIn('id', $ids)->delete();
        ProductImage::where('product_id', $req->id)->delete();
        ProductVariation::where('product_id', $req->id)->delete();
        Video::where('product_id', $req->id)->delete();
        Redis::connection();
        Redis::flushDB();
        $response = ['res' => true, 'msg' => "Deleted Successfully", 'data' => ""];

        return response()->json($response);
    }

    public function deleteproductimage(Request $req)
    {
        ProductImage::where('id', $req->image_id)->delete();
        $response = ['res' => true, 'msg' => "Deleted Successfully", 'data' => ""];
        return response()->json($response);
    }

    public function deleteproductvideo(Request $req)
    {
        Video::where('id', $req->id)->delete();
        $response = ['res' => true, 'msg' => "Deleted Successfully", 'data' => ""];

        return response()->json($response);
    }

    public function allcategory(Request $request)
    {


        $redis = Redis::connection();
        $existredis = Redis::exists("allcategory");
        if ($existredis > 0) {
            $cachedcategory = Redis::get("allcategory");
            $category = json_decode($cachedcategory, false);
            $response = ['res' => true, 'msg' => "", 'data' => $category];
        } else {
            $categories = DB::table('category AS r')
                ->leftJoin('category AS e', 'e.id', '=', 'r.parent_id')
                ->leftJoin('category AS l', 'r.id', '=', 'l.parent_id')
                ->select('e.name AS parent_name',
                    'e.id AS parent_id',
                    'r.id AS child_id',
                    'r.name AS child_name',
                    'l.id AS last_id',
                    'l.name AS last_name')
                ->where('l.parent_id', '>', 0)
                ->where('e.status', '=', 0)
                ->where('r.status', '=', 0)
                ->where('l.status', '=', 0)
                ->get();
            $allcategory = array();
            if (count($categories) > 0) {
                foreach ($categories as $var) {
                    $allcategory[] = array(
                        'category' => $var->parent_name . '->' . $var->child_name . '->' . $var->last_name,
                        'last_id' => $var->last_id
                    );
                }
            }
            $category = $redis->set('allcategory', json_encode($allcategory));
            $response = ['res' => true, 'msg' => "", 'data' => $allcategory];
        }


        return response()->json($response);
    }

    public function states(Request $request)
    {
        $result_array = array();

        $states = DB::table('states')
            ->where('country_id', $request->country_id)
            ->orderBy('name', 'ASC')
            ->get();

        foreach ($states as $v) {
            $result_array[] = array(
                'id' => $v->id,
                'state_name' => $v->name
            );
        }

        $response = ['res' => true, 'msg' => "", 'data' => $result_array];
        return response()->json($response);
    }

    public function cities(Request $request)
    {
        $result_array = array();

        $states = DB::table('cities')
            ->where('state_id', $request->state_id)
            ->orderBy('name', 'ASC')
            ->get();

        foreach ($states as $v) {
            $result_array[] = array(
                'id' => $v->id,
                'city_name' => $v->name
            );
        }

        $response = ['res' => true, 'msg' => "", 'data' => $result_array];
        return response()->json($response);
    }

    public function convertprice(Request $request, $price)
    {
        //return response()->json('What are looking for ?');
        $req_url = 'https://api.exchangerate.host/latest?base=USD&symbols=USD,CAD,GBP,AUD,EUR&places=2&amount=' . $price;
        $response_json = file_get_contents($req_url);
        if (false !== $response_json) {
            try {
                $response_obj = json_decode($response_json);
                if ($response_obj->success === true) {
                    $response = ['res' => true, 'msg' => "", 'data' => $response_obj->rates];
                }
            } catch (Exception $e) {
                $response = ['res' => false, 'msg' => "Something went wrong", 'data' => ""];
            }
        }
        return response()->json($response);
    }


}
