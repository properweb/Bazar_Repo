<?php

namespace Modules\Wishlist\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Wishlist\Entities\Wishlist;
use Modules\Wishlist\Entities\Board;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Product\Entities\Products;
use Modules\Product\Entities\ProductVariation;
use Modules\Product\Entities\ProductPrepack;
use Modules\Country\Entities\Country;

class WishlistController extends Controller {

    public function add(Request $request) {
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
            $alreadyWished = Wishlist::where('user_id', $request->user_id)->where('cart_id', null)->where('product_id', $product->id)->where('variant_id', $variantId)->where('type', $productType)->first();
        } else {
            $alreadyWished = Wishlist::where('user_id', $request->user_id)->where('cart_id', null)->where('product_id', $product->id)->where('type', $productType)->first();
        }
        if ($alreadyWished) {
            $alreadyWished->quantity = $alreadyWished->quantity + (int) $request->quantity;
            $alreadyWished->amount = $product->price + $alreadyWished->amount;
            if (!empty($request->variant_id) && $variant) {
                if ($productType == 'OPEN_SIZING') {
                    $optionsArr = [];
                    $styleArr = [];
                    $styleGrpArr = [];
                    $sizeArr = [];
                    $priceArr = [];
                    $qtyArr = [];
                    $reference_arr = unserialize($alreadyWished->reference);

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
                    $alreadyWished->price = $tprice;
                    $alreadyWished->style_name = rtrim(implode(',', $styleArr), ',');
                    $alreadyWished->style_group_name = rtrim(implode(',', $styleGrpArr), ',');
                    $alreadyWished->reference = serialize($optionsArr);
                }
                if ($productType == 'PREPACK') {
                    $prepackVariant = ProductPrepack::where('id', $request->prepack_id)->first();
                    $alreadyWished->price = $prepackVariant->packs_price;
                    $alreadyWished->style_name = $prepackVariant->style;
                    $alreadyWished->style_group_name = $prepackVariant->size_ratio . ';' . $prepackVariant->size_range;
                }
                if ($productType == 'SINGLE_PRODUCT') {
                    if ($variant->stock < $alreadyWished->quantity || $variant->stock <= 0) {
                        $response = ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];
                        return response()->json($response);
                    }
                }
            } else {
                if ($variant->stock < $alreadyWished->quantity || $variant->stock <= 0) {
                    $response = ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];
                    return response()->json($response);
                }
            }
            $alreadyWished->save();
        } else {
            $boardCount = Board::where('user_id', $request->user_id)->get()->count();
            if ($boardCount == 0) {
                $newBoard = new Board;
                $newBoard->user_id = $request->user_id;
                $newBoard->board_key = 'rb_' . Str::lower(Str::random(10));
                $newBoard->name = 'Saved Products';
                $newBoard->visibility = '1';
                $newBoard->save();
            }
            $wishlist = new Wishlist;
            $board = Board::where('user_id', $request->user_id)->orderBy('id', 'desc')->first();
            $wishlist->board_id = $board->id;
            $wishlist->user_id = $request->user_id;
            $wishlist->product_id = $product->id;
            $wishlist->brand_id = $product->user_id;
            $wishlist->product_name = $productName;
            $wishlist->product_sku = $productSKU;
            $wishlist->quantity = (int) $request->quantity;

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

    public function delete(Request $request) {
        $wishlist = Wishlist::find($request->id);
        if ($wishlist) {
            $wishlist->delete();
            $response = ['res' => true, 'msg' => 'Cart successfully removed', 'data' => ""];
            return response()->json($response);
        }
        $response = ['res' => false, 'msg' => 'Error please try again', 'data' => ""];
        return response()->json($response);
    }

    public function fetch(Request $request, $id) {
        $boardArr = [];
        $brandArr = [];
        $productArr = [];
        $user = User::find($id);
        if ($user) {
            $wishesByBrand = Wishlist::where('user_id', $id)->where('cart_id', null)->groupBy('brand_id')->get()->toArray();
            $wishesByBoard = Board::where('user_id', $id)->orderBy('updated_at', 'desc')->get();
        }
        if ($wishesByBrand) {
            foreach ($wishesByBrand as $brandk => $brandv) {
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
                        if ($prdctV['variant_id']) {
                            $variant = ProductVariation::find($prdctV['variant_id']);
                            $productWholeSalePrice = $variant->price;
                            $productRetailPrice = $variant->retail_price;
                        }
                        $brandArr[$brandk]['products'][$prdctK]['id'] = $prdctV['id'];
                        $brandArr[$brandk]['products'][$prdctK]['product_id'] = $product->id;
                        $brandArr[$brandk]['products'][$prdctK]['product_name'] = $product->name;
                        $brandArr[$brandk]['products'][$prdctK]['product_wholesale_price'] = $productWholeSalePrice;
                        $brandArr[$brandk]['products'][$prdctK]['product_retail_price'] = $productRetailPrice;
                        $brandArr[$brandk]['products'][$prdctK]['product_qty'] = $prdctV['quantity'];
                        $brandArr[$brandk]['products'][$prdctK]['style_name'] = $prdctV['style_name'];
                        $brandArr[$brandk]['products'][$prdctK]['style_group_name'] = $prdctV['style_group_name'];
                        $brandArr[$brandk]['products'][$prdctK]['type'] = $prdctV['type'];
                        $brandArr[$brandk]['products'][$prdctK]['product_image'] = $product->featured_image != '' ? $product->featured_image : asset('public/img/logo-image.png');

                        $productDet['id'] = $prdctV['id'];
                        $productDet['product_id'] = $product->id;
                        $productDet['product_name'] = $product->name;
                        $productDet['product_wholesale_price'] = $productWholeSalePrice;
                        $productDet['product_retail_price'] = $productRetailPrice;
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
        if ($wishesByBoard) {
            foreach ($wishesByBoard as $boardk => $boardv) {
                $wishesByBoard = Wishlist::where('board_id', $boardv->id)->where('cart_id', null)->groupBy('board_id')->first();
                $wishesByBoardCount = Wishlist::where('board_id', $boardv->id)->where('cart_id', null)->get()->count();
                $productImage = '';
                if ($wishesByBoard && $wishesByBoardCount > 0) {
                    $productDetails = Products::find($wishesByBoard->product_id);
                    $productImage = $productDetails->featured_image;
                }
                $board = Board::find($boardv->id);
                if ($board) {
                    $boardArr[$boardk]['board_key'] = $board->board_key;
                    $boardArr[$boardk]['board_id'] = $board->id;
                    $boardArr[$boardk]['board_name'] = $board->name;
                    $boardArr[$boardk]['board_visibility'] = $board->visibility;
                    $boardArr[$boardk]['board_image'] = $productImage;
                    $boardArr[$boardk]['products_count'] = $wishesByBoardCount;
                }
            }
        }

        $data = array(
            'board_arr' => $boardArr,
            'brand_arr' => $brandArr,
            'product_arr' => $productArr,
        );

        $response = ['res' => true, 'msg' => "", 'data' => $data];
        return response()->json($response);
    }

    public function fetchBoards(Request $request, $id) {
        $boardArr = [];
        $user = User::find($id);
        if ($user) {
            $wishesByBoard = Board::where('user_id', $id)->orderBy('updated_at', 'desc')->get();
        }
        if ($wishesByBoard) {
            foreach ($wishesByBoard as $boardk => $boardv) {
                $wishesByBoard = Wishlist::where('board_id', $boardv->id)->where('cart_id', null)->groupBy('board_id')->first();
                $wishesByBoardCount = Wishlist::where('board_id', $boardv->id)->where('cart_id', null)->get()->count();
                $productImage = '';
                if ($wishesByBoard && $wishesByBoardCount > 0) {
                    $productDetails = Products::find($wishesByBoard->product_id);
                    $productImage = $productDetails->featured_image;
                }
                $board = Board::find($boardv->id);
                if ($board) {
                    $boardArr[$boardk]['board_key'] = $board->board_key;
                    $boardArr[$boardk]['board_id'] = $board->id;
                    $boardArr[$boardk]['board_name'] = $board->name;
                    $boardArr[$boardk]['board_visibility'] = $board->visibility;
                    $boardArr[$boardk]['board_image'] = $productImage;
                    $boardArr[$boardk]['products_count'] = $wishesByBoardCount;
                }
            }
        }

        $data = $boardArr;

        $response = ['res' => true, 'msg' => "", 'data' => $data];
        return response()->json($response);
    }

    public function fetchBoard(Request $request, $key) {
        $boardArr = [];
        $productArr = [];
        $board = Board::where('board_key', $key)->first();
        if ($board) {
            $wishesByBoard = Wishlist::where('board_id', $board->id)->where('cart_id', null)->get();
            if ($wishesByBoard) {
                foreach ($wishesByBoard as $wish) {
                    $product = Products::find($wish->product_id);
                    $productWholeSalePrice = $product->usd_wholesale_price;
                    $productRetailPrice = $product->usd_retail_price;
                    if ($wish->variant_id) {
                        $variant = ProductVariation::find($wish->variant_id);
                        $productWholeSalePrice = $variant->price;
                        $productRetailPrice = $variant->retail_price;
                    }
                    $productDet['id'] = $wish->id;
                    $productDet['product_id'] = $product->id;
                    $productDet['product_name'] = $product->name;
                    $productDet['product_wholesale_price'] = $productWholeSalePrice;
                    $productDet['product_retail_price'] = $productRetailPrice;
                    $productDet['product_qty'] = $wish->quantity;
                    $productDet['style_name'] = $wish->style_name;
                    $productDet['style_group_name'] = $wish->style_group_name;
                    $productDet['type'] = $wish->type;
                    $productDet['product_image'] = $product->featured_image != '' ? $product->featured_image : asset('public/img/logo-image.png');
                    $productArr[] = $productDet;
                }
            }
            $wishesByBrandCount = Wishlist::where('board_id', $board->id)->where('cart_id', null)->get()->count();
            $board->products_count = $wishesByBrandCount;
            $boardArr = $board;
        }
        $data = array(
            'board_arr' => $boardArr,
            'product_arr' => $productArr,
        );
        $response = ['res' => true, 'msg' => "", 'data' => $data];
        return response()->json($response);
    }

    public function addBoard(Request $request) {
        $validator = Validator::make($request->all(), [
                    'name' => 'required',
        ]);
        if ($validator->fails()) {
            $response = ['res' => false, 'msg' => $validator->errors()->first(), 'data' => ""];
        } else {
            $newBoard = new Board;
            $newBoard->user_id = $request->user_id;
            $newBoard->board_key = 'rb_' . Str::lower(Str::random(10));
            $newBoard->name = $request->name;
            $newBoard->visibility = $request->visibility;
            $status = $newBoard->save();
            if ($status) {
                $response = ['res' => true, 'msg' => "Successfully added your board", 'data' => ''];
            } else {
                $response = ['res' => false, 'msg' => "Please try again!", 'data' => ''];
            }
        }

        return response()->json($response);
    }

    public function updateBoard(Request $request) {
        $validator = Validator::make($request->all(), [
                    'name' => 'required',
        ]);
        if ($validator->fails()) {
            $response = ['res' => false, 'msg' => $validator->errors()->first(), 'data' => ""];
        } else {
            $board = Board::where('board_key', $request->key)->first();
            $board->name = $request->name;
            $board->visibility = $request->visibility;
            $status = $board->save();
            if ($status) {
                $response = ['res' => true, 'msg' => "Successfully updated your board", 'data' => ''];
            } else {
                $response = ['res' => false, 'msg' => "Please try again!", 'data' => ''];
            }
        }

        return response()->json($response);
    }

    public function deleteBoard(Request $request) {
        $board = Board::where('board_key', $request->key)->first();
        if ($board) {
            $wishesByBoard = Wishlist::where('board_id', $board->id)->where('cart_id', null)->get();
            if ($wishesByBoard) {
                foreach ($wishesByBoard as $wish) {
                    Wishlist::where('id', $wish->id)->delete();
                }
            }
            Board::where('id', $board->id)->delete();
            $response = ['res' => true, 'msg' => 'Board successfully removed', 'data' => ""];
            return response()->json($response);
        }
        $response = ['res' => false, 'msg' => 'Error please try again', 'data' => ""];
        return response()->json($response);
    }

    public function changeBoard(Request $request) {
        $whishlist = Wishlist::find($request->wish_id);
        if ($whishlist) {
            $board = Board::where('board_key', $request->board_key)->first();
            $whishlist->board_id = $board->id;
            $status = $whishlist->save();
            if ($status) {
                $response = ['res' => true, 'msg' => 'Product successfully updated', 'data' => ""];
                return response()->json($response);
            } else {
                $response = ['res' => false, 'msg' => "Please try again!", 'data' => ''];
                return response()->json($response);
            }
        }
        $response = ['res' => false, 'msg' => 'Error please try again', 'data' => ""];
        return response()->json($response);
    }

}
