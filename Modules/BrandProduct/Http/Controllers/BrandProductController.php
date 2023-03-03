<?php

namespace Modules\BrandProduct\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Routing\Controller;
use Modules\Product\Entities\Products;
use Modules\Product\Entities\ProductVariation;
use Modules\Product\Entities\BrandStore;
use Modules\Product\Entities\ProductImage;
use Modules\Product\Entities\Video;
use Modules\Product\Entities\ProductPrepack;
use DB;


class BrandProductController extends Controller
{
    public function __construct()
    {
        //Redis::connection();
    }

    public function index(Request $request)
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

    public function ProductSort(Request $request)
    {

        $result_array = [];
        $search = $request->search_key && !in_array($request->search_key, array('undefined', 'null')) ? $request->search_key : '';

        $productFetch = Products::where('user_id', $request->user_id);
        $productsCount = $productFetch->count();
        $productPublish = Products::where('user_id', $request->user_id)->where('status', 'publish');
        $publishCount = $productPublish->count();
        $productUnpublish = Products::where('user_id', $request->user_id)->where('status', 'unpublish');
        $unpublishedCount = $productUnpublish->count();
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
            $productVariations = ProductVariation::where('product_id', $v->id)->where('status', '1')->get();
            $productVariationsCount = $productVariations->count();
            $availability = 'out of stock';
            if ($productVariationsCount > 0) {
                $variantMinPrice = ProductVariation::where('product_id', $v->id)->min('price');
                $price = $variantMinPrice . '+';
                $variantStock = ProductVariation::where('product_id', $v->id)->sum('price');
                $availability = $variantStock > 0 ? 'in stock' : 'out of stock';
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
                'options_count' => $productVariationsCount > 0 ? $productVariationsCount : 1,
                'price' => $price,
                'availability' => $availability,
                'website' => $v->website,
                'import_type' => $v->import_type,
            );
        }

        $data = array(
            "products" => $result_array,
            "pblshprdcts_count" => $publishCount,
            "unpblshprdcts_count" => $unpublishedCount,
            "allprdcts_count" => $productsCount
        );


        $response = ['res' => true, 'msg' => "", 'data' => $data];


        return response()->json($response);
    }

    public function productDetails(Request $request)
    {
        $result_array = [];

        $products = Products::where('id', $request->id)->first();
        $prevProduct = Products::where('user_id', $products->user_id)->where('id', '<', $request->id)->orderBy('id', 'DESC')->first();
        $prevProductId = $prevProduct ? $prevProduct->id : 0;
        $nextProduct = Products::where('user_id', $products->user_id)->where('id', '>', $request->id)->orderBy('id', 'ASC')->first();
        $nextProductId = $nextProduct ? $nextProduct->id : 0;
        $brandDetails = Products::where('user_id', $products->user_id)->first();
        $bazaarDirectLink = $brandDetails->bazaar_direct_link;
        $productImages = ProductImage::where('product_id', $request->id)->get();
        $productVideos = Video::where('product_id', $request->id)->get()->toArray();
        $allImage = [];
        if (!empty($productImages)) {
            foreach ($productImages as $img) {
                $allImage[] = array(
                    'image' => $img->images,
                    'feature_key' => $img->feature_key,
                    'image_id' => $img->id
                );
            }
        }
        $productVariations = ProductVariation::where('product_id', $request->id)->where('status', '1')->get();
        $productPrepacks = ProductPrepack::where('product_id', $request->id)->get();
        $prePacks = [];
        $prepackSizeRanges = [];
        if (!empty($productPrepacks)) {
            foreach ($productPrepacks as $ppkey => $ppval) {
                if (!in_array($ppval->size_range, $prepackSizeRanges)) {
                    $prepackSizeRanges[] = $ppval->size_range;
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
                $prePacks[$pkey]['size_range'] = $prepackSizeRanges;
            }
        }

        $allVariations = [];
        $swatches = [];
        $swatchImgs = [];
        $options = [];
        $values1 = [];
        $values2 = [];
        $values3 = [];

        if (!empty($productVariations)) {
            foreach ($productVariations as $key => $var) {
                $allVariations[$key] = array(
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
                $variationValues = [];
                $variationOptions = [];
                if ($var->options1 != null && $var->value1 != null) {
                    $option = ucfirst(strtolower($var->options1));
                    $allVariations[$key][$option] = $var->value1;
                    $variationOptions[] = $option;
                    $variationValues[] = $var->value1;
                    if (!in_array($option, $options)) {
                        $options[] = $option;
                    }
                    if (!in_array($var->value1, $values1)) {
                        $values1[] = $var->value1;
                    }
                }
                if ($var->options2 != null && $var->value2 != null) {
                    $option = ucfirst(strtolower($var->options2));
                    $allVariations[$key][$option] = $var->value2;
                    $variationOptions[] = $option;
                    $variationValues[] = $var->value2;
                    if (!in_array($option, $options)) {
                        $options[] = $option;
                    }
                    if (!in_array($var->value2, $values2)) {
                        $values2[] = $var->value2;
                    }
                }
                if ($var->options3 != null && $var->value3 != null) {
                    $option = ucfirst(strtolower($var->options3));
                    $allVariations[$key][$option] = $var->value3;
                    $variationOptions[] = $option;
                    $variationValues[] = $var->value3;
                    if (!in_array($option, $options)) {
                        $options[] = $option;
                    }
                    if (!in_array($var->value3, $values3)) {
                        $values3[] = $var->value3;
                    }
                }

                $allVariations[$key]['variation_options'] = $variationOptions;
                $allVariations[$key]['variation_values'] = $variationValues;


                if (!in_array($var->swatch_image, $swatchImgs) && !empty($var->swatch_image)) {
                    $swatchImg[] = $var->swatch_image;
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
                    $swatches[] = (object)["name" => $color, "img" => $swatchImg[$ck] ?? ''];
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


        $featuredImage = ProductImage::where('product_id', $request->id)->where('feature_key', '1')->get()->first();
        $featuredImageKey = ($featuredImage) ? $featuredImage->image_sort : 0;

        if ($products) {
            $result_array[] = array(
                'id' => $products->id,
                'bazaar_direct_link' => $bazaarDirectLink,
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
                'featured_image_key' => $featuredImageKey,
                'allimage' => $allImage,
                'allvariations' => $allVariations,
                'option_type' => $options,
                'option_value' => $values,
                'swatches' => $swatches,
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
                'videos' => $productVideos,
                'default_currency' => $products->default_currency,
                'outside_us' => $products->outside_us,
                'sell_type' => $products->sell_type,
                'prepack_type' => $products->prepack_type,
                'pre_packs' => $prePacks,
                'prev_product_id' => $prevProductId,
                'next_product_id' => $nextProductId,
            );
        }
        $fetchProduct = Redis::set("productdetails:" . $request->id, json_encode($result_array));
        $response = ['res' => true, 'msg' => "", 'data' => $result_array];


        return response()->json($response);
    }

    public function fetchProductByVendor(Request $request)
    {
        $result_array = [];


        $products = Products::where('user_id', $request->user_id)
            ->orderBy('order_by', 'ASC')
            ->get();


        foreach ($products as $v) {
            $productVariations = ProductVariation::where('product_id', $v->id)->where('status', '1')->get();
            $productVariationsCount = $productVariations->count();
            $availability = 'out of stock';
            if ($productVariationsCount > 0) {
                $variantMinPrice = ProductVariation::where('product_id', $v->id)->min('price');
                $price = $variantMinPrice . '+';
                $variantStock = ProductVariation::where('product_id', $v->id)->sum('price');
                $availability = $variantStock > 0 ? 'in stock' : 'out of stock';
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
                'options_count' => $productVariationsCount > 0 ? $productVariationsCount : 1,
                'variations_count' => $productVariationsCount,
                'price' => $price,
                'availability' => $availability,
                'website' => $v->website,
            );
            $fetchProduct = Redis::set("fetchproductbyvendor:" . $request->user_id, json_encode($result_array));
            $response = ['res' => true, 'msg' => "", 'data' => $result_array];
        }

        return response()->json($response);
    }

    public function fetchProducts(Request $request)
    {
        //DB::enableQueryLog();
        $result_array = [];
        $search = $request->search_key && !in_array($request->search_key, array('undefined', 'null')) ? $request->search_key : '';


        $productVariationsTbl = DB::raw("(SELECT product_id as vproduct_id,id as variant_id,value1,value2,value3,sku as vsku,stock as vstock,image as vimage
		FROM product_variations WHERE status='1') as pv");// Raw query is needed as nested query using for this function with alias.
        $products_sql = DB::table('products as p')
            ->select('p.*', 'pv.*')
            ->leftjoin($productVariationsTbl, 'pv.vproduct_id', '=', 'p.id')
            ->where('p.user_id', $request->user_id)
            ->orderBy('p.order_by', 'ASC');


        $allProductsCount = $products_sql->count();
        if (!empty($request->status)) {
            switch ($request->status) {
                case 'instock':
                    $products_sql->where(function ($products_sql) {
                        $products_sql->where('p.stock', '>', 0)
                            ->orWhere('pv.vstock', '>', 0);
                    });
                    break;
                case 'outofstock':
                    $products_sql->where('p.stock', '<', 1)->where('pv.vstock', '<', 1);
                    break;
                default:
                    break;
            }
        }

        if ($request->search_key && !in_array($request->search_key, array('undefined', 'null'))) {
            $products_sql->where('p.name', 'Like', '%' . $request->search_key . '%');
        }

        $isPrdctQuery = DB::table('products as p')
            ->select('p.*', 'pv.*')
            ->leftjoin($productVariationsTbl, 'pv.vproduct_id', '=', 'p.id')
            ->where('p.user_id', $request->user_id)
            ->where(function ($isPrdctQuery) {
                $isPrdctQuery->where('p.stock', '>', 0)
                    ->orWhere('pv.vstock', '>', 0);
            });
        $instockProductsCount = $isPrdctQuery->count();

        //dd(DB::getQueryLog());

        $osprdctQuery = DB::table('products as p')
            ->select('p.*', 'pv.*')
            ->leftjoin($productVariationsTbl, 'pv.vproduct_id', '=', 'p.id')
            ->where('p.user_id', $request->user_id)->where('p.stock', '<', 1)->where('pv.vstock', '<', 1);
        $outstockProductsCount = $osprdctQuery->count();


        $products = $products_sql->paginate(10);

        foreach ($products as $v) {
            $image = !empty($v->vimage) ? $v->vimage : $v->featured_image;
            $sku = !empty($v->vsku) ? $v->vsku : $v->sku;
            $stock = !empty($v->vstock) ? $v->vstock : $v->stock;
            $variableArr = [];
            if (!empty($v->value1)) {
                $variableArr[] = $v->value1;
            }
            if (!empty($v->value2)) {
                $variableArr[] = $v->value2;
            }
            if (!empty($v->value3)) {
                $variableArr[] = $v->value3;
            }
            $result_array[] = array(
                'id' => $v->id,
                'variant_id' => $v->variant_id,
                'variant' => implode('/', $variableArr),
                'name' => $v->name,
                'sku' => $sku,
                'featured_image' => $image,
                'stock' => $stock
            );
        }

        $data = array(
            "products" => $result_array,
            "instckprdcts_count" => $instockProductsCount,
            "outstckprdcts_count" => $outstockProductsCount,
            "allprdcts_count" => $allProductsCount
        );

        $fetchProduct = Redis::set("brandproduct:FetchProducts:" . $request->page . ":" . $search . ":" . $request->user_id . ":" . $request->status, json_encode($data));
        $response = ['res' => true, 'msg' => "", 'data' => $data];


        return response()->json($response);
    }


}
