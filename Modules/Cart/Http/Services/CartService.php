<?php

namespace Modules\Cart\Http\Services;

use Modules\Cart\Entities\Cart;
use Modules\Order\Entities\Order;
use Modules\Wishlist\Entities\Wishlist;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Product\Entities\Products;
use Modules\Product\Entities\ProductVariation;
use Modules\Product\Entities\ProductPrepack;

class CartService
{
    protected Cart $cart;

    protected User $user;
    protected $product = null;
    protected $variant = null;

    public function __construct(Products $product)
    {
        $this->product = $product;
    }


    /**
     * @param array $request
     * @return array
     */
    public function add(array $request): array
    {

        if (empty($request['product_id'])) {

            return [
                'res' => false,
                'msg' => 'Invalid Product',
                'data' => ""
            ];
        }
        $product = Products::find($request['product_id']);
        $productType = 'SINGLE_PRODUCT';
        $productName = $product->name;
        $productSKU = $product->sku;

        if (empty($product)) {
            $response = ['res' => false, 'msg' => 'Invalid Product', 'data' => ""];
            return ($response);
        }
        if ($request['variant_id'] && !empty($request['variant_id'])) {
            $variant = ProductVariation::find($request['variant_id']);
            $variantId = $variant->id;
            if (!empty($request['openSizingArray'])) {
                $productType = 'OPEN_SIZING';
            }
            if (!empty($request['prepack_id'])) {
                $productType = 'PREPACK';
                $variantId = $request['prepack_id'];
            }
        }

        if (!empty($request['variant_id']) && $variant) {
            $alreadyCart = Cart::where('user_id', $request['user_id'])->where('order_id', null)->where('product_id', $product->id)->where('variant_id', $variantId)->where('type', $productType)->first();
        } else {
            $alreadyCart = Cart::where('user_id', $request['user_id'])->where('order_id', null)->where('product_id', $product->id)->where('type', $productType)->first();
        }
        // return $alreadyCart;
        if ($alreadyCart) {
            // dd($alreadyCart);
            $alreadyCart->quantity = $alreadyCart->quantity + (int)$request['quantity'];
            $alreadyCart->amount = $product->price + $alreadyCart->amount;
            // return $alreadyCart->quantity;
            if (!empty($request['variant_id']) && $variant) {
                if ($productType == 'OPEN_SIZING') {
                    $optionsArr = [];
                    $styleArr = [];
                    $styleGrpArr = [];
                    $sizeArr = [];
                    $priceArr = [];
                    $qtyArr = [];
                    $referenceArr = unserialize($alreadyCart->reference);

                    if (!empty($request['openSizingArray'])) {
                        foreach ($request['openSizingArray'] as $size) {
                            if ($variant->options1 == 'Size') {
                                $sizeVariant = ProductVariation::where('product_id', $product->id)->where('options1', "Size")->where('value1', $size['value'])->where('value2', $variant->value2)->where('value3', $variant->value3)->first();
                            }
                            if ($variant->options2 == 'Size') {
                                $sizeVariant = ProductVariation::where('product_id', $product->id)->where('options2', "Size")->where('value2', $size['value'])->where('value1', $variant->value1)->where('value3', $variant->value3)->first();
                            }
                            if ($variant->options3 == 'Size') {
                                $sizeVariant = ProductVariation::where('product_id', $product->id)->where('options3', "Size")->where('value3', $size['value'])->where('value2', $variant->value2)->where('value1', $variant->value1)->first();
                            }
                            $ord_qty = isset($referenceArr[$sizeVariant->id]) ? $referenceArr[$sizeVariant->id] : 0;
                            $new_qty = $ord_qty + $size['qty'];

                            if ($sizeVariant->stock < $new_qty || $sizeVariant->stock <= 0) {
                                $response = ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];
                                return ($response);
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
                    $prepackVariant = ProductPrepack::where('id', $request['prepack_id'])->first();
                    $alreadyCart->price = $prepackVariant->packs_price;
                    $alreadyCart->style_name = $prepackVariant->style;
                    $alreadyCart->style_group_name = $prepackVariant->size_ratio . ';' . $prepackVariant->size_range;
                }
                if ($productType == 'SINGLE_PRODUCT') {
                    if ($variant->stock < $alreadyCart->quantity || $variant->stock <= 0) {
                        $response = ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];
                        return ($response);
                    }
                }
            } else {
                if ($product->stock < $alreadyCart->quantity || $product->stock <= 0) {
                    $response = ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];
                    return ($response);
                }
            }
            $alreadyCart->save();
        } else {
            $cart = new Cart;

            $cart->user_id = $request['user_id'];
            $cart->product_id = $product->id;
            $cart->brand_id = $product->user_id;
            $cart->product_name = $productName;
            $cart->product_sku = $productSKU;
            $cart->quantity = (int)$request['quantity'];

            if (!empty($request['variant_id']) && $variant) {
                $cart->price = $variant->price;
                if ($productType == 'OPEN_SIZING') {
                    $optionsArr = [];
                    $styleArr = [];
                    $styleGrpArr = [];
                    $sizeArr = [];
                    $priceArr = [];
                    $qtyArr = [];

                    if (!empty($request['openSizingArray'])) {
                        foreach ($request['openSizingArray'] as $size) {
                            if ($variant->options1 == 'Size') {
                                $sizeVariant = ProductVariation::where('product_id', $product->id)->where('options1', "Size")->where('value1', $size['value'])->where('value2', $variant->value2)->where('value3', $variant->value3)->first();
                            }
                            if ($variant->options2 == 'Size') {
                                $sizeVariant = ProductVariation::where('product_id', $product->id)->where('options2', "Size")->where('value2', $size['value'])->where('value1', $variant->value1)->where('value3', $variant->value3)->first();
                            }
                            if ($variant->options3 == 'Size') {
                                $sizeVariant = ProductVariation::where('product_id', $product->id)->where('options3', "Size")->where('value3', $size['value'])->where('value2', $variant->value2)->where('value1', $variant->value1)->first();
                            }
                            if ($sizeVariant->stock < $cart->quantity || $sizeVariant->stock <= 0) {
                                $response = ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];
                                return ($response);
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
                    $cart->price = $tprice;
                    $cart->style_name = rtrim(implode(',', $styleArr), ',');
                    $cart->style_group_name = rtrim(implode(',', $styleGrpArr), ',');
                    $cart->reference = serialize($optionsArr);
                    $cart->variant_id = $request['variant_id'];
                }
                if ($productType == 'PREPACK') {
                    $prepackVariant = ProductPrepack::where('id', $request['prepack_id'])->first();
                    $cart->price = $prepackVariant->packs_price;
                    $cart->style_name = $prepackVariant->style;
                    $cart->style_group_name = $prepackVariant->size_ratio . ';' . $prepackVariant->size_range;
                    $cart->reference = $request['variant_id'];
                    $cart->variant_id = $request['prepack_id'];
                }
                if ($productType == 'SINGLE_PRODUCT') {
                    if ($variant->stock < $cart->quantity || $variant->stock <= 0) {
                        $response = ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];
                        return ($response);
                    }
                    $styleArr = [];
                    $styleGrpArr = [];
                    $styleArr[] = $variant->value1;
                    $styleArr[] = $variant->value2;
                    $styleArr[] = $variant->value3;
                    $cart->style_name = rtrim(implode(',', $styleArr), ',');
                    $cart->style_group_name = rtrim(implode(',', $styleGrpArr), ',');
                    $cart->reference = $variant->id;
                    $cart->variant_id = $request['variant_id'];
                }
            } else {
                $cart->price = $product->usd_wholesale_price;
                if ($product->stock < $cart->quantity || $product->stock <= 0) {
                    $response = ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];
                    return ($response);
                }
            }
            $cart->amount = $cart->price * $cart->quantity;
            $cart->type = $productType;
            $cart->save();
            $wishlist = Wishlist::where('user_id', $request['user_id'])->where('cart_id', null)->update(['cart_id' => $cart->id]);
        }
        $response = ['res' => true, 'msg' => 'Product successfully added to cart', 'data' => ""];
        return ($response);

    }

    /**
     * @param $requestData
     * @return array
     */

    public function fetch($requestData): array
    {


        $cartCount = 0;
        $cart_arr = [];
        $id = $requestData->id;
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
                $alreadyOrdered = Order::where('brand_id', $brand->user_id)->where('user_id', $user->id)->count();
                $cart_arr[$brandk]['brand_min'] = $alreadyOrdered > 0? $brand->re_order_min : $brand->first_order_min;
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

        $response = ['res' => true, 'msg' => '', 'data' => $data];
        return ($response);
    }


    /**
     * @param array $cartData
     * @return array
     */

    public function update(array $cartData): array
    {
        //update Program
        $id = $cartData['id'];
        $name = $cartData['name'];
        $country = $cartData['country'];
        $street = $cartData['street'];
        $suite = $cartData['suite'];
        $state = $cartData['state'];
        $town = $cartData['town'];
        $zip = $cartData['zip'];
        $phoneCode = $cartData['phoneCode'];
        $data = array(
            'name' => $name,
            'country' => $country,
            'street' => $street,
            'suite' => $suite,
            'state' => $state,
            'town' => $town,
            'zip' => $zip,
            'phoneCode' => $phoneCode
        );
        Cart::where('id', $id)->update($data);

        $response = [
            'res' => true,
            'msg' => 'Updated Successfully',
            'data' => ''
        ];

        return $response;
    }

    /**
     * @param $requestData
     * @return array
     */
    public function delete($id, $user_id): array
    {
        $cart = Cart::where('id', $id)->where('user_id', $user_id)->first();
        if (empty($cart)) {
            return [
                'res' => false,
                'msg' => 'Error please try again',
                'data' => ""
            ];
        }
        $cart->delete();
        return [
            'res' => true,
            'msg' => 'Cart successfully removed',
            'data' => ""
        ];
    }

}
