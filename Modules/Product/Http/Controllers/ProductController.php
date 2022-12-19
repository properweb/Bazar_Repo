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
use Modules\Product\Entities\ProductImage;
use Modules\Product\Entities\ProductPrepack;
use Modules\Product\Entities\Category;


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
