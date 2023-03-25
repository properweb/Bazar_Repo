<?php

namespace Modules\Cart\Http\Services;

use Modules\Cart\Entities\Cart;
use Modules\Order\Entities\Order;
use Modules\Wishlist\Entities\Wishlist;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariation;
use Modules\Product\Entities\ProductPrepack;

class CartService
{
    protected Cart $cart;

    protected User $user;
    protected $product = null;
    protected $variant = null;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }


    /**
     * User can add product to his cart
     *
     * @param $request
     * @return array
     */
    public function add($request): array
    {

        $userId = auth()->user()->id;
        $product = Product::find($request->product_id);
        $productType = 'SINGLE_PRODUCT';
        $productName = $product->name;
        $productSKU = $product->sku;

        if (empty($product)) {
            return [
                'res' => false,
                'msg' => 'Invalid Product',
                'data' => ""
            ];
        }
        $variantId = '';
        $getVariant = $request->variant_id;
        $preID = $request->prepack_id;

        if (!empty($getVariant)) {
            $variant = ProductVariation::find($getVariant);
            $variantId = $variant->id;
            if (!empty($request->openSizingArray)) {
                $productType = 'OPEN_SIZING';
            }
            if (!empty($request->prepack_id)) {
                $productType = 'PREPACK';
                $variantId = $preID;

            }
        }
        if (!empty($request->prepack_id)) {
            $alreadyCart = Cart::where('user_id', $userId)->where('order_id', null)->where('product_id', $product->id)->where('reference', $getVariant)->where('type', $productType)->first();
        } else {
            if (!empty($getVariant) && !empty($variant)) {
                $alreadyCart = Cart::where('user_id', $userId)->where('order_id', null)->where('product_id', $product->id)->where('variant_id', $variantId)->where('type', $productType)->first();
            } else {
                $alreadyCart = Cart::where('user_id', $userId)->where('order_id', null)->where('product_id', $product->id)->where('type', $productType)->first();
            }
        }


        if ($alreadyCart) {

            $alreadyCart->quantity = $alreadyCart->quantity + (int)$request->quantity;
            $alreadyCart->amount = $product->price + $alreadyCart->amount;

            if (!empty($getVariant) && !empty($variant)) {
                if ($productType == 'OPEN_SIZING') {
                    $optionsArr = [];
                    $styleArr = [];
                    $styleGrpArr = [];
                    $priceArr = [];
                    $qtyArr = [];
                    $referenceArr = unserialize($alreadyCart->reference);
                    $sizeVariant = '';
                    if (!empty($request->openSizingArray)) {
                        foreach ($request->openSizingArray as $size) {
                            if ($variant->options1 == 'Size') {
                                $sizeVariant = ProductVariation::where('product_id', $product->id)->where('options1', "Size")->where('value1', $size['value'])->where('value2', $variant->value2)->where('value3', $variant->value3)->first();
                            }
                            if ($variant->options2 == 'Size') {
                                $sizeVariant = ProductVariation::where('product_id', $product->id)->where('options2', "Size")->where('value2', $size['value'])->where('value1', $variant->value1)->where('value3', $variant->value3)->first();
                            }
                            if ($variant->options3 == 'Size') {
                                $sizeVariant = ProductVariation::where('product_id', $product->id)->where('options3', "Size")->where('value3', $size['value'])->where('value2', $variant->value2)->where('value1', $variant->value1)->first();
                            }
                            $ord_qty = $referenceArr[$sizeVariant->id] ?? 0;
                            $new_qty = $ord_qty + $size['qty'];

                            if ($sizeVariant->stock < $new_qty || $sizeVariant->stock <= 0) {

                                return ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];
                            }
                            $optionsArr[$sizeVariant->id] = $new_qty;
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
                    $tPrice = 0;
                    foreach ($qtyArr as $qk => $qv) {
                        $tPrice += $priceArr[$qk] * $qv;
                    }
                    $alreadyCart->price = $tPrice;
                    $alreadyCart->style_name = rtrim(implode(',', $styleArr), ',');
                    $alreadyCart->style_group_name = rtrim(implode(',', $styleGrpArr), ',');
                    $alreadyCart->reference = serialize($optionsArr);
                }
                if ($productType == 'PREPACK') {
                    $pPackVariant = ProductPrepack::where('id', $preID)->first();
                    $alreadyCart->price = $pPackVariant->packs_price;
                    $alreadyCart->style_name = $pPackVariant->style;
                    $alreadyCart->style_group_name = $pPackVariant->size_ratio . ';' . $pPackVariant->size_range;
                    if ($variant->stock < $alreadyCart->quantity || $variant->stock <= 0) {

                        return ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];

                    }
                }
                if ($productType == 'SINGLE_PRODUCT') {
                    if ($variant->stock < $alreadyCart->quantity || $variant->stock <= 0) {

                        return ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];

                    }
                }
            } else {
                if ($product->stock < $alreadyCart->quantity || $product->stock <= 0) {

                    return ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];

                }
            }
            $alreadyCart->save();
        } else {
            $cart = new Cart;

            $cart->user_id = $userId;
            $cart->product_id = $product->id;
            $cart->brand_id = $product->user_id;
            $cart->product_name = $productName;
            $cart->product_sku = $productSKU;
            $cart->quantity = (int)$request->quantity;

            if (!empty($request->variant_id) && !empty($variant)) {
                $cart->price = $variant->price;
                if ($productType == 'OPEN_SIZING') {
                    $optionsArr = [];
                    $styleArr = [];
                    $styleGrpArr = [];
                    $priceArr = [];
                    $qtyArr = [];
                    $sizeVariant = '';
                    if (!empty($request->openSizingArray)) {
                        foreach ($request->openSizingArray as $size) {
                            if ($variant->options1 == 'Size') {
                                $sizeVariant = ProductVariation::where('product_id', $product->id)->where('options1', "Size")->where('value1', $size['value'])->where('value2', $variant->value2)->where('value3', $variant->value3)->first();
                            }
                            if ($variant->options2 == 'Size') {
                                $sizeVariant = ProductVariation::where('product_id', $product->id)->where('options2', "Size")->where('value2', $size['value'])->where('value1', $variant->value1)->where('value3', $variant->value3)->first();
                            }
                            if ($variant->options3 == 'Size') {
                                $sizeVariant = ProductVariation::where('product_id', $product->id)->where('options3', "Size")->where('value3', $size['value'])->where('value2', $variant->value2)->where('value1', $variant->value1)->first();
                            }
                            if (!empty($sizeVariant->stock) < $cart->quantity || $sizeVariant->stock <= 0) {
                                return [
                                    'res' => false,
                                    'msg' => 'Stock not sufficient!',
                                    'data' => ""
                                ];

                            }
                            $optionsArr[$sizeVariant->id] = $size['qty'];
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
                    $tPrice = 0;
                    foreach ($qtyArr as $qk => $qv) {
                        $tPrice += $priceArr[$qk] * $qv;
                    }
                    $cart->price = $tPrice;
                    $cart->style_name = rtrim(implode(',', $styleArr), ',');
                    $cart->style_group_name = rtrim(implode(',', $styleGrpArr), ',');
                    $cart->reference = serialize($optionsArr);
                    $cart->variant_id = $request->variant_id;
                }
                if ($productType == 'PREPACK') {
                    $pPackVariant = ProductPrepack::where('id', $preID)->first();
                    $cart->price = $pPackVariant->packs_price;
                    $cart->style_name = $pPackVariant->style;
                    $cart->style_group_name = $pPackVariant->size_ratio . ';' . $pPackVariant->size_range;
                    $cart->reference = $getVariant;
                    $cart->variant_id = $preID;

                }
                if ($productType == 'SINGLE_PRODUCT') {
                    if ($variant->stock < $cart->quantity || $variant->stock <= 0) {

                        return ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];
                    }
                    $styleArr = [];
                    $styleGrpArr = [];
                    $styleArr[] = $variant->value1;
                    $styleArr[] = $variant->value2;
                    $styleArr[] = $variant->value3;
                    $cart->style_name = rtrim(implode(',', $styleArr), ',');
                    $cart->style_group_name = rtrim(implode(',', $styleGrpArr), ',');
                    $cart->reference = $variant->id;
                    $cart->variant_id = $getVariant;
                }
            } else {
                $cart->price = $product->usd_wholesale_price;
                if ($product->stock < $cart->quantity || $product->stock <= 0) {

                    return ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];

                }
            }
            $cart->amount = $cart->price * $cart->quantity;
            $cart->type = $productType;
            $cart->save();
            Wishlist::where('user_id', $userId)->where('cart_id', null)->update(['cart_id' => $cart->id]);
        }

        return ['res' => true, 'msg' => 'Product successfully added to cart', 'data' => ""];
    }

    /**
     * User can view his cart
     *
     * @return array
     */
    public function fetch(): array
    {
        $cartCount = 0;
        $cartArr = [];
        $id = auth()->user()->id;
        $user = User::find($id);
        if ($user) {
            $cartCount = Cart::where('user_id', $id)->where('order_id', null)->sum('quantity');
            if ($cartCount > 0) {
                $brandArr = Cart::where('user_id', $id)->where('order_id', null)->groupBy('brand_id')->get()->toArray();
            }
        }
        if (!empty($brandArr)) {
            foreach ($brandArr as $brandK => $brandV) {
                $brand = Brand::where('user_id', $brandV['brand_id'])->first();
                $cartArr[$brandK]['brand_key'] = $brand->brand_key;
                $cartArr[$brandK]['brand_id'] = $brand->user_id;
                $cartArr[$brandK]['brand_name'] = $brand->brand_name;
                $cartArr[$brandK]['brand_logo'] = $brand->logo_image != '' ? asset('public') . '/' . $brand->logo_image : asset('public/img/logo-image.png');
                $alreadyOrdered = Order::where('brand_id', $brand->user_id)->where('user_id', $user->id)->count();
                $cartArr[$brandK]['brand_min'] = $alreadyOrdered > 0 ? $brand->re_order_min : $brand->first_order_min;
                $prdArr = Cart::where('user_id', $id)->where('order_id', null)->where('brand_id', $brandV['brand_id'])->get()->toArray();
                if (!empty($prdArr)) {
                    foreach ($prdArr as $prdK => $prdV) {
                        $product = Product::find($prdV['product_id']);
                        $cartArr[$brandK]['products'][$prdK]['id'] = $prdV['id'];
                        $cartArr[$brandK]['products'][$prdK]['product_id'] = $product->id;
                        $cartArr[$brandK]['products'][$prdK]['product_name'] = $product->name;
                        $cartArr[$brandK]['products'][$prdK]['product_price'] = $prdV['price'];
                        $cartArr[$brandK]['products'][$prdK]['product_qty'] = $prdV['quantity'];
                        $cartArr[$brandK]['products'][$prdK]['style_name'] = $prdV['style_name'];
                        $cartArr[$brandK]['products'][$prdK]['style_group_name'] = $prdV['style_group_name'];
                        $cartArr[$brandK]['products'][$prdK]['type'] = $prdV['type'];
                        $cartArr[$brandK]['products'][$prdK]['product_image'] = $product->featured_image != '' ? $product->featured_image : asset('public/img/logo-image.png');
                    }
                }
            }
        }

        $data = array(
            'cart_count' => $cartCount,
            'cart_arr' => $cartArr,
        );

        $response = ['res' => true, 'msg' => '', 'data' => $data];

        return ($response);
    }

    /**
     * User can delete product from his cart
     *
     * @param $id
     * @return array
     */
    public function delete($id): array
    {
        $cart = Cart::where('id', $id)->first();
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

    /**
     * Update existing cart
     *
     * @param $request
     * @return array
     */
    public function update($request): array
    {
        $carts = $request->cart;
        $error = 0;
        foreach ($carts as $v) {
            $cart = Cart::where('id', $v['id'])->first();

            if (!empty($cart->variant_id) && $cart->type === 'SINGLE_PRODUCT') {
                $variant = ProductVariation::where('id', $cart->variant_id)->first();
                if ($v['product_qty'] > $variant->stock) {
                    $error = 1;
                }

            } else if ($cart->type === 'PREPACK') {
                $variantPrePack = ProductVariation::where('id', $cart->reference)->first();
                if ($v['product_qty'] > $variantPrePack->stock) {
                    $error = 1;
                }
            } else {
                $product = Product::where('id', $cart->product_id)->first();
                if ($v['product_qty'] > $product->stock) {
                    $error = 1;
                }

            }
        }
        if ($error == 1) {
            return [
                'res' => false,
                'msg' => 'Stock is not sufficient',
                'data' => ""
            ];
        }
        foreach ($carts as $v) {
            Cart::where('id', $v['id'])->where('order_id', null)->update(['quantity' => $v['product_qty']]);
        }
        return [
            'res' => true,
            'msg' => '',
            'data' => ""
        ];
    }

}
