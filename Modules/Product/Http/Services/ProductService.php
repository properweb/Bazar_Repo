<?php

namespace Modules\Product\Http\Services;

use Modules\User\Entities\User;
use Modules\Product\Entities\Product;
use Illuminate\Support\Str;
use Modules\Product\Entities\Video;
use Modules\Product\Entities\ProductVariation;
use Modules\Product\Entities\ProductImage;
use Modules\Product\Entities\ProductPrepack;
use Modules\Product\Entities\Category;
use File;
use DB;


class ProductService
{
    private $productAbsPath = "";
    private $productRelPath = "";
    protected Product $product;


    public function __construct()
    {
        $this->productAbsPath = public_path('uploads/products');
        $this->productRelPath = asset('public') . '/uploads/products/';
    }

    /**
     * @param $request
     * @return array
     */

    public function fetch($request): array
    {

        $resultArray = [];
        $productFetch = Product::where('user_id', $request->user_id);
        $productsCount = $productFetch->count();
        $productPublish = Product::where('user_id', $request->user_id)->where('status', 'publish');
        $publishCount = $productPublish->count();
        $productUnpublish = Product::where('user_id', $request->user_id)->where('status', 'unpublish');
        $unpublishedCount = $productUnpublish->count();
        $query = Product::where('user_id', $request->user_id);
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
            if ($productVariationsCount > 0) {
                $variantMinPrice = ProductVariation::where('product_id', $v->id)->min('price');
                $price = $variantMinPrice . '+';
                $variantStock = ProductVariation::where('product_id', $v->id)->sum('price');
                $availability = $variantStock > 0 ? 'in stock' : 'out of stock';
            } else {
                $price = $v->usd_wholesale_price;
                $availability = $v->stock > 0 ? 'in stock' : 'out of stock';
            }
            $resultArray[] = array(
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
            "products" => $resultArray,
            "pblshprdcts_count" => $publishCount,
            "unpblshprdcts_count" => $unpublishedCount,
            "allprdcts_count" => $productsCount
        );


        return ['res' => true, 'msg' => "", 'data' => $data];
    }

    /**
     * @param $request
     * @return array
     */
    public function arrange($request): array
    {

        $resultArray = [];
        $products = Product::where('user_id', $request->user_id)
            ->orderBy('order_by', 'ASC')
            ->get();

        foreach ($products as $v) {
            $productVariations = ProductVariation::where('product_id', $v->id)->where('status', '1')->get();
            $productVariationsCount = $productVariations->count();
            if ($productVariationsCount > 0) {
                $variantMinPrice = ProductVariation::where('product_id', $v->id)->min('price');
                $price = $variantMinPrice . '+';
                $variantStock = ProductVariation::where('product_id', $v->id)->sum('price');
                $availability = $variantStock > 0 ? 'in stock' : 'out of stock';
            } else {
                $price = $v->usd_wholesale_price;
                $availability = $v->stock > 0 ? 'in stock' : 'out of stock';
            }
            $resultArray[] = array(
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
        }

        return ['res' => true, 'msg' => "", 'data' => $resultArray];
    }

    /**
     * @param $request
     * @return array
     */
    public function FetchStock($request): array
    {

        $resultArray = [];
        $productVariationsTbl = DB::raw("(SELECT product_id as vProductId,id as variant_id,value1,value2,value3,sku as vSku,stock as vStock,image as vImage
		FROM product_variations WHERE status='1') as pv");// Raw query is needed as nested query using for this function with alias.
        $products_sql = DB::table('products as p')
            ->select('p.*', 'pv.*')
            ->leftjoin($productVariationsTbl, 'pv.vProductId', '=', 'p.id')
            ->where('p.user_id', $request->user_id)
            ->orderBy('p.order_by', 'ASC');


        $allProductsCount = $products_sql->count();
        if (!empty($request->status)) {
            switch ($request->status) {
                case 'instock':
                    $products_sql->where(function ($products_sql) {
                        $products_sql->where('p.stock', '>', 0)
                            ->orWhere('pv.vStock', '>', 0);
                    });
                    break;
                case 'outofstock':
                    $products_sql->where('p.stock', '<', 1)->where('pv.vStock', '<', 1);
                    break;
                default:
                    break;
            }
        }

        if ($request->search_key && !in_array($request->search_key, array('undefined', 'null'))) {
            $products_sql->where('p.name', 'Like', '%' . $request->search_key . '%');
        }

        $isProductQuery = DB::table('products as p')
            ->select('p.*', 'pv.*')
            ->leftjoin($productVariationsTbl, 'pv.vProductId', '=', 'p.id')
            ->where('p.user_id', $request->user_id)
            ->where(function ($isProductQuery) {
                $isProductQuery->where('p.stock', '>', 0)
                    ->orWhere('pv.vStock', '>', 0);
            });
        $inStockProductsCount = $isProductQuery->count();

        $osProductQuery = DB::table('products as p')
            ->select('p.*', 'pv.*')
            ->leftjoin($productVariationsTbl, 'pv.vProductId', '=', 'p.id')
            ->where('p.user_id', $request->user_id)->where('p.stock', '<', 1)->where('pv.vStock', '<', 1);
        $outStockProductsCount = $osProductQuery->count();


        $products = $products_sql->paginate(10);

        foreach ($products as $v) {
            $image = !empty($v->vImage) ? $v->vImage : $v->featured_image;
            $sku = !empty($v->vSku) ? $v->vSku : $v->sku;
            $stock = !empty($v->vStock) ? $v->vStock : $v->stock;
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
            $resultArray[] = array(
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
            "products" => $resultArray,
            "instckprdcts_count" => $inStockProductsCount,
            "outstckprdcts_count" => $outStockProductsCount,
            "allprdcts_count" => $allProductsCount
        );

        return ['res' => true, 'msg' => "", 'data' => $data];


    }

    /**
     * @param $request
     * @return array
     */
    public function create($request): array
    {
        $usdWholesalePrice = $request->usd_wholesale_price && !in_array($request->usd_wholesale_price, array('undefined', 'null')) ? $request->usd_wholesale_price : 0;
        $usdRetailPrice = $request->usd_retail_price && !in_array($request->usd_wholesale_price, array('undefined', 'null')) ? $request->usd_retail_price : 0;
        $cadWholesalePrice = $request->cad_wholesale_price && !in_array($request->cad_wholesale_price, array('undefined', 'null')) ? $request->cad_wholesale_price : 0;
        $cadRetailPrice = $request->cad_retail_price && !in_array($request->cad_retail_price, array('undefined', 'null')) ? $request->cad_retail_price : 0;
        $gbpWholesalePrice = $request->gbp_wholesale_price && !in_array($request->gbp_wholesale_price, array('undefined', 'null')) ? $request->gbp_wholesale_price : 0;
        $gbpRetailPrice = $request->gbp_retail_price && !in_array($request->gbp_retail_price, array('undefined', 'null')) ? $request->gbp_retail_price : 0;
        $eurWholesalePrice = $request->eur_wholesale_price && !in_array($request->eur_wholesale_price, array('undefined', 'null')) ? $request->eur_wholesale_price : 0;
        $eurRetailPrice = $request->eur_retail_price && !in_array($request->eur_retail_price, array('undefined', 'null')) ? $request->eur_retail_price : 0;
        $audWholesalePrice = $request->aud_wholesale_price && !in_array($request->aud_wholesale_price, array('undefined', 'null')) ? $request->aud_wholesale_price : 0;
        $audRetailPrice = $request->aud_retail_price && !in_array($request->aud_retail_price, array('undefined', 'null')) ? $request->aud_retail_price : 0;
        $testerPrice = $request->testers_price && !in_array($request->testers_price, array('undefined', 'null')) ? $request->testers_price : 0;
        $shippingTariffCode = $request->shipping_tariff_code && !in_array($request->shipping_tariff_code, array('undefined', 'null')) ? $request->shipping_tariff_code : '';
        $caseQuantity = $request->order_case_qty ?? 0;
        $minCaseQuantity = $request->order_min_case_qty ?? 0;
        $sellType = $request->sell_type ?: 1;
        $prepackType = $sellType == 3 ? $request->prepack_type : 1;

        if (!empty($request->shipping_inventory)) {

            $stock = $request->shipping_inventory;
        } else {
            $stock = 0;
        }
        $mainCategory = '';
        $category = '';
        $outsideUs = $request->outside_us == 'true' ? 1 : 0;
        $subCategory = $request->product_type;
        $subCategoryDetails = Category::where('id', $subCategory)->first();

        if (!empty($subCategoryDetails)) {
            $categoryDetails = Category::where('id', $subCategoryDetails->parent_id)->first();
            $category = $categoryDetails->id;
            $mainCategory = $categoryDetails->parent_id;
        }
        $productKey = 'p_' . Str::lower(Str::random(10));
        $productSlug = Str::slug($request->product_name);

        $product = new Product();
        $product->product_key = $productKey;
        $product->slug = $productSlug;
        $product->name = $request->product_name;
        $product->user_id = $request->user_id;
        $product->main_category = $mainCategory;
        $product->category = $category;
        $product->sub_category = $subCategory;
        $product->status = "publish";
        $product->description = $request->description;
        $product->country = $request->product_made;
        $product->case_quantity = $caseQuantity;
        $product->min_order_qty = $minCaseQuantity;
        $product->sku = $request->shipping_sku;
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
        $product->dimension_unit = $request->dimension_unit;
        $product->is_bestseller = $request->is_bestseller;
        $product->shipping_height = $request->shipping_height;
        $product->stock = $stock;
        $product->shipping_length = $request->shipping_length;
        $product->shipping_weight = $request->shipping_weight;
        $product->shipping_width = $request->shipping_width;
        $product->weight_unit = $request->weight_unit;
        $product->reatailers_inst = addslashes($request->reatailers_inst);
        $product->reatailer_input_limit = $request->reatailer_input_limit;
        $product->retailer_min_qty = $request->retailer_min_qty;
        $product->retailer_add_charge = $request->retailer_add_charge;
        $product->product_shipdate = date('Y-m-d', strtotime($request->product_shipdate)) ? $request->product_shipdate : '';
        $product->product_endshipdate = date('Y-m-d', strtotime($request->product_endshipdate)) ? $request->product_endshipdate : '';
        $product->product_deadline = date('Y-m-d', strtotime($request->product_deadline)) ? $request->product_deadline : '';
        $product->out_of_stock = $request->out_of_stock;
        $product->keep_product = $request->keep_product;
        $product->sell_type = $sellType;
        $product->prepack_type = $prepackType;
        $product->outside_us = $outsideUs;
        $product->tariff_code = $shippingTariffCode;
        $product->created_at = date('Y-m-d H:i:s');
        $product->updated_at = date('Y-m-d H:i:s');
        $product->save();

        $lastInsertId = $product->id;

        if (!empty($request->file('video_url'))) {
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
        if (!empty($request->file('product_images'))) {
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
        Product::where('id', $lastInsertId)
            ->update([
                'featured_image' => $featuredImage
            ]);
        $optionTypes = explode(',', $request->option_type);
        $colorKey = in_array('Color', $optionTypes) ? array_search("Color", $optionTypes) + 1 : 0;
        $colors = [];
        $swatches = [];
        $colorOptions = json_decode($request->colorOptionItems, true);
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

        $variations = json_decode($request->variations, true);

        if (is_countable($variations) && count($variations) > 0) {
            foreach ($variations as $vars) {
                $sImage = '';
                if ($colorKey > 0) {
                    $colorVal = $vars['value' . $colorKey];
                    $sColorKey = in_array($colorVal, $colors) ? array_search($colorVal, $colors) : 0;
                    $sImage = $swatches[$sColorKey];
                }
                $imageIndex = (int)$vars["image_index"];
                $vImage = $variationImages[$imageIndex] ?? '';

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
                $productVariation->swatch_image = $sImage;
                $productVariation->image = $vImage;
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

        if ($request->sell_type == 3) {
            $prePacks = json_decode($request->pre_packs, true);
            if ($prePacks) {
                Product::where('id', $lastInsertId)
                    ->update([
                        'sell_type' => 3
                    ]);
                foreach ($prePacks as $prePack) {
                    $active = $prePack['active'];
                    $packsPrice = $prePack['packs_price'] && !in_array($prePack['packs_price'], array('', 'undefined', 'null')) ? $prePack['packs_price'] : 0;
                    $productPrepack = new ProductPrepack();
                    $productPrepack->product_id = $lastInsertId;
                    $productPrepack->style = $prePack['style'];
                    $productPrepack->pack_name = $prePack['pack_name'];
                    $productPrepack->size_ratio = $prePack['size_ratio'];
                    $productPrepack->size_range = $prePack['size_range_value'];
                    $productPrepack->packs_price = $packsPrice;
                    $productPrepack->active = $active;
                    $productPrepack->save();
                }
            }
        }

        return [
            'res' => true,
            'msg' => 'Product created successfully',
            'data' => ''
        ];
    }

    /**
     * @param $request
     * @return array
     */

    public function details($request): array
    {
        $resultArray = [];

        $products = Product::where('id', $request->id)->first();
        $prevProduct = Product::where('user_id', $products->user_id)->where('id', '<', $request->id)->orderBy('id', 'DESC')->first();
        $prevProductId = $prevProduct ? $prevProduct->id : 0;
        $nextProduct = Product::where('user_id', $products->user_id)->where('id', '>', $request->id)->orderBy('id', 'ASC')->first();
        $nextProductId = $nextProduct ? $nextProduct->id : 0;
        $brandDetails = Product::where('user_id', $products->user_id)->first();
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
            foreach ($productPrepacks as $pPkey => $pPVal) {
                if (!in_array($pPVal->size_range, $prepackSizeRanges)) {
                    $prepackSizeRanges[] = $pPVal->size_range;
                }
                $prePacks[] = array(
                    'id' => $pPVal->id,
                    'product_id' => $pPVal->product_id,
                    'style' => $pPVal->style,
                    'pack_name' => $pPVal->pack_name,
                    'size_ratio' => $pPVal->size_ratio,
                    'size_range_value' => $pPVal->size_range,
                    'packs_price' => $pPVal->packs_price,
                    'active' => $pPVal->active,
                    'status' => 'published',
                );
            }
        }
        if (!empty($prePacks)) {
            foreach ($prePacks as $pKey => $pVal) {
                $prePacks[$pKey]['size_range'] = $prepackSizeRanges;
            }
        }

        $allVariations = [];
        $swatches = [];
        $swatchImg = [];
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


                if (!in_array($var->swatch_image, $swatchImg) && !empty($var->swatch_image)) {
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
        return ['res' => true, 'msg' => "", 'data' => $resultArray];
    }

    /**
     * @param $request
     * @return array
     */
    public function update($request): array
    {

        $productId = $request->id;
        $usdWholesalePrice = $request->usd_wholesale_price && !in_array($request->usd_wholesale_price, array('undefined', 'null')) ? $request->usd_wholesale_price : 0;
        $usdRetailPrice = $request->usd_retail_price && !in_array($request->usd_wholesale_price, array('undefined', 'null')) ? $request->usd_retail_price : 0;
        $cadWholesalePrice = $request->cad_wholesale_price && !in_array($request->cad_wholesale_price, array('undefined', 'null')) ? $request->cad_wholesale_price : 0;
        $cadRetailPrice = $request->cad_retail_price && !in_array($request->cad_retail_price, array('undefined', 'null')) ? $request->cad_retail_price : 0;
        $gbpWholesalePrice = $request->gbp_wholesale_price && !in_array($request->gbp_wholesale_price, array('undefined', 'null')) ? $request->gbp_wholesale_price : 0;
        $gbpRetailPrice = $request->gbp_retail_price && !in_array($request->gbp_retail_price, array('undefined', 'null')) ? $request->gbp_retail_price : 0;
        $eurWholesalePrice = $request->eur_wholesale_price && !in_array($request->eur_wholesale_price, array('undefined', 'null')) ? $request->eur_wholesale_price : 0;
        $eurRetailPrice = $request->eur_retail_price && !in_array($request->eur_retail_price, array('undefined', 'null')) ? $request->eur_retail_price : 0;
        $audWholesalePrice = $request->aud_wholesale_price && !in_array($request->aud_wholesale_price, array('undefined', 'null')) ? $request->aud_wholesale_price : 0;
        $audRetailPrice = $request->aud_retail_price && !in_array($request->aud_retail_price, array('undefined', 'null')) ? $request->aud_retail_price : 0;
        $shippingTariffCode = $request->shipping_tariff_code && !in_array($request->shipping_tariff_code, array('undefined', 'null')) ? $request->shipping_tariff_code : '';
        $caseQuantity = $request->order_case_qty ?? 0;
        $minCaseQuantity = $request->order_min_case_qty ?? 0;
        $sellType = $request->sell_type ?: 1;
        $prePackType = $sellType == 3 ? $request->prepack_type : 1;
        if (!empty($request->shipping_inventory)) {

            $stock = $request->shipping_inventory;
        } else {
            $stock = 0;
        }
        $mainCategory = '';
        $category = '';
        $outsideUs = $request->outside_us == 'true' ? 1 : 0;
        $subCategory = $request->product_type;
        $subCategoryDetails = Category::where('id', $subCategory)->first();

        if ($subCategoryDetails) {
            $categoryDetails = Category::where('id', $subCategoryDetails->parent_id)->first();
            $category = $categoryDetails->id;
            $mainCategory = $categoryDetails->parent_id;
        }

        $data = array(
            'name' => $request->product_name,
            'main_category' => $mainCategory,
            'category' => $category,
            'sub_category' => $subCategory,
            'description' => $request->description,
            'country' => $request->product_made,
            'case_quantity' => $caseQuantity,
            'min_order_qty' => $minCaseQuantity,
            'min_order_qty_type' => $request->min_order_qty_type,
            'sku' => $request->shipping_sku,
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
            'usd_tester_price' => $request->usd_tester_price,
            'dimension_unit' => $request->dimension_unit,
            'is_bestseller' => $request->is_bestseller,
            'shipping_height' => $request->shipping_height,
            'stock' => $stock,
            'shipping_length' => $request->shipping_length,
            'shipping_weight' => $request->shipping_weight,
            'shipping_width' => $request->shipping_width,
            'weight_unit' => $request->weight_unit,
            'reatailers_inst' => $request->reatailers_inst,
            'reatailer_input_limit' => $request->reatailer_input_limit,
            'retailer_min_qty' => $request->retailer_min_qty,
            'retailer_add_charge' => $request->retailer_add_charge,
            'product_shipdate' => $request->product_shipdate,
            'product_endshipdate' => $request->product_endshipdate,
            'product_deadline' => $request->product_deadline,
            'keep_product' => $request->keep_product,
            'featured_image' => '',
            'out_of_stock' => $request->out_of_stock,
            'sell_type' => $sellType,
            'prepack_type' => $prePackType,
            'tariff_code' => $shippingTariffCode,
            'outside_us' => $outsideUs,
            'updated_at' => date('Y-m-d H:i:s'),
        );
        Product::where('id', $productId)->update($data);


        if (!empty($request->file('video_url'))) {
            foreach ($request->file('video_url') as $key => $file) {
                $fileName = rand() . time() . '.' . $file->extension();
                $file->move($this->productAbsPath, $fileName);
                $video = new Video();
                $video->product_id = $productId;
                $video->video_url = $this->productRelPath . $fileName;
                $video->save();
            }
        }

        $variationImages = [];
        $prev_product_images = ProductImage::where('product_id', $productId)->get();
        if (!empty($prev_product_images)) {
            foreach ($prev_product_images as $previmg) {
                $variationImages[] = $previmg->images;
            }
        }

        $featuredKey = isset($request->featured_image) && !empty($request->featured_image) ? (int)$request->featured_image : 0;

        $checkImage = ProductImage::where('product_id', $productId)->orderBy('image_sort', 'desc')->first();
        if (!empty($checkImage)) {
            $imageKey = $checkImage->image_sort + 1;
        } else {
            $imageKey = 0;
        }

        if (!empty($request->file('product_images'))) {
            foreach ($request->file('product_images') as $key => $file) {
                $fileName = rand() . time() . '.' . $file->extension();
                $file->move($this->productAbsPath, $fileName);
                $productImages = new ProductImage();
                $productImages->product_id = $productId;
                $productImages->images = $this->productRelPath . $fileName;
                $productImages->image_sort = $imageKey + $key;
                $productImages->feature_key = 0;
                $productImages->save();
                $variationImages[] = $this->productRelPath . $fileName;
            }
        }

        ProductImage::where('product_id', $productId)
            ->update([
                'feature_key' => 0
            ]);

        ProductImage::where('image_sort', $featuredKey)->where('product_id', $productId)
            ->update([
                'feature_key' => 1
            ]);
        $productImage = ProductImage::where('product_id', $productId)->where('image_sort', $featuredKey)->first();
        $featuredImage = $productImage->images;
        Product::where('id', $productId)
            ->update([
                'featured_image' => $featuredImage
            ]);
        $productFeatureImage = ProductImage::where('product_id', $productId)->where('feature_key', '1')->first();
        Product::where('id', $productId)->update(array('featured_image' => $productFeatureImage->images));
        $optionTypes = explode(',', $request->option_type);
        $colorKey = in_array('Color', $optionTypes) ? (int)(array_search("Color", $optionTypes)) + 1 : 0;
        $colors = [];
        $swatches = [];
        $colorOptions = json_decode($request->colorOptionItems, true);


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

        $variations = json_decode($request->variations, true);

        if (!empty($variations)) {
            ProductVariation::where('product_id', $productId)->delete();

            foreach ($variations as $vars) {
                $sImage = '';
                if (count($colorOptions) > 0 && $colorKey > 0) {
                    $colorVal = $vars['value' . $colorKey];
                    $sColorKey = in_array($colorVal, $colors) ? array_search($colorVal, $colors) : 0;
                    $sImage = $swatches[$sColorKey];
                }


                if (isset($vars["image_index"])) {
                    $imageIndex = (int)$vars["image_index"];
                    $vImage = $variationImages[$imageIndex] ?? '';
                } else {
                    $vImage = $vars['preview_images'];
                }
                $wholesalePrice = $vars['usd_wholesale_price'] && !in_array($vars['usd_wholesale_price'], array('undefined', 'null')) ? $vars['usd_wholesale_price'] : 0;
                $retailPrice = $vars['usd_retail_price'] && !in_array($vars['usd_retail_price'], array('undefined', 'null')) ? $vars['usd_retail_price'] : 0;
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
                    $productVariation->swatch_image = $sImage;
                    $productVariation->image = $vImage;
                    $productVariation->product_id = $productId;
                    $productVariation->price = $wholesalePrice;
                    $productVariation->options1 = $vars['option1'];
                    $productVariation->options2 = $vars['option2'];
                    $productVariation->options3 = $vars['option3'];
                    $productVariation->sku = $vars['sku'];
                    $productVariation->value1 = $vars['value1'];
                    $productVariation->value2 = $vars['value2'];
                    $productVariation->value3 = $vars['value3'];
                    $productVariation->retail_price = $retailPrice;
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
                    $productVariation->website = $vars['website'] ?? '';
                    $productVariation->website_product_id = $vars['website_product_id'] ?? '';
                    $productVariation->inventory_item_id = $vars['inventory_item_id'] ?? '';

                    $productVariation->save();
                }
            }
        }
        if (is_countable($variations) && count($variations) == 1) {
            Product::where('id', $productId)->update(array("stock" => $variations[0]['inventory']));
        }
        if (is_countable($variations) && count($variations) == 0) {

            ProductVariation::where('product_id', $productId)->update(array('status' => 2));
        }

        if ($sellType == 3) {
            $prePacks = json_decode($request->pre_packs, true);
            if (!empty($prePacks)) {
                Product::where('id', $productId)->update(array('sell_type' => '3'));
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
                        $productPack = new ProductPrepack();
                        $productPack->product_id = $productId;
                        $productPack->style = $prePack['style'];
                        $productPack->pack_name = $prePack['pack_name'];
                        $productPack->size_ratio = $prePack['size_ratio'];
                        $productPack->size_range = $prePack['size_range_value'];
                        $productPack->packs_price = $packsPrice;
                        $productPack->active = $active;
                        $productPack->save();
                    }
                }
            }
        }

        return ['res' => true, 'msg' => "Updated Successfully", 'data' => ""];
    }

    /**
     * @param $request
     * @return array
     */
    public function status($request): array
    {
        $ids = explode(",", $request->id);
        if ($request->status == 'publish') {
            $errorMsg = 0;
            $productDetails = Product::where('id', $request->id)->first();
            $resImages = ProductImage::where('product_id', $productDetails->id)->get();
            $usdWholesalePrice = (float)$productDetails->usd_wholesale_price;
            $usdRetailPrice = (float)$productDetails->usd_retail_price;
            $productVariations = ProductVariation::where('product_id', $request->id)->where('status', '1')->get();
            $productVariationsCount = $productVariations->count();
            if ($productVariationsCount > 0) {
                $productVariations->toArray();
                $usdWholesalePrice = (float)$productVariations[0]->price;
                $usdRetailPrice = (float)$productVariations[0]->retail_price;
            }

            if ($productDetails->name == '') {
                $errorMsg++;
            } elseif ($productDetails->main_category == 0 || $productDetails->category == 0 || $productDetails->sub_category == 0) {
                $errorMsg++;
            } elseif ($productDetails->country == 0 || in_array($productDetails->country, array('undefined', 'null', ''))) {
                $errorMsg++;
            } elseif (count($resImages) == 0) {
                $errorMsg++;
            } elseif ($usdWholesalePrice == 0) {
                $errorMsg++;
            } elseif ($usdRetailPrice == 0) {
                $errorMsg++;
            } elseif ($productDetails->sell_type == '1' && (int)$productDetails->case_quantity == 0) {
                $errorMsg++;
            } elseif ((int)$productDetails->min_order_qty == 0) {
                $errorMsg++;
            }

            if ($errorMsg == 0) {
                Product::whereIn("id", $ids)
                    ->update([
                        'status' => $request->status
                    ]);
                $response = ['res' => true, 'msg' => "Updated Successfully", 'data' => ""];
            } else {
                $response = ['res' => false, 'msg' => "Please fill all required fields", 'data' => ""];
            }
        } else {
            Product::whereIn("id", $ids)
                ->update([
                    'status' => $request->status
                ]);
            $response = ['res' => true, 'msg' => "Updated Successfully", 'data' => ""];
        }

        return $response;
    }

    /**
     * @param $request
     * @return array
     */
    public function delete($request): array
    {
        $ids = explode(",", $request->id);
        Product::whereIn('id', $ids)->delete();
        ProductImage::where('product_id', $request->id)->delete();
        ProductVariation::where('product_id', $request->id)->delete();
        Video::where('product_id', $request->id)->delete();

        return ['res' => true, 'msg' => "Deleted Successfully", 'data' => ""];


    }

    /**
     * @param $request
     * @return array
     */
    public function DeleteImage($request): array
    {
        ProductImage::where('id', $request->image_id)->delete();

        return ['res' => true, 'msg' => "Deleted Successfully", 'data' => ""];


    }

    /**
     * @param $request
     * @return array
     */
    public function DeleteVideo($request): array
    {
        Video::where('id', $request->id)->delete();

        return ['res' => true, 'msg' => "Deleted Successfully", 'data' => ""];


    }

    /**
     * @param $request
     * @return array
     */
    public function reorder($request): array
    {
        $items = $request->items;

        foreach ($items as $k => $v) {
            $product = Product::find($v);
            $product->order_by = $k;
            $product->save();
        }
        return ['res' => true, 'msg' => "", 'data' => ""];
    }

    public function UpdateStock($request): array
    {
        if ($request->variant_id) {
            ProductVariation::where('id', $request->variant_id)->update(array('stock' => $request->stock));
        } else {
            Product::where('id', $request->id)->update(array('stock' => $request->stock));
        }
        return ['res' => true, 'msg' => "", 'data' => ""];
    }

    /**
     * @param $image
     * @return string
     */


    private function image64Upload($image): string
    {
        $image_64 = $image;
        $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
        $image_64 = str_replace($replace, '', $image_64);
        $image_64 = str_replace(' ', '+', $image_64);
        $imageName = Str::random(10) . '.' . 'png';

        File::put($this->productAbsPath . "/" . $imageName, base64_decode($image_64));
        return $this->productRelPath . $imageName;
    }


}
