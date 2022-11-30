<?php

namespace Modules\Product\Http\Controllers;

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
use Modules\Product\Entities\Brandstore;
use Modules\Product\Entities\ProductImage;
use Modules\Product\Entities\ProductPrepack;
use Modules\Product\Entities\Category;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    private $productAbsPath = "";
    private $productRelPath = "";

    public function __construct()
    {
        $this->productAbsPath = public_path('uploads/products');
        $this->productRelPath = asset('public') . '/uploads/products/';
        Redis::connection();
    }

    public function create(Request $request)
    {

        $usdWholesalePrice = $request->input('usd_wholesale_price') && !in_array($request->input('usd_wholesale_price'), array('undefined', 'null')) ? $request->input('usd_wholesale_price') : 0;
        $usdRetailPrice = $request->input('usd_retail_price') && !in_array($request->input('usd_wholesale_price'), array('undefined', 'null')) ? $request->input('usd_retail_price') : 0;
        $cadWholesalePrice = $request->input('cad_wholesale_price') && !in_array($request->input('cad_wholesale_price'), array('undefined', 'null')) ? $request->input('cad_wholesale_price') : 0;
        $cadRetailPrice = $request->input('cad_retail_price') && !in_array($request->input('cad_retail_price'), array('undefined', 'null')) ? $request->input('cad_retail_price') : 0;
        $gbpWholesalePrice = $request->input('gbp_wholesale_price') && !in_array($request->input('gbp_wholesale_price'), array('undefined', 'null')) ? $request->input('gbp_wholesale_price') : 0;
        $gbpRetailPrice = $request->input('gbp_retail_price') && !in_array($request->input('gbp_retail_price'), array('undefined', 'null')) ? $request->input('gbp_retail_price') : 0;
        $eurWholesalePrice = $request->input('eur_wholesale_price') && !in_array($request->input('eur_wholesale_price'), array('undefined', 'null')) ? $request->input('eur_wholesale_price') : 0;
        $eurRetailPrice = $request->input('eur_retail_price') && !in_array($request->input('eur_retail_price'), array('undefined', 'null')) ? $request->input('eur_retail_price') : 0;
        $audWholesalePrice = $request->input('aud_wholesale_price') && !in_array($request->input('aud_wholesale_price'), array('undefined', 'null')) ? $request->input('aud_wholesale_price') : 0;
        $audRetailPrice = $request->input('aud_retail_price') && !in_array($request->input('aud_retail_price'), array('undefined', 'null')) ? $request->input('aud_retail_price') : 0;
        $testerPrice = $request->input('testers_price') && !in_array($request->input('testers_price'), array('undefined', 'null')) ? $request->input('testers_price') : 0;
        $shippingTariffCode = $request->input('shipping_tariff_code') && !in_array($request->input('shipping_tariff_code'), array('undefined', 'null')) ? $request->input('shipping_tariff_code') : '';
        $caseQuantity = $request->input('order_case_qty') ? $request->input('order_case_qty') : 0;
        $minCaseQuantity = $request->input('order_min_case_qty') ? $request->input('order_min_case_qty') : 0;
        $sellType = $request->input('sell_type');
        $prepackType = $sellType == 3 ? $request->input('prepack_type') : 1;
        if ($request->input('shipping_inventory') == 'undefined') {
            $stock = 0;
        } else {
            $stock = $request->input('shipping_inventory');
        }
        $mainCategory = '';
        $category = '';
        $outsideUs = $request->input('outside_us') == 'true' ? 1 : 0;
        $subCategory = $request->input('product_type');
        $subCategoryDetails = Category::where('id', $subCategory)->first();

        if ($subCategoryDetails) {
            $categoryDetails = Category::where('id', $subCategoryDetails->parent_id)->first();
            $category = $categoryDetails->id;
            $mainCategory = $categoryDetails->parent_id;
        }
        $userId = $request->input('user_id');
        $productKey = 'p_' . Str::lower(Str::random(10));
        $productSlug = Str::slug($request->input('product_name'));

        $product = new Products();
        $product->product_key = $productKey;
        $product->slug = $productSlug;
        $product->name = addslashes($request->input('product_name'));
        $product->user_id = $request->input('user_id');
        $product->main_category = $mainCategory;
        $product->category = $category;
        $product->sub_category = $subCategory;
        $product->status = "publish";
        $product->description = addslashes($request->input('description'));
        $product->country = $request->input('product_made');
        $product->case_quantity = $caseQuantity;
        $product->min_order_qty = $minCaseQuantity;
        $product->sku = $request->input('shipping_sku');
        $product->usd_wholesale_price = $usdWholesalePrice;
        $product->usd_retail_price = $usdRetailPrice;
        $product->cad_wholesale_price = $cadWholesalePrice;
        $product->cad_retail_price = $cadRetailPrice;
        $product->gbp_wholesale_price = $gbpWholesalePrice;
        $product->gbp_retail_price = $gbpRetailPrice;
        $product->eur_wholesale_price = $eurWholesalePrice;
        $product->eur_retail_price = $eurRetailPrice;
        $product->gbr_wholesale_price = $audWholesalePrice;
        $product->gbr_retail_price = $audRetailPrice;
        $product->usd_tester_price = $testerPrice;
        $product->created_date = date('Y-m-d');
        $product->dimension_unit = $request->input('dimension_unit');
        $product->is_bestseller = $request->input('is_bestseller');
        $product->shipping_height = $request->input('shipping_height');
        $product->stock = $stock;
        $product->shipping_length = $request->input('shipping_length');
        $product->shipping_weight = $request->input('shipping_weight');
        $product->shipping_width = $request->input('shipping_width');
        $product->weight_unit = $request->input('weight_unit');
        $product->reatailers_inst = addslashes($request->input('reatailers_inst'));
        $product->reatailer_input_limit = $request->input('reatailer_input_limit');
        $product->retailer_min_qty = $request->input('retailer_min_qty');
        $product->retailer_add_charge = $request->input('retailer_add_charge');
        $product->product_shipdate = date('Y-m-d', strtotime($request->input('product_shipdate')));
        $product->product_endshipdate = date('Y-m-d', strtotime($request->input('product_endshipdate')));
        $product->product_deadline = date('Y-m-d', strtotime($request->input('product_deadline')));
        $product->out_of_stock = $request->input('out_of_stock');
        $product->keep_product = $request->input('keep_product');
        $product->sell_type = $sellType;
        $product->prepack_type = $prepackType;
        $product->outside_us = $outsideUs;
        $product->tariff_code = $shippingTariffCode;
        $product->created_at = date('Y-m-d H:i:s');
        $product->updated_at = date('Y-m-d H:i:s');
        $product->save();

        $lastInsertId = DB::getPdo()->lastInsertId();

        if ($request->file('video_url')) {
            foreach ($request->file('video_url') as $key => $file) {
                $fileName = rand() . time() . '.' . $file->extension();
                $file->move($this->productAbsPath, $fileName);
                $video = new Video();
                $video->product_id = $lastInsertId;
                $video->video_url = $this->productRelPath . $fileName;
                $video->save();
            }
        }

        $variationImages = [];
        $featuredKey = isset($request->featured_image) && !empty($request->featured_image) ? (int)$request->featured_image : 0;
        if ($request->file('product_images')) {
            foreach ($request->file('product_images') as $key => $file) {
                $fileName = rand() . time() . '.' . $file->extension();
                $file->move($this->productAbsPath, $fileName);
                $productImage = new ProductImage();
                $productImage->product_id = $lastInsertId;
                $productImage->images = $this->productRelPath . $fileName;
                $productImage->image_sort = $key;
                $productImage->feature_key = 0;
                $productImage->save();
                $variationImages[] = $this->productRelPath . $fileName;
            }
        }
        ProductImage::where('image_sort', $featuredKey)->where('product_id', $lastInsertId)
            ->update([
                'feature_key' => 1
            ]);
        $productImage = ProductImage::where('product_id', $lastInsertId)->where('image_sort', $featuredKey)->first();
        $featuredImage = $productImage->images;
        Products::where('id', $lastInsertId)
            ->update([
                'featured_image' => $featuredImage
            ]);
        $optionTypes = explode(',', $request->input('option_type'));
        $colorKey = in_array('Color', $optionTypes) ? array_search("Color", $optionTypes) + 1 : 0;
        $colors = [];
        $swatches = [];
        $colorOptions = json_decode($request->input('colorOptionItems'), true);
        if (is_countable($colorOptions) && count($colorOptions) > 0) {
            foreach ($colorOptions as $color) {
                if (!in_array($color['name'], $colors)) {
                    $colors[] = $color['name'];
                    $swatchImage = $color["img"];
                    if (isset($swatchImage) && !empty($swatchImage)) {
                        $uploadedImage = $this->image64Upload($swatchImage);
                        $swatches[] = $uploadedImage;
                    }
                }
            }
        }

        $variations = json_decode($request->input('variations'), true);

        if (is_countable($variations) && count($variations) > 0) {
            foreach ($variations as $vars) {
                $simage = '';
                if ($colorKey > 0) {
                    $colorVal = $vars['value' . $colorKey];
                    $scolorKey = in_array($colorVal, $colors) ? array_search($colorVal, $colors) : 0;
                    $simage = $swatches[$scolorKey];
                }
                $imageIndex = (int)$vars["image_index"];
                $vimage = $variationImages[$imageIndex] ?? '';

                $cadWholesalePrice = $vars['cad_wholesale_price'] && !in_array($vars['cad_wholesale_price'], array('undefined', 'null')) ? $vars['cad_wholesale_price'] : 0;
                $cadRetailPrice = $vars['cad_retail_price'] && !in_array($vars['cad_retail_price'], array('undefined', 'null')) ? $vars['cad_retail_price'] : 0;
                $gbpWholesalePrice = $vars['gbp_wholesale_price'] && !in_array($vars['gbp_wholesale_price'], array('undefined', 'null')) ? $vars['gbp_wholesale_price'] : 0;
                $gbpRetailPrice = $vars['gbp_retail_price'] && !in_array($vars['gbp_retail_price'], array('undefined', 'null')) ? $vars['gbp_retail_price'] : 0;
                $eurWholesalePrice = $vars['eur_wholesale_price'] && !in_array($vars['eur_wholesale_price'], array('undefined', 'null')) ? $vars['eur_wholesale_price'] : 0;
                $eurRetailPrice = $vars['eur_retail_price'] && !in_array($vars['eur_retail_price'], array('undefined', 'null')) ? $vars['eur_retail_price'] : 0;
                $audWholesalePrice = $vars['aud_wholesale_price'] && !in_array($vars['aud_wholesale_price'], array('undefined', 'null')) ? $vars['aud_wholesale_price'] : 0;
                $audRetailPrice = $vars['aud_retail_price'] && !in_array($vars['aud_retail_price'], array('undefined', 'null')) ? $vars['aud_retail_price'] : 0;

                $variantKey = 'v_' . Str::lower(Str::random(10));

                $productVariation = new ProductVariation();
                $productVariation->variant_key = $variantKey;
                $productVariation->swatch_image = $simage;
                $productVariation->image = $vimage;
                $productVariation->product_id = $lastInsertId;
                $productVariation->price = $vars['wholesale_price'];
                $productVariation->options1 = $vars['option1'];
                $productVariation->options2 = $vars['option2'];
                $productVariation->options3 = $vars['option3'];
                $productVariation->sku = $vars['sku'];
                $productVariation->value1 = $vars['value1'];
                $productVariation->value2 = $vars['value2'];
                $productVariation->value3 = $vars['value3'];
                $productVariation->retail_price = $vars['retail_price'];
                $productVariation->cad_wholesale_price = $cadWholesalePrice;
                $productVariation->cad_retail_price = $cadRetailPrice;
                $productVariation->gbp_wholesale_price = $gbpWholesalePrice;
                $productVariation->gbp_retail_price = $gbpRetailPrice;
                $productVariation->eur_wholesale_price = $eurWholesalePrice;
                $productVariation->eur_retail_price = $eurRetailPrice;
                $productVariation->aud_wholesale_price = $audWholesalePrice;
                $productVariation->aud_retail_price = $audRetailPrice;
                $productVariation->stock = $vars['inventory'];
                $productVariation->weight = $vars['weight'];
                $productVariation->length = $vars['length'];
                $productVariation->length_unit = $vars['length_unit'];
                $productVariation->width_unit = $vars['width_unit'];
                $productVariation->height_unit = $vars['height_unit'];
                $productVariation->width = $vars['width'];
                $productVariation->height = $vars['height'];
                $productVariation->dimension_unit = $vars['dimension_unit'];
                $productVariation->weight_unit = $vars['weight_unit'];
                $productVariation->tariff_code = $vars['tariff_code'];
                $productVariation->save();
            }
        }

        if ($request->input('sell_type') == 3) {
            $prePacks = json_decode($request->input('pre_packs'), true);
            if ($prePacks) {
                Products::where('id', $lastInsertId)
                    ->update([
                        'sell_type' => 3
                    ]);
                foreach ($prePacks as $prePack) {
                    $active = $prePack['active'];
                    $packsPrice = $prePack['packs_price'] && !in_array($prePack['packs_price'], array('', 'undefined', 'null')) ? $prePack['packs_price'] : 0;
                    $ProductPrepack = new ProductPrepack();
                    $ProductPrepack->product_id = $lastInsertId;
                    $ProductPrepack->style = $prePack['style'];
                    $ProductPrepack->pack_name = $prePack['pack_name'];
                    $ProductPrepack->size_ratio = $prePack['size_ratio'];
                    $ProductPrepack->size_range = $prePack['size_range_value'];
                    $ProductPrepack->packs_price = $packsPrice;
                    $ProductPrepack->active = $active;
                    $ProductPrepack->save();
                }
            }
        }

        Redis::flushDB();

        $response = ['res' => true, 'msg' => "Added Successfully", 'data' => ""];

        return response()->json($response);
    }

    public function fetchProductBySort(Request $request)
    {
        $result_array = [];
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
                case 2:
                    $query->orderBy('name', 'DESC');
                    break;
                case 3:
                    $query->orderBy('updated_at', 'DESC');
                    $query->orderBy('id', 'DESC');
                    break;
                default:
                    $query->orderBy('name', 'ASC');
                    break;
            }
            if ($request->search_key && !in_array($request->search_key, array('undefined', 'null'))) {
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

            $allfetchproduct = Redis::set("brandproduct:fetchproductsort:" . $request->page . ":" . $search . ":" . $request->status . ":" . $request->sort_key . ":" . $request->user_id, json_encode($data));

            $response = ['res' => true, 'msg' => "", 'data' => $data];
        }


        return response()->json($response);
    }

    public function fetchProducts(Request $request)
    {
        $result_array = [];
        $search = $request->search_key && !in_array($request->search_key, array('undefined', 'null')) ? $request->search_key : '';
        $existredis = Redis::exists("brandproduct:FetchProducts:" . $request->page . ":" . $search . ":" . $request->user_id . ":" . $request->status);
        if ($existredis > 0) {
            $cachedproducts = Redis::get("brandproduct:FetchProducts:" . $request->page . ":" . $search . ":" . $request->user_id . ":" . $request->status);
            $allfetchproduct = json_decode($cachedproducts, false);
            $response = ['res' => true, 'msg' => "", 'data' => $allfetchproduct];
        } else {
            $product_variations_tbl = DB::raw("(SELECT product_id,id as variant_id,value1,value2,value3,sku as vsku,stock as vstock,image as vimage
		FROM product_variations WHERE status='1') as pv");// Raw query is needed as nested query using for this function with alias.
            $products_sql = DB::table('products as p')
                ->select('p.*', 'pv.*')
                ->leftjoin($product_variations_tbl, 'pv.product_id', '=', 'p.id')
                ->where('p.user_id', $request->user_id)
                ->orderBy('p.order_by', 'ASC');


            $all_products_count = $products_sql->count();

            switch ($request->status) {
                case 'instock':
                    $products_sql->where('p.stock', '>', 0)->orWhere('pv.vstock', '>', 0);
                    break;
                case 'outofstock':
                    $products_sql->where('p.stock', '<', 1)->where('pv.vstock', '<', 1);
                    break;
                default:
                    break;
            }

            if ($request->search_key && !in_array($request->search_key, array('undefined', 'null'))) {
                $products_sql->where('p.name', 'Like', '%' . $request->search_key . '%');
            }

            $isprdct_query = DB::table('products as p')
                ->select('p.*', 'pv.*')
                ->leftjoin($product_variations_tbl, 'pv.product_id', '=', 'p.id')
                ->where('p.user_id', $request->user_id)->where('p.stock', '>', 0)->orWhere('pv.vstock', '>', 0);
            $instock_products_count = $isprdct_query->count();


            $osprdct_query = DB::table('products as p')
                ->select('p.*', 'pv.*')
                ->leftjoin($product_variations_tbl, 'pv.product_id', '=', 'p.id')
                ->where('p.user_id', $request->user_id)->where('p.stock', '<', 1)->where('pv.vstock', '<', 1);
            $outstock_products_count = $osprdct_query->count();
            $products = $products_sql->paginate(10);

            foreach ($products as $v) {
                $image = !empty($v->vimage) ? $v->vimage : $v->featured_image;
                $sku = !empty($v->vsku) ? $v->vsku : $v->sku;
                $stock = !empty($v->vstock) ? $v->vstock : $v->stock;
                $variable_arr = [];
                if (!empty($v->value1)) {
                    $variable_arr[] = $v->value1;
                }
                if (!empty($v->value2)) {
                    $variable_arr[] = $v->value2;
                }
                if (!empty($v->value3)) {
                    $variable_arr[] = $v->value3;
                }
                $result_array[] = array(
                    'id' => $v->id,
                    'variant_id' => $v->variant_id,
                    'variant' => implode('/', $variable_arr),
                    'name' => $v->name,
                    'sku' => $sku,
                    'featured_image' => $image,
                    'stock' => $stock
                );
            }

            $data = array(
                "products" => $result_array,
                "instckprdcts_count" => $instock_products_count,
                "outstckprdcts_count" => $outstock_products_count,
                "allprdcts_count" => $all_products_count
            );

            $allfetchproduct = Redis::set("brandproduct:FetchProducts:" . $request->page . ":" . $search . ":" . $request->user_id . ":" . $request->status, json_encode($data));
            $response = ['res' => true, 'msg' => "", 'data' => $data];
        }
        return response()->json($response);
    }

    public function importWordpress(Request $request)
    {
        $userId = $request->user_id;
        $consumer_key = $request->consumer_key;
        $website = $request->website;
        $consumer_secret = $request->consumer_secret;
        $user_query_uri = $website;
        $user_query_uri = preg_replace("#^[^:/.]*[:/]+#i", "", $user_query_uri);
        $results_website = Brandstore::where('website', $user_query_uri)->get();
        if (count($results_website) > 0) {
            $response = ['res' => true, 'msg' => "Already Imported", 'data' => ""];
        } else {
            include(app_path() . '/Classes/class-wc-api-client.php');
            $wc_api = new \WC_API_Client($consumer_key, $consumer_secret, $website);
            $products_obj = $wc_api->get_products()->products;
            $products_arr = json_decode(json_encode($products_obj), true);
            if (!empty($products_arr)) {
                foreach ($products_arr as $product) {
                    if (!empty($product['description'])) {
                        $desc = $product['description'];
                    } else {
                        $desc = $product['short_description'];
                    }
                    if (empty($product['stock_quantity'])) {
                        $product['stock_quantity'] = 0;
                    }

                    $title = str_replace("'", "`", $product['title']);
                    $desc = str_replace("'", "`", $desc);
                    $productKey = 'p_' . Str::lower(Str::random(10));
                    $productSlug = Str::slug($title, '-');
                    $ProductAdd = new Products();
                    $ProductAdd->product_key = $productKey;
                    $ProductAdd->slug = $productSlug;
                    $ProductAdd->name = addslashes($title);
                    $ProductAdd->user_id = $request->input('user_id');
                    $ProductAdd->status = "unpublish";
                    $ProductAdd->description = addslashes($desc);
                    $ProductAdd->sku = $product['sku'];
                    $ProductAdd->stock = $product['stock_quantity'];
                    $ProductAdd->product_id = $product['id'];
                    $ProductAdd->website = $user_query_uri;
                    $ProductAdd->featured_image = $product['featured_src'];
                    $ProductAdd->import_type = 'wordpress';
                    $ProductAdd->default_currency = 'USD';
                    $ProductAdd->created_at = date('Y-m-d H:i:s');
                    $ProductAdd->updated_at = date('Y-m-d H:i:s');
                    $ProductAdd->save();
                    $last_product_id = DB::getPdo()->lastInsertId();
                    $images = $product['images'];
                    if (!empty($images)) {

                        foreach ($images as $img) {

                            if ($img['src'] == $product['featured_src']) {
                                $feature_key = 1;
                            } else {
                                $feature_key = 0;
                            }
                            $Imagedd = new ProductImage();
                            $Imagedd->product_id = $last_product_id;
                            $Imagedd->images = $img['src'];
                            $Imagedd->feature_key = $feature_key;
                            $Imagedd->save();
                        }
                    }


                    $variations = $product['variations'];
                    if (!empty($variations)) {
                        foreach ($variations as $vars) {


                            $variantKey = 'v_' . Str::lower(Str::random(10));
                            $productVariation = new ProductVariation();
                            $productVariation->variant_key = $variantKey;
                            $productVariation->image = $vars['image'][0]['src'];
                            $productVariation->product_id = $last_product_id;
                            $productVariation->price = 0;
                            $productVariation->options1 = $vars['attributes'][0]['name'] ?? '';
                            $productVariation->options2 = $vars['attributes'][1]['name'] ?? '';
                            $productVariation->options3 = $vars['attributes'][2]['name'] ?? '';
                            $productVariation->sku = $vars['sku'];
                            $productVariation->value1 = $vars['attributes'][0]['option'] ?? '';
                            $productVariation->value2 = $vars['attributes'][1]['option'] ?? '';
                            $productVariation->value3 = $vars['attributes'][2]['option'] ?? '';
                            $productVariation->website_product_id = $product['id'];
                            $productVariation->website = $user_query_uri;
                            $productVariation->stock = $vars['stock_quantity'];
                            $productVariation->variation_id = $vars['id'];
                            $productVariation->save();
                        }
                    }
                }
                $Brandstore = new Brandstore();
                $Brandstore->brand_id = $request->user_id;
                $Brandstore->website = $user_query_uri;
                $Brandstore->api_key = $consumer_key;
                $Brandstore->api_password = $consumer_secret;
                $Brandstore->types = 'wordpress';
                $Brandstore->save();
                $response = ['res' => true, 'msg' => "Successfully Imported", 'data' => ""];
                Redis::flushDB();
                $response = ['res' => true, 'msg' => "Imported Successfully", 'data' => ""];
            } else {
                $response = ['res' => false, 'msg' => "Enter valid information", 'data' => ""];
            }
        }

        return response()->json($response);
    }

    private function curlCall($url, $param, $method, $request)
    {
        $API_KEY = $request->api_key;
        $PASSWORD = $request->api_password;
        $STORE_URL = $request->store_url;
        $apiURL = 'https://' . $API_KEY . ':' . $PASSWORD . '@' . $STORE_URL . $url;
        $postInput = $param;
        $ch = curl_init($apiURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postInput);
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $result = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $statusCode = $http_status;
        $responseBody = json_decode($result, true);
        $resultObject = array("statusCode" => $statusCode, "responseBody" => $responseBody);
        return $resultObject;

    }

    public function importShopify(Request $request)
    {

        $user_query_uri = $request->store_url;
        $user_query_uri = preg_replace("#^[^:/.]*[:/]+#i", "", $user_query_uri);
        $apiURL = '/admin/shop.json';
        $result = $this->curlCall($apiURL, [], 'GET', $request);
        $statusCode = $result['statusCode'];
        $responseBody = $result['responseBody'];

        if ($statusCode <> 200) {
            $response = ['res' => false, 'msg' => "Please enter valid information.", 'data' => ""];
            return response()->json($response);
            exit;
        }
        $default_currency = $responseBody['shop']['currency'];
        $results_website = Brandstore::where('website', $user_query_uri)->get();
        if (count($results_website) == 0) {
            $apiURL = '/admin/products/count.json';
            $result = $this->curlCall($apiURL, [], 'GET', $request);
            $responseBody = $result['responseBody'];
            $totalcount = ceil($responseBody['count'] / 250);
            $newproduct = '';
            for ($ks = 1; $ks <= 10; $ks++) {
                $sinc_id = $newproduct;
                if ($ks == 1) {
                    $url = '/admin/products.json?limit=250';
                } else {
                    $url = '/admin/products.json?limit=250&since_id=' . $sinc_id;
                }
                $result_sync = $this->curlCall($url, [], 'GET', $request);
                $imagemain = '';
                $products_arr = $result_sync['responseBody']['products'];
                foreach ($products_arr as $product) {
                    if (empty($product['image'])) {
                        $imagemain = '';
                    } else {
                        $imagemain = $product['image']['src'];
                    }

                    if (empty($product['variants'][0]['inventory_quantity'])) {
                        $stock = 0;
                    } else {
                        $stock = $product['variants'][0]['inventory_quantity'];
                    }

                    $productKey = 'p_' . Str::lower(Str::random(10));
                    $productSlug = Str::slug($product['title']);
                    $ProductAdd = new Products();
                    $ProductAdd->product_key = $productKey;
                    $ProductAdd->slug = $productSlug;
                    $ProductAdd->name = addslashes($product['title']);
                    $ProductAdd->user_id = $request->input('user_id');
                    $ProductAdd->status = "unpublish";
                    $ProductAdd->description = addslashes($product['body_html']);
                    $ProductAdd->sku = $product['variants'][0]['sku'];
                    $ProductAdd->stock = $stock;
                    $ProductAdd->product_id = $product['id'];
                    $ProductAdd->website = $user_query_uri;
                    $ProductAdd->featured_image = $imagemain;
                    $ProductAdd->import_type = 'shopify';
                    $ProductAdd->default_currency = $default_currency;
                    $ProductAdd->created_at = date('Y-m-d H:i:s');
                    $ProductAdd->updated_at = date('Y-m-d H:i:s');
                    $ProductAdd->save();
                    $last_product_id = DB::getPdo()->lastInsertId();

                    $images = $product['images'];
                    if (!empty($images)) {
                        foreach ($images as $img) {
                            if ($img['src'] == $imagemain) {
                                $feature_key = 1;
                            } else {
                                $feature_key = 0;
                            }

                            $Imagedd = new ProductImage();
                            $Imagedd->product_id = $last_product_id;
                            $Imagedd->images = $img['src'];
                            $Imagedd->image_id = $img['id'];
                            $Imagedd->feature_key = $feature_key;
                            $Imagedd->save();

                        }
                    }

                    $variations = count($product['variants']);

                    if ($variations > 0) {
                        foreach ($product['variants'] as $vars) {
                            $options = count($product['options']);

                            $variantKey = 'v_' . Str::lower(Str::random(10));

                            if (empty($vars['inventory_quantity'])) {
                                $stock = 0;
                            } else {
                                $stock = $vars['inventory_quantity'];
                            }
                            if (!empty($product['options'][0]['name'])) {
                                $productVariation = new ProductVariation();
                                $productVariation->variant_key = $variantKey;
                                $productVariation->image = $imagemain;
                                $productVariation->product_id = $last_product_id;
                                $productVariation->price = 0;
                                $productVariation->options1 = $product['options'][0]['name'];
                                $productVariation->options2 = $product['options'][1]['name'] ?? '';
                                $productVariation->options3 = $product['options'][2]['name'] ?? '';
                                $productVariation->sku = $vars['sku'];
                                $productVariation->value1 = $vars['option1'];
                                $productVariation->value2 = $vars['option2'] ?? '';
                                $productVariation->value3 = $vars['option3'] ?? '';
                                $productVariation->image_id = $vars['image_id'];
                                $productVariation->website_product_id = $product['id'];
                                $productVariation->website = $user_query_uri;
                                $productVariation->stock = $stock;
                                $productVariation->variation_id = $vars['id'];
                                $productVariation->inventory_item_id = $vars['inventory_item_id'];
                                $productVariation->save();
                            }
                        }
                    }
                }

                $newproduct = $product['id'];

            }
            $Brandstore = new Brandstore();
            $Brandstore->brand_id = $request->user_id;
            $Brandstore->website = $user_query_uri;
            $Brandstore->api_key = $request->api_key;
            $Brandstore->api_password = $request->api_password;
            $Brandstore->types = 'shopify';
            $Brandstore->save();
            $response = ['res' => true, 'msg' => "Successfully Imported", 'data' => ""];
            Redis::flushDB();
        } else {
            $response = ['res' => true, 'msg' => "Already import", 'data' => ""];
        }

        return response()->json($response);
    }

    public function fetchProductByVendor(Request $request)
    {
        $result_array = [];
        $existredis = Redis::exists("fetchproductbyvendor:" . $request->user_id);
        if ($existredis > 0) {
            $cachedproducts = Redis::get("fetchproductbyvendor:" . $request->user_id);
            $allfetchproduct = json_decode($cachedproducts, false);
            $response = ['res' => true, 'msg' => "", 'data' => $allfetchproduct];
        }

        $products = Products::where('user_id', $request->user_id)
            ->orderBy('order_by', 'ASC')
            ->get();


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
                'import_type' => $v->import_type,
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
                'stock' => $v->stock,
                'default_currency' => $v->default_currency,
                'options_count' => $product_variations_count > 0 ? $product_variations_count : 1,
                'variations_count' => $product_variations_count,
                'price' => $price,
                'availability' => $availability,
                'website' => $v->website,
            );
            $allfetchproduct = Redis::set("fetchproductbyvendor:" . $request->user_id, json_encode($result_array));
            $response = ['res' => true, 'msg' => "", 'data' => $result_array];
        }

        return response()->json($response);
    }

    public function productDetails(Request $request)
    {
        $result_array = [];
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
            $productImages = ProductImage::where('product_id', $request->id)->get();
            $product_videos = Video::where('product_id', $request->id)->get()->toArray();
            $allimage = [];
            if (!empty($productImages)) {
                foreach ($productImages as $img) {
                    $allimage[] = array(
                        'image' => $img->images,
                        'feature_key' => $img->feature_key,
                        'image_id' => $img->id
                    );
                }
            }
            $product_variations = ProductVariation::where('product_id', $request->id)->where('status', '1')->get();
            $product_prepacks = ProductPrepack::where('product_id', $request->id)->get();
            $prePacks = [];
            $prepack_sizeranges = [];
            if (!empty($product_prepacks)) {
                foreach ($product_prepacks as $ppkey => $ppval) {
                    if (!in_array($ppval->size_range, $prepack_sizeranges)) {
                        $prepack_sizeranges[] = $ppval->size_range;
                    }
                    $prePacks[] = array(
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
            if (!empty($prePacks)) {
                foreach ($prePacks as $pkey => $pval) {
                    $prePacks[$pkey]['size_range'] = $prepack_sizeranges;
                }
            }

            $allvariations = [];
            $swatches = [];
            $swatch_imgs = [];
            $options = [];
            $values1 = [];
            $values2 = [];
            $values3 = [];

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


                    if (!in_array($var->swatch_image, $swatch_imgs) && !empty($var->swatch_image)) {
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

            $allattributes = [];
            if (count($qry) > 0) {
                foreach ($qry as $var) {
                    $allattributes[] = array(
                        'options' => $var->attributes,
                        'value' => $var->options
                    );
                }
            }

            $featuredImage = ProductImage::where('product_id', $request->id)->where('feature_key', '1')->get()->first();
            $featuredImage_key = ($featuredImage) ? $featuredImage->image_sort : 0;

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
                    'featured_image_key' => $featuredImage_key,
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
                    'pre_packs' => $prePacks,
                    'prev_product_id' => $prev_product_id,
                    'next_product_id' => $next_product_id,
                );
            }
            $allfetchproduct = Redis::set("productdetails:" . $request->id, json_encode($result_array));
            $response = ['res' => true, 'msg' => "", 'data' => $result_array];
        }

        return response()->json($response);
    }

    public function productsReorder(Request $request)
    {
        $items = $request->items;

        foreach ($items as $k => $v) {
            $product = Products::find($v);
            $product->order_by = $k;
            $product->save();
        }
        Redis::flushDB();
    }

    public function update(Request $request)
    {

        $product_id = $request->input('id');
        $product = Products::where('id', $request->input('id'))->first();

        $usdWholesalePrice = $request->input('usd_wholesale_price') && !in_array($request->input('usd_wholesale_price'), array('undefined', 'null')) ? $request->input('usd_wholesale_price') : 0;
        $usdRetailPrice = $request->input('usd_retail_price') && !in_array($request->input('usd_wholesale_price'), array('undefined', 'null')) ? $request->input('usd_retail_price') : 0;
        $cadWholesalePrice = $request->input('cad_wholesale_price') && !in_array($request->input('cad_wholesale_price'), array('undefined', 'null')) ? $request->input('cad_wholesale_price') : 0;
        $cadRetailPrice = $request->input('cad_retail_price') && !in_array($request->input('cad_retail_price'), array('undefined', 'null')) ? $request->input('cad_retail_price') : 0;
        $gbpWholesalePrice = $request->input('gbp_wholesale_price') && !in_array($request->input('gbp_wholesale_price'), array('undefined', 'null')) ? $request->input('gbp_wholesale_price') : 0;
        $gbpRetailPrice = $request->input('gbp_retail_price') && !in_array($request->input('gbp_retail_price'), array('undefined', 'null')) ? $request->input('gbp_retail_price') : 0;
        $eurWholesalePrice = $request->input('eur_wholesale_price') && !in_array($request->input('eur_wholesale_price'), array('undefined', 'null')) ? $request->input('eur_wholesale_price') : 0;
        $eurRetailPrice = $request->input('eur_retail_price') && !in_array($request->input('eur_retail_price'), array('undefined', 'null')) ? $request->input('eur_retail_price') : 0;
        $audWholesalePrice = $request->input('aud_wholesale_price') && !in_array($request->input('aud_wholesale_price'), array('undefined', 'null')) ? $request->input('aud_wholesale_price') : 0;
        $audRetailPrice = $request->input('aud_retail_price') && !in_array($request->input('aud_retail_price'), array('undefined', 'null')) ? $request->input('aud_retail_price') : 0;
        $shippingTariffCode = $request->input('shipping_tariff_code') && !in_array($request->input('shipping_tariff_code'), array('undefined', 'null')) ? $request->input('shipping_tariff_code') : '';
        $caseQuantity = $request->input('order_case_qty') ? $request->input('order_case_qty') : 0;
        $minCaseQuantity = $request->input('order_min_case_qty') ? $request->input('order_min_case_qty') : 0;
        $sellType = $request->input('sell_type');
        $prepackType = $sellType == 3 ? $request->input('prepack_type') : 1;
        if ($request->input('shipping_inventory') == 'undefined') {
            $stock = 0;
        } else {
            $stock = $request->input('shipping_inventory');
        }
        $mainCategory = '';
        $category = '';
        $outsideUs = $request->input('outside_us') == 'true' ? 1 : 0;
        $subCategory = $request->input('product_type');
        $subCategoryDetails = Category::where('id', $subCategory)->first();

        if ($subCategoryDetails) {
            $categoryDetails = Category::where('id', $subCategoryDetails->parent_id)->first();
            $category = $categoryDetails->id;
            $mainCategory = $categoryDetails->parent_id;
        }
        $userId = $request->input('user_id');


        $data = array(
            'name' => addslashes($request->input('product_name')),
            'main_category' => $mainCategory,
            'category' => $category,
            'sub_category' => $subCategory,
            'description' => addslashes(strip_tags($request->input('description'))),
            'country' => $request->input('product_made'),
            'case_quantity' => $caseQuantity,
            'min_order_qty' => $minCaseQuantity,
            'min_order_qty_type' => $request->input('min_order_qty_type'),
            'sku' => $request->input('shipping_sku'),
            'usd_wholesale_price' => $usdWholesalePrice,
            'usd_retail_price' => $usdRetailPrice,
            'cad_wholesale_price' => $cadWholesalePrice,
            'cad_retail_price' => $cadRetailPrice,
            'gbr_wholesale_price' => $audWholesalePrice,
            'gbr_retail_price' => $audRetailPrice,
            'eur_wholesale_price' => $eurWholesalePrice,
            'eur_retail_price' => $eurRetailPrice,
            'gbp_wholesale_price' => $gbpWholesalePrice,
            'gbp_retail_price' => $gbpRetailPrice,
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
            'sell_type' => $sellType,
            'prepack_type' => $prepackType,
            'tariff_code' => $shippingTariffCode,
            'outside_us' => $outsideUs,
            'updated_at' => date('Y-m-d H:i:s'),
        );

        Products::where('id', $request->input('id'))->update($data);

        if ($request->file('video_url')) {
            foreach ($request->file('video_url') as $key => $file) {
                $fileName = rand() . time() . '.' . $file->extension();
                $file->move($this->productAbsPath, $fileName);
                $video = new Video();
                $video->product_id = $product_id;
                $video->video_url = $this->productRelPath . $fileName;
                $video->save();
            }
        }

        $variationImages = [];
        $prev_product_images = ProductImage::where('product_id', $product_id)->get();
        if ($prev_product_images) {
            foreach ($prev_product_images as $previmg) {
                $variationImages[] = $previmg->images;
            }
        }

        $featuredKey = isset($request->featured_image) && !empty($request->featured_image) ? (int)$request->featured_image : 0;
        if ($request->file('product_images')) {
            foreach ($request->file('product_images') as $key => $file) {
                $fileName = rand() . time() . '.' . $file->extension();
                $file->move($this->productAbsPath, $fileName);
                $productImage = new ProductImage();
                $productImage->product_id = $product_id;
                $productImage->images = $this->productRelPath . $fileName;
                $productImage->image_sort = $key;
                $productImage->feature_key = 0;
                $productImage->save();
                $variationImages[] = $this->productRelPath . $fileName;
            }
        }

        ProductImage::where('image_sort', $featuredKey)->where('product_id', $product_id)
            ->update([
                'feature_key' => 1
            ]);
        $productImage = ProductImage::where('product_id', $product_id)->where('image_sort', $featuredKey)->first();
        $featuredImage = $productImage->images;
        Products::where('id', $product_id)
            ->update([
                'featured_image' => $featuredImage
            ]);
        $product_fet_img = ProductImage::where('product_id', $product_id)->where('feature_key', '1')->first();
        Products::where('id', $product_id)->update(array('featured_image' => $product_fet_img->images));


        $optionTypes = explode(',', $request->input('option_type'));
        $colorKey = in_array('Color', $optionTypes) ? (int)(array_search("Color", $optionTypes)) + 1 : 0;
        $colors = [];
        $swatches = [];
        $colorOptions = json_decode($request->input('colorOptionItems'), true);


        if (is_countable($colorOptions) && count($colorOptions) > 0) {
            foreach ($colorOptions as $color) {
                if (!in_array($color['name'], $colors)) {
                    $colors[] = $color['name'];
                    $swatchImage = $color["img"];

                    if (!empty($swatchImage) && filter_var($swatchImage, FILTER_VALIDATE_URL) === false) {
                        $uploadedImage = $this->image64Upload($swatchImage);
                        $swatches[] = $uploadedImage;
                    } else {
                        $swatches[] = $swatchImage;
                    }
                }
            }
        }

        $variations = json_decode($request->input('variations'), true);

        if ($variations) {
            ProductVariation::where('product_id', $product_id)->delete();

            foreach ($variations as $vars) {
                $simage = '';
                if (count($colorOptions) > 0 && $colorKey > 0) {
                    $colorVal = $vars['value' . $colorKey];
                    $scolorKey = in_array($colorVal, $colors) ? array_search($colorVal, $colors) : 0;
                    $simage = $swatches[$scolorKey];
                }


                if (isset($vars["image_index"])) {
                    $imageIndex = (int)$vars["image_index"];
                    $vimage = isset($variationImages[$imageIndex]) ? $variationImages[$imageIndex] : '';
                } else {
                    $vimage = $vars['preview_images'];
                }
                $wholesale_price = $vars['usd_wholesale_price'] && !in_array($vars['usd_wholesale_price'], array('undefined', 'null')) ? $vars['usd_wholesale_price'] : 0;
                $retail_price = $vars['usd_retail_price'] && !in_array($vars['usd_retail_price'], array('undefined', 'null')) ? $vars['usd_retail_price'] : 0;
                $cadWholesalePrice = $vars['cad_wholesale_price'] && !in_array($vars['cad_wholesale_price'], array('undefined', 'null')) ? $vars['cad_wholesale_price'] : 0;
                $cadRetailPrice = $vars['cad_retail_price'] && !in_array($vars['cad_retail_price'], array('undefined', 'null')) ? $vars['cad_retail_price'] : 0;
                $gbpWholesalePrice = $vars['gbp_wholesale_price'] && !in_array($vars['gbp_wholesale_price'], array('undefined', 'null')) ? $vars['gbp_wholesale_price'] : 0;
                $gbpRetailPrice = $vars['gbp_retail_price'] && !in_array($vars['gbp_retail_price'], array('undefined', 'null')) ? $vars['gbp_retail_price'] : 0;
                $eurWholesalePrice = $vars['eur_wholesale_price'] && !in_array($vars['eur_wholesale_price'], array('undefined', 'null')) ? $vars['eur_wholesale_price'] : 0;
                $eurRetailPrice = $vars['eur_retail_price'] && !in_array($vars['eur_retail_price'], array('undefined', 'null')) ? $vars['eur_retail_price'] : 0;
                $audWholesalePrice = $vars['aud_wholesale_price'] && !in_array($vars['aud_wholesale_price'], array('undefined', 'null')) ? $vars['aud_wholesale_price'] : 0;
                $audRetailPrice = $vars['aud_retail_price'] && !in_array($vars['aud_retail_price'], array('undefined', 'null')) ? $vars['aud_retail_price'] : 0;
                if ($vars['status'] == 'published') {
                    $variantKey = 'v_' . Str::lower(Str::random(10));
                    $productVariation = new ProductVariation();
                    $productVariation->variant_key = $variantKey;
                    $productVariation->swatch_image = $simage;
                    $productVariation->image = $vimage;
                    $productVariation->product_id = $product_id;
                    $productVariation->price = $wholesale_price;
                    $productVariation->options1 = $vars['option1'];
                    $productVariation->options2 = $vars['option2'];
                    $productVariation->options3 = $vars['option3'];
                    $productVariation->sku = $vars['sku'];
                    $productVariation->value1 = $vars['value1'];
                    $productVariation->value2 = $vars['value2'];
                    $productVariation->value3 = $vars['value3'];
                    $productVariation->retail_price = $retail_price;
                    $productVariation->cad_wholesale_price = $cadWholesalePrice;
                    $productVariation->cad_retail_price = $cadRetailPrice;
                    $productVariation->gbp_wholesale_price = $gbpWholesalePrice;
                    $productVariation->gbp_retail_price = $gbpRetailPrice;
                    $productVariation->eur_wholesale_price = $eurWholesalePrice;
                    $productVariation->eur_retail_price = $eurRetailPrice;
                    $productVariation->aud_wholesale_price = $audWholesalePrice;
                    $productVariation->aud_retail_price = $audRetailPrice;
                    $productVariation->stock = $vars['inventory'];
                    $productVariation->weight = $vars['weight'];
                    $productVariation->length = $vars['length'];
                    $productVariation->length_unit = $vars['length_unit'];
                    $productVariation->width_unit = $vars['width_unit'];
                    $productVariation->height_unit = $vars['height_unit'];
                    $productVariation->width = $vars['width'];
                    $productVariation->height = $vars['height'];
                    $productVariation->dimension_unit = $vars['dimension_unit'];
                    $productVariation->weight_unit = $vars['weight_unit'];
                    $productVariation->tariff_code = $vars['tariff_code'];
                    $productVariation->website = $vars['website'];
                    $productVariation->website_product_id = $vars['website_product_id'];
                    $productVariation->variation_id = $vars['variation_id'];
                    $productVariation->inventory_item_id = $vars['inventory_item_id'];

                    $productVariation->save();
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
            $prePacks = json_decode($request->input('pre_packs'), true);
            if ($prePacks) {
                Products::where('id', $product_id)->update(array('sell_type' => '3'));
                foreach ($prePacks as $prePack) {
                    $active = $prePack['active'];
                    if (isset($prePack['id']) && !empty($prePack['id'])) {
                        if (isset($prePack['status']) && $prePack['status'] == 'deleted') {
                            ProductPrepack::where('id', $prePack['id'])->delete();
                        } else {
                            $packsPrice = $prePack['packs_price'] && !in_array($prePack['packs_price'], array('', 'undefined', 'null')) ? $prePack['packs_price'] : 0;
                            ProductPrepack::where('id', $prePack['id'])
                                ->update([
                                    'style' => $prePack['style'],
                                    'pack_name' => $prePack['pack_name'],
                                    'size_ratio' => $prePack['size_ratio'],
                                    'size_range' => $prePack['size_range_value'],
                                    'packs_price' => $packsPrice,
                                    'active' => $active

                                ]);
                        }
                    } else {
                        $packsPrice = $prePack['packs_price'] && !in_array($prePack['packs_price'], array('', 'undefined', 'null')) ? $prePack['packs_price'] : 0;
                        $ProductPrepack = new ProductPrepack();
                        $ProductPrepack->product_id = $product_id;
                        $ProductPrepack->style = $prePack['style'];
                        $ProductPrepack->pack_name = $prePack['pack_name'];
                        $ProductPrepack->size_ratio = $prePack['size_ratio'];
                        $ProductPrepack->size_range = $prePack['size_range_value'];
                        $ProductPrepack->packs_price = $packsPrice;
                        $ProductPrepack->active = $active;
                        $ProductPrepack->save();
                    }
                }
            }
        }
        Redis::flushDB();

        $response = ['res' => true, 'msg' => "Updated Successfully", 'data' => ""];

        return response()->json($response);
    }


    public function changeStatus(Request $req)
    {
        $ids = explode(",", $req->id);
        if ($req->status == 'publish') {
            $error_msg = 0;
            $product_details = Products::where('id', $req->id)->first();
            $res_images = ProductImage::where('product_id', $product_details->id)->get();
            $usdWholesalePrice = (float)$product_details->usd_wholesale_price;
            $usdRetailPrice = (float)$product_details->usd_retail_price;
            $product_variations = ProductVariation::where('product_id', $req->id)->where('status', '1')->get();
            $product_variations_count = $product_variations->count();
            if ($product_variations_count > 0) {
                $product_variations->toArray();
                $usdWholesalePrice = (float)$product_variations[0]->price;
                $usdRetailPrice = (float)$product_variations[0]->retail_price;
            }

            if ($product_details->name == '') {
                $error_msg++;
            } elseif ($product_details->main_category == 0 || $product_details->category == 0 || $product_details->sub_category == 0) {
                $error_msg++;
            } elseif ($product_details->country == 0 || in_array($product_details->country, array('undefined', 'null', ''))) {
                $error_msg++;
            } elseif (count($res_images) == 0) {
                $error_msg++;
            } elseif ($usdWholesalePrice == 0) {
                $error_msg++;
            } elseif ($usdRetailPrice == 0) {
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
        Redis::flushDB();
        return response()->json($response);
    }

    public function delete(Request $req)
    {
        $ids = explode(",", $req->id);
        Products::whereIn('id', $ids)->delete();
        ProductImage::where('product_id', $req->id)->delete();
        ProductVariation::where('product_id', $req->id)->delete();
        Video::where('product_id', $req->id)->delete();
        Redis::flushDB();
        $response = ['res' => true, 'msg' => "Deleted Successfully", 'data' => ""];

        return response()->json($response);
    }

    public function deleteProductImage(Request $req)
    {
        ProductImage::where('id', $req->image_id)->delete();
        $response = ['res' => true, 'msg' => "Deleted Successfully", 'data' => ""];
        return response()->json($response);
    }

    public function deleteProductVideo(Request $req)
    {
        Video::where('id', $req->id)->delete();
        $response = ['res' => true, 'msg' => "Deleted Successfully", 'data' => ""];

        return response()->json($response);
    }

    public function updateProductsStock(Request $request)
    {
        if ($request->variant_id) {
            ProductVariation::where('id', $request->variant_id)->update(array('stock' => $request->stock));
        } else {
            Products::where('id', $request->input('id'))->update(array('stock' => $request->stock));
        }
        Redis::flushDB();
    }

    public function category(Request $request)
    {


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
            $allcategory = [];
            if (count($categories) > 0) {
                foreach ($categories as $var) {
                    $allcategory[] = array(
                        'category' => $var->parent_name . '->' . $var->child_name . '->' . $var->last_name,
                        'last_id' => $var->last_id
                    );
                }
            }
            $category = Redis::set('allcategory', json_encode($allcategory));
            $response = ['res' => true, 'msg' => "", 'data' => $allcategory];
        }


        return response()->json($response);
    }

    public function states(Request $request)
    {
        $result_array = [];

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
        $result_array = [];

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

    public function convertPrice(Request $request, $price)
    {
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

    public function syncList(Request $request)
    {
        $result_array = [];
        $syncs = Brandstore::where('brand_id', $request->user_id)->orderBy('id', 'DESC')->get();
        foreach ($syncs as $v) {
            $result_array[] = array(
                'id' => $v->id,
                'website' => $v->website,
                'types' => $v->types
            );
        }
        $response = ['res' => true, 'msg' => "", 'data' => $result_array];
        return response()->json($response);
    }


    private function image64Upload($image)
    {
        $image_64 = $image; //your base64 encoded data
        $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
        $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
        $image_64 = str_replace($replace, '', $image_64);
        $image_64 = str_replace(' ', '+', $image_64);
        $imageName = Str::random(10) . '.' . 'png';

        File::put($this->productAbsPath . "/" . $imageName, base64_decode($image_64));
        return $this->productRelPath . $imageName;
    }


}
