<?php

namespace Modules\Backend\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Entities\ProductImage;
use Modules\Product\Entities\ProductPrepack;
use Modules\Product\Entities\Products;
use Modules\Product\Entities\ProductVariation;
use Modules\Product\Entities\Video;
use Modules\Country\Entities\Country;
use Session;
use DB;

class ProductController extends Controller
{

    public function index(Request $request)
    {
        if (!Session::has('AdminId')) {
            return redirect('/backend');
        }

        return view('backend::ProductList', ['brand_id' => $request->brand_id]);

    }
    public function vendorProduct(Request $request)
    {
        if (!Session::has('AdminId')) {
            return redirect('/backend');
        }
        $products = Products::where('user_id', $request->brand_id)->where('status', 'publish')->orderBy('updated_at', 'DESC')->get();
        $slno = 1;
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
            $data[] = array(
                'slno' => $slno,
                'id' => $v->id,
                'product_key' => $v->product_key,
                'import_type' => $v->import_type,
                'name' => $v->name,
                'category' => $v->category,
                'sku' => $v->sku,
                'case_quantity' => $v->case_quantity,
                'min_order_qty' => $v->min_order_qty,
                'stock' => $v->stock,
                'default_currency' => $v->default_currency,
                'variations_count' => $productVariationsCount,
                'availability' => $availability,
                'website' => $v->website,

            );
            $slno++;
        }
        echo $json_data = json_encode(array("data" => $data));
        exit;
    }

    public function productDetail(Request $request)
    {
        if (!Session::has('AdminId')) {
            return redirect('/backend');
        }
        $products = Products::where('id', $request->id)->first();
        $brandDetails = Products::where('user_id', $products->user_id)->first();
        $categories = DB::table('category AS r')
            ->leftJoin('category AS e', 'e.id', '=', 'r.parent_id')
            ->leftJoin('category AS l', 'r.id', '=', 'l.parent_id')
            ->select('e.name AS parent_name',
                'e.id AS parent_id',
                'r.id AS child_id',
                'r.name AS child_name',
                'l.id AS last_id',
                'l.name AS last_name')
            ->where('l.id', '=', $products->sub_category)
            ->get();
        $category = $categories[0]->parent_name . '->' . $categories[0]->child_name . '->' . $categories[0]->last_name;
        $country = Country::where('id', $products->country)->first();
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
            $resultArray[] = array(
                'id' => $products->id,
                'bazaar_direct_link' => $bazaarDirectLink,
                'name' => $products->name,
                'category' => $category,
                'status' => $products->status,
                'description' => strip_tags($products->description),
                'country' => $country->name,
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

            );
        }

        return view('backend::ProductDetail', ['data' => $resultArray]);

    }


}
