<?php

namespace Modules\Cart\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Cart\Entities\Cart;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Product\Entities\Products;
use Modules\Product\Entities\ProductVariation;
use Modules\Product\Entities\ProductPrepack;

class CartController extends Controller
{
    protected $product = null;
    protected $variant = null;

    public function __construct(Products $product)
    {
        $this->product = $product;
    }

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
        if ($request->variant_id && !empty($request->variant_id)) {
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
            $alreadyCart = Cart::where('user_id', $request->user_id)->where('order_id', null)->where('product_id', $product->id)->where('variant_id', $variantId)->where('type', $productType)->first();
        } else {
            $alreadyCart = Cart::where('user_id', $request->user_id)->where('order_id', null)->where('product_id', $product->id)->where('type', $productType)->first();
        }

        if ($alreadyCart) {

            $alreadyCart->quantity = $alreadyCart->quantity + (int)$request->quantity;
            $alreadyCart->amount = $product->price + $alreadyCart->amount;

            if (!empty($request->variant_id) && $variant) {
                if ($productType == 'OPEN_SIZING') {
                    $options_arr = [];
                    $style_arr = [];
                    $style_grp_arr = [];
                    $size_arr = [];
                    $price_arr = [];
                    $qty_arr = [];
                    $reference_arr = unserialize($alreadyCart->reference);

                    if (!empty($request->openSizingArray)) {
                        foreach ($request->openSizingArray as $size) {
                            if ($variant->options1 == 'Size') {
                                $size_variant = ProductVariation::where('product_id', $product->id)->where('options1', "Size")->where('value1', $size['value'])->where('value2', $variant->value2)->where('value3', $variant->value3)->first();
                            }
                            if ($variant->options2 == 'Size') {
                                $size_variant = ProductVariation::where('product_id', $product->id)->where('options2', "Size")->where('value2', $size['value'])->where('value1', $variant->value1)->where('value3', $variant->value3)->first();
                            }
                            if ($variant->options3 == 'Size') {
                                $size_variant = ProductVariation::where('product_id', $product->id)->where('options3', "Size")->where('value3', $size['value'])->where('value2', $variant->value2)->where('value1', $variant->value1)->first();
                            }
                            $ord_qty = isset($reference_arr[$size_variant->id]) ? $reference_arr[$size_variant->id] : 0;
                            $new_qty = $ord_qty + $size['qty'];

                            if ($size_variant->stock < $new_qty || $size_variant->stock <= 0) {
                                $response = ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];
                                return response()->json($response);
                            }
                            $options_arr[$size_variant->id] = $new_qty;
                            $size_arr[] = $size['value'];
                            $style_grp_arr[] = "(" . $new_qty . ")" . $size['value'];
                            $price_arr[] = $size_variant->price;
                            $qty_arr[] = $new_qty;
                        }
                    }
                    if ($variant->options1 == 'Size') {
                        $style_arr[] = $variant->value2;
                        $style_arr[] = $variant->value3;
                    }
                    if ($variant->options2 == 'Size') {
                        $style_arr[] = $variant->value1;
                        $style_arr[] = $variant->value3;
                    }
                    if ($variant->options3 == 'Size') {
                        $style_arr[] = $variant->value2;
                        $style_arr[] = $variant->value1;
                    }
                    $tprice = 0;
                    foreach ($qty_arr as $qk => $qv) {
                        $tprice += $price_arr[$qk] * $qv;
                    }
                    $alreadyCart->price = $tprice;
                    $alreadyCart->style_name = rtrim(implode(',', $style_arr), ',');
                    $alreadyCart->style_group_name = rtrim(implode(',', $style_grp_arr), ',');
                    $alreadyCart->reference = serialize($options_arr);
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
            $cart = new Cart;

            $cart->user_id = $request->user_id;
            $cart->product_id = $product->id;
            $cart->brand_id = $product->user_id;
            $cart->product_name = $productName;
            $cart->product_sku = $productSKU;

            $cart->quantity = (int)$request->quantity;

            if (!empty($request->variant_id) && $variant) {
                $cart->price = $variant->price;
                if ($productType == 'OPEN_SIZING') {
                    $options_arr = [];
                    $style_arr = [];
                    $style_grp_arr = [];
                    $size_arr = [];
                    $price_arr = [];
                    $qty_arr = [];

                    if (!empty($request->openSizingArray)) {
                        foreach ($request->openSizingArray as $size) {
                            if ($variant->options1 == 'Size') {
                                $size_variant = ProductVariation::where('product_id', $product->id)->where('options1', "Size")->where('value1', $size['value'])->where('value2', $variant->value2)->where('value3', $variant->value3)->first();
                            }
                            if ($variant->options2 == 'Size') {
                                $size_variant = ProductVariation::where('product_id', $product->id)->where('options2', "Size")->where('value2', $size['value'])->where('value1', $variant->value1)->where('value3', $variant->value3)->first();
                            }
                            if ($variant->options3 == 'Size') {
                                $size_variant = ProductVariation::where('product_id', $product->id)->where('options3', "Size")->where('value3', $size['value'])->where('value2', $variant->value2)->where('value1', $variant->value1)->first();
                            }

                            if ($size_variant->stock < $cart->quantity || $size_variant->stock <= 0) {
                                $response = ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];
                                return response()->json($response);
                            }
                            $options_arr[$size_variant->id] = $size['qty'];
                            $size_arr[] = $size['value'];
                            $style_grp_arr[] = "(" . $size['qty'] . ")" . $size['value'];
                            $price_arr[] = $size_variant->price;
                            $qty_arr[] = $size['qty'];
                        }
                    }
                    if ($variant->options1 == 'Size') {
                        $style_arr[] = $variant->value2;
                        $style_arr[] = $variant->value3;
                    }
                    if ($variant->options2 == 'Size') {
                        $style_arr[] = $variant->value1;
                        $style_arr[] = $variant->value3;
                    }
                    if ($variant->options3 == 'Size') {
                        $style_arr[] = $variant->value2;
                        $style_arr[] = $variant->value1;
                    }
                    $tprice = 0;
                    foreach ($qty_arr as $qk => $qv) {
                        $tprice += $price_arr[$qk] * $qv;
                    }
                    $cart->price = $tprice;
                    $cart->style_name = rtrim(implode(',', $style_arr), ',');
                    $cart->style_group_name = rtrim(implode(',', $style_grp_arr), ',');
                    $cart->reference = serialize($options_arr);
                    $cart->variant_id = $request->variant_id;
                }
                if ($productType == 'PREPACK') {
                    $prepackVariant = ProductPrepack::where('id', $request->prepack_id)->first();
                    $cart->price = $prepackVariant->packs_price;
                    $cart->style_name = $prepackVariant->style;
                    $cart->style_group_name = $prepackVariant->size_ratio . ';' . $prepackVariant->size_range;
                    $cart->reference = $request->variant_id;
                    $cart->variant_id = $request->prepack_id;
                }
                if ($productType == 'SINGLE_PRODUCT') {
                    if ($variant->stock < $cart->quantity || $variant->stock <= 0) {
                        $response = ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];
                        return response()->json($response);
                    }
                    $style_arr = [];
                    $style_grp_arr = [];
                    $style_arr[] = $variant->value1;
                    $style_arr[] = $variant->value2;
                    $style_arr[] = $variant->value3;
                    $cart->style_name = rtrim(implode(',', $style_arr), ',');
                    $cart->style_group_name = rtrim(implode(',', $style_grp_arr), ',');
                    $cart->reference = $variant->id;
                    $cart->variant_id = $request->variant_id;
                }
            } else {
                $cart->price = $product->usd_wholesale_price;
                if ($product->stock < $cart->quantity || $product->stock <= 0) {
                    $response = ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];
                    return response()->json($response);
                }
            }
            $cart->amount = $cart->price * $cart->quantity;
            $cart->type = $productType;
            $cart->save();

        }
        $response = ['res' => true, 'msg' => 'Product successfully added to cart', 'data' => ""];
        return response()->json($response);
    }

    public function delete(Request $request, $id)
    {
        $cart = Cart::find($id);
        if ($cart) {
            $cart->delete();
            $response = ['res' => true, 'msg' => 'Cart successfully removed', 'data' => ""];
            return response()->json($response);
        }
        $response = ['res' => false, 'msg' => 'Error please try again', 'data' => ""];
        return response()->json($response);
    }

    public function update(Request $request)
    {
        // dd($request->all());
        if ($request->quant) {
            $error = array();
            $success = '';

            foreach ($request->quant as $k => $quant) {

                $id = $request->qty_id[$k];

                $cart = Cart::find($id);

                if ($quant > 0 && $cart) {

                    if ($cart->product->stock < $quant) {
                        request()->session()->flash('error', 'Out of stock');
                        return back();
                    }
                    $cart->quantity = ($cart->product->stock > $quant) ? $quant : $cart->product->stock;

                    if ($cart->product->stock <= 0)
                        continue;
                    $after_price = ($cart->product->price - ($cart->product->price * $cart->product->discount) / 100);
                    $cart->amount = $after_price * $quant;

                    $cart->save();
                    $success = 'Cart successfully updated!';
                } else {
                    $error[] = 'Cart Invalid!';
                }
            }
            return back()->with($error)->with('success', $success);
        } else {
            return back()->with('Cart Invalid!');
        }
    }

    public function fetch(Request $request, $id)
    {
        $cartCount = 0;
        $cart_arr = [];
        $user = User::find($id);
        if ($user) {
            $cartCount = Cart::where('user_id', $id)->where('order_id', null)->sum('quantity');
            if ($cartCount > 0) {
                $brand_arr = Cart::where('user_id', $id)->where('order_id', null)->groupBy('brand_id')->get()->toArray();
            }
        }
        if (!empty($brand_arr)) {
            foreach ($brand_arr as $brandk => $brandv) {
                $brand = Brand::where('user_id', $brandv['brand_id'])->first();
                $cart_arr[$brandk]['brand_key'] = $brand->brand_key;
                $cart_arr[$brandk]['brand_id'] = $brand->user_id;
                $cart_arr[$brandk]['brand_name'] = $brand->brand_name;
                $cart_arr[$brandk]['brand_logo'] = $brand->logo_image != '' ? asset('public') . '/' . $brand->logo_image : asset('public/img/logo-image.png');
                $prdct_arr = Cart::where('user_id', $id)->where('order_id', null)->where('brand_id', $brandv['brand_id'])->get()->toArray();
                if (!empty($prdct_arr)) {
                    foreach ($prdct_arr as $prdctk => $prdctv) {
                        $product = Products::find($prdctv['product_id']);
                        $cart_arr[$brandk]['products'][$prdctk]['id'] = $prdctv['id'];
                        $cart_arr[$brandk]['products'][$prdctk]['product_id'] = $product->id;
                        $cart_arr[$brandk]['products'][$prdctk]['product_name'] = $product->name;
                        $cart_arr[$brandk]['products'][$prdctk]['product_price'] = $prdctv['price'];
                        $cart_arr[$brandk]['products'][$prdctk]['product_qty'] = $prdctv['quantity'];
                        $cart_arr[$brandk]['products'][$prdctk]['style_name'] = $prdctv['style_name'];
                        $cart_arr[$brandk]['products'][$prdctk]['style_group_name'] = $prdctv['style_group_name'];
                        $cart_arr[$brandk]['products'][$prdctk]['type'] = $prdctv['type'];
                        $cart_arr[$brandk]['products'][$prdctk]['product_image'] = $product->featured_image != '' ? $product->featured_image : asset('public/img/logo-image.png');
                    }
                }
            }
        }

        $data = array(
            'cart_count' => $cartCount,
            'cart_arr' => $cart_arr,
        );

        $response = ['res' => true, 'msg' => "", 'data' => $data];
        return response()->json($response);
    }

}
