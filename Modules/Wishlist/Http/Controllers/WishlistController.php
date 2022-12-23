<?php

namespace Modules\Wishlist\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Wishlist\Entities\Wishlist;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Product\Entities\Products;
use Modules\Product\Entities\ProductVariation;
use Modules\Product\Entities\ProductPrepack;
use Modules\Country\Entities\Country;

class WishlistController extends Controller
{
    public function add(Request $request)
    {
        if (empty($request->product_id)) {
            $response = ['res' => false, 'msg' => 'Invalid Product', 'data' => ""];
            return response()->json($response);
        }
        $product = Products::find($request->product_id);
        $productType = 'SINGLE_PRODUCT';
        $productName = $product->name;
        $productSKU = $product->sku;

        if (empty($product)) {
            $response = ['res' => false, 'msg' => 'Invalid Product', 'data' => ""];
            return response()->json($response);
        }
        if (!empty($request->variant_id)) {
            $variant = ProductVariation::find($request->variant_id);
            $variantId = $variant->id;
            if (!empty($request->openSizingArray)) {
                $productType = 'OPEN_SIZING';
            }
            if ($request->prepack_id) {
                $productType = 'PREPACK';
                $variantId = $request->prepack_id;
            }
        }

        if (!empty($request->variant_id) && $variant) {
            $alreadyCart = Wishlist::where('user_id', $request->user_id)->where('cart_id', null)->where('product_id', $product->id)->where('variant_id', $variantId)->where('type', $productType)->first();
        } else {
            $alreadyCart = Wishlist::where('user_id', $request->user_id)->where('cart_id', null)->where('product_id', $product->id)->where('type', $productType)->first();
        }
        if ($alreadyCart) {
            $alreadyCart->quantity = $alreadyCart->quantity + (int)$request->quantity;
            $alreadyCart->amount = $product->price + $alreadyCart->amount;
            if (!empty($request->variant_id) && $variant) {
                if ($productType == 'OPEN_SIZING') {
                    $optionsArr = [];
                    $styleArr = [];
                    $styleGrpArr = [];
                    $sizeArr = [];
                    $priceArr = [];
                    $qtyArr = [];
                    $reference_arr = unserialize($alreadyCart->reference);

                    if (!empty($request->openSizingArray)) {
                        foreach ($request->openSizingArray as $size) {
                            $sizeVariant = ProductVariation::where('product_id', $product->id)->where('options1', "Size")->where('value1', $size['value'])->where('value2', $variant->value2)->where('value3', $variant->value3)->first();
                            if ($variant->options1 == 'Size') {
                                $sizeVariant = ProductVariation::where('product_id', $product->id)->where('options1', "Size")->where('value1', $size['value'])->where('value2', $variant->value2)->where('value3', $variant->value3)->first();
                            }
                            if ($variant->options2 == 'Size') {
                                $sizeVariant = ProductVariation::where('product_id', $product->id)->where('options2', "Size")->where('value2', $size['value'])->where('value1', $variant->value1)->where('value3', $variant->value3)->first();
                            }
                            if ($variant->options3 == 'Size') {
                                $sizeVariant = ProductVariation::where('product_id', $product->id)->where('options3', "Size")->where('value3', $size['value'])->where('value2', $variant->value2)->where('value1', $variant->value1)->first();
                            }
                            $ord_qty = $reference_arr[$sizeVariant->id] ?? 0;
                            $new_qty = $ord_qty + $size['qty'];

                            if ($sizeVariant->stock < $new_qty || $sizeVariant->stock <= 0) {
                                $response = ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];
                                return response()->json($response);
                            }
                            $optionsArr[$sizeVariant->id] = $new_qty;
                            $sizeArr[] = $size['value'];
                            $styleGrpArr[] = "(" . $new_qty . ")" . $size['value'];
                            $priceArr[] = $sizeVariant->price;
                            $qtyArr[] = $new_qty;
                        }
                    }
                    if ($variant->options1 == 'Size') {
                        $styleArr[] = $variant->value2;
                        $styleArr[] = $variant->value3;
                    }
                    if ($variant->options2 == 'Size') {
                        $styleArr[] = $variant->value1;
                        $styleArr[] = $variant->value3;
                    }
                    if ($variant->options3 == 'Size') {
                        $styleArr[] = $variant->value2;
                        $styleArr[] = $variant->value1;
                    }
                    $tprice = 0;
                    foreach ($qtyArr as $qk => $qv) {
                        $tprice += $priceArr[$qk] * $qv;
                    }
                    $alreadyCart->price = $tprice;
                    $alreadyCart->style_name = rtrim(implode(',', $styleArr), ',');
                    $alreadyCart->style_group_name = rtrim(implode(',', $styleGrpArr), ',');
                    $alreadyCart->reference = serialize($optionsArr);
                }
                if ($productType == 'PREPACK') {
                    $prepackVariant = ProductPrepack::where('id', $request->prepack_id)->first();
                    $alreadyCart->price = $prepackVariant->packs_price;
                    $alreadyCart->style_name = $prepackVariant->style;
                    $alreadyCart->style_group_name = $prepackVariant->size_ratio . ';' . $prepackVariant->size_range;
                }
                if ($productType == 'SINGLE_PRODUCT') {
                    if ($variant->stock < $alreadyCart->quantity || $variant->stock <= 0) {
                        $response = ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];
                        return response()->json($response);
                    }
                }
            } else {
                if ($variant->stock < $alreadyCart->quantity || $variant->stock <= 0) {
                    $response = ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];
                    return response()->json($response);
                }
            }
            $alreadyCart->save();
        } else {
            $wishlist = new Wishlist;

            $wishlist->user_id = $request->user_id;
            $wishlist->product_id = $product->id;
            $wishlist->brand_id = $product->user_id;
            $wishlist->product_name = $productName;
            $wishlist->product_sku = $productSKU;
            //$wishlist->price = ($product->usd_wholesale_price - ($product->usd_wholesale_price * $product->discount) / 100);
            //$wishlist->price = $product->usd_wholesale_price;
            $wishlist->quantity = (int)$request->quantity;

            if (!empty($request->variant_id) && $variant) {
                $wishlist->price = $variant->price;
                if ($productType == 'OPEN_SIZING') {
                    $optionsArr = [];
                    $styleArr = [];
                    $styleGrpArr = [];
                    $sizeArr = [];
                    $priceArr = [];
                    $qtyArr = [];

                    if (!empty($request->openSizingArray)) {
                        foreach ($request->openSizingArray as $size) {
                            $sizeVariant = ProductVariation::where('product_id', $product->id)->where('options1', "Size")->where('value1', $size['value'])->where('value2', $variant->value2)->where('value3', $variant->value3)->first();
                            if ($variant->options1 == 'Size') {
                                $sizeVariant = ProductVariation::where('product_id', $product->id)->where('options1', "Size")->where('value1', $size['value'])->where('value2', $variant->value2)->where('value3', $variant->value3)->first();
                            }
                            if ($variant->options2 == 'Size') {
                                $sizeVariant = ProductVariation::where('product_id', $product->id)->where('options2', "Size")->where('value2', $size['value'])->where('value1', $variant->value1)->where('value3', $variant->value3)->first();
                            }
                            if ($variant->options3 == 'Size') {
                                $sizeVariant = ProductVariation::where('product_id', $product->id)->where('options3', "Size")->where('value3', $size['value'])->where('value2', $variant->value2)->where('value1', $variant->value1)->first();
                            }
                            if ($sizeVariant->stock < $wishlist->quantity || $sizeVariant->stock <= 0) {
                                $response = ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];
                                return response()->json($response);
                            }
                            $optionsArr[$sizeVariant->id] = $size['qty'];
                            $sizeArr[] = $size['value'];
                            $styleGrpArr[] = "(" . $size['qty'] . ")" . $size['value'];
                            $priceArr[] = $sizeVariant->price;
                            $qtyArr[] = $size['qty'];
                        }
                    }
                    if ($variant->options1 == 'Size') {
                        $styleArr[] = $variant->value2;
                        $styleArr[] = $variant->value3;
                    }
                    if ($variant->options2 == 'Size') {
                        $styleArr[] = $variant->value1;
                        $styleArr[] = $variant->value3;
                    }
                    if ($variant->options3 == 'Size') {
                        $styleArr[] = $variant->value2;
                        $styleArr[] = $variant->value1;
                    }
                    $tprice = 0;
                    foreach ($qtyArr as $qk => $qv) {
                        $tprice += $priceArr[$qk] * $qv;
                    }
                    $wishlist->price = $tprice;
                    $wishlist->style_name = rtrim(implode(',', $styleArr), ',');
                    $wishlist->style_group_name = rtrim(implode(',', $styleGrpArr), ',');
                    $wishlist->reference = serialize($optionsArr);
                    $wishlist->variant_id = $request->variant_id;
                }
                if ($productType == 'PREPACK') {
                    $prepackVariant = ProductPrepack::where('id', $request->prepack_id)->first();
                    $wishlist->price = $prepackVariant->packs_price;
                    $wishlist->style_name = $prepackVariant->style;
                    $wishlist->style_group_name = $prepackVariant->size_ratio . ';' . $prepackVariant->size_range;
                    $wishlist->reference = $request->variant_id;
                    $wishlist->variant_id = $request->prepack_id;
                }
                if ($productType == 'SINGLE_PRODUCT') {
                    if ($variant->stock < $wishlist->quantity || $variant->stock <= 0) {
                        $response = ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];
                        return response()->json($response);
                    }
                    $styleArr = [];
                    $styleGrpArr = [];
                    $styleArr[] = $variant->value1;
                    $styleArr[] = $variant->value2;
                    $styleArr[] = $variant->value3;
                    $wishlist->style_name = rtrim(implode(',', $styleArr), ',');
                    $wishlist->style_group_name = rtrim(implode(',', $styleGrpArr), ',');
                    $wishlist->reference = $variant->id;
                    $wishlist->variant_id = $request->variant_id;
                }
            } else {
                $wishlist->price = $product->usd_wholesale_price;
                if ($product->stock < $wishlist->quantity || $product->stock <= 0) {
                    $response = ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];
                    return response()->json($response);
                }
            }
            $wishlist->amount = $wishlist->price * $wishlist->quantity;
            $wishlist->type = $productType;
            $wishlist->save();
        }
        $response = ['res' => true, 'msg' => 'Product successfully added to cart', 'data' => ""];
        return response()->json($response);
    }

    public function delete(Request $request, $id)
    {
        $wishlist = Wishlist::find($id);
        if ($wishlist) {
            $wishlist->delete();
            $response = ['res' => true, 'msg' => 'Cart successfully removed', 'data' => ""];
            return response()->json($response);
        }
        $response = ['res' => false, 'msg' => 'Error please try again', 'data' => ""];
        return response()->json($response);
    }


    public function fetch(Request $request, $id)
    {
        $brandArr = [];
        $productArr = [];
        $user = User::find($id);
        if ($user) {
            $brand_arr = Wishlist::where('user_id', $id)->where('cart_id', null)->groupBy('brand_id')->get()->toArray();
        }
        if (!empty($brand_arr)) {
            foreach ($brand_arr as $brandk => $brandv) {
                $brand = Brand::where('user_id', $brandv['brand_id'])->first();
                $brandArr[$brandk]['brand_key'] = $brand->brand_key;
                $brandArr[$brandk]['brand_id'] = $brand->user_id;
                $brandArr[$brandk]['brand_name'] = $brand->brand_name;
                $brandArr[$brandk]['brand_logo'] = $brand->logo_image != '' ? asset('public') . '/' . $brand->logo_image : asset('public/img/logo-image.png');
                $brandArr[$brandk]['avg_lead_time'] = $brand->avg_lead_time;
                $brandArr[$brandk]['first_order_min'] = $brand->first_order_min;

                $country = Country::where('id', $brand->country)->first();
                $brandArr[$brandk]['country'] = $country->name;
                $headQuatered = Country::where('id', $brand->headquatered)->first();
                $brandArr[$brandk]['headquatered'] = $headQuatered->name;
                $productShipped = Country::where('id', $brand->product_shipped)->first();
                $brandArr[$brandk]['product_shipped'] = $productShipped->name;

                $prdctArr = Wishlist::where('user_id', $id)->where('cart_id', null)->where('brand_id', $brandv['brand_id'])->get()->toArray();
                if (!empty($prdctArr)) {
                    foreach ($prdctArr as $prdctK => $prdctV) {
                        $product = Products::find($prdctV['product_id']);
                        $productWholeSalePrice = $product->usd_wholesale_price;
                        $productRetailPrice = $product->usd_retail_price;
                        if($prdctV['variant_id']){
                            $variant = ProductVariation::find($prdctV['variant_id']);
                            $productWholeSalePrice = $variant->price;
                            $productRetailPrice = $variant->retail_price;
                        }
                        $brandArr[$brandk]['products'][$prdctK]['id'] = $prdctV['id'];
                        $brandArr[$brandk]['products'][$prdctK]['product_id'] = $product->id;
                        $brandArr[$brandk]['products'][$prdctK]['product_name'] = $product->name;
                        $brandArr[$brandk]['products'][$prdctK]['product_price'] = $prdctV['price'];
                        $brandArr[$brandk]['products'][$prdctK]['product_qty'] = $prdctV['quantity'];
                        $brandArr[$brandk]['products'][$prdctK]['style_name'] = $prdctV['style_name'];
                        $brandArr[$brandk]['products'][$prdctK]['style_group_name'] = $prdctV['style_group_name'];
                        $brandArr[$brandk]['products'][$prdctK]['type'] = $prdctV['type'];
                        $brandArr[$brandk]['products'][$prdctK]['product_image'] = $product->featured_image != '' ? $product->featured_image : asset('public/img/logo-image.png');

                        $productDet['id'] = $prdctV['id'];
                        $productDet['product_id'] = $product->id;
                        $productDet['product_name'] = $product->name;
                        $productDet['product_wholesale_price'] = $productWholeSalePrice;
                        $productDet['product_retail_price'] =  $productRetailPrice;
                        $productDet['product_qty'] = $prdctV['quantity'];
                        $productDet['style_name'] = $prdctV['style_name'];
                        $productDet['style_group_name'] = $prdctV['style_group_name'];
                        $productDet['type'] = $prdctV['type'];
                        $productDet['product_image'] = $product->featured_image != '' ? $product->featured_image : asset('public/img/logo-image.png');
                        $productArr[] = $productDet;
                    }
                }
            }
        }

        $data = array(
            'brand_arr' => $brandArr,
            'product_arr' => $productArr,
        );

        $response = ['res' => true, 'msg' => "", 'data' => $data];
        return response()->json($response);
    }
}
