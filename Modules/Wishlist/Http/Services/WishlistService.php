<?php

namespace Modules\Wishlist\Http\Services;


use Illuminate\Support\Str;
use Modules\Cart\Entities\Cart;
use Modules\Wishlist\Entities\Wishlist;
use Modules\Wishlist\Entities\Board;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariation;
use Modules\Product\Entities\ProductPrepack;
use Modules\Country\Entities\Country;


class WishlistService
{
    protected Wishlist $wishlist;

    protected User $user;

    /**
     * Add wishlist By Logged User
     *
     * @param $request
     * @return array
     */
    public function add($request): array
    {

        if (empty($request->product_id)) {

            return ['res' => false, 'msg' => 'Invalid Product', 'data' => ""];
        }
        $user_id = auth()->user()->id;
        $product = Product::find($request->product_id);
        $productType = 'SINGLE_PRODUCT';
        $productName = $product->name;
        $productSKU = $product->sku;

        if (empty($product)) {

            return ['res' => false, 'msg' => 'Invalid Product', 'data' => ""];
        }
        $variant = '';

        if (!empty($request->variant_id)) {
            $variant = ProductVariation::find($request->variant_id);

            if (!empty($request->openSizingArray)) {
                $productType = 'OPEN_SIZING';
            }
            if (!empty($request->prepack_id)) {
                $productType = 'PREPACK';

            }
        }

        $alreadyWished = Wishlist::where('user_id', $user_id)->where('cart_id', null)->where('product_id', $product->id)->first();


        if ($alreadyWished) {

            return ['res' => false, 'msg' => 'Product is already in your wish list', 'data' => ""];

        } else {
            $boardCount = Board::where('user_id', $user_id)->get()->count();
            if ($boardCount === 0) {
                $newBoard = new Board;
                $newBoard->user_id = $user_id;
                $newBoard->board_key = 'rb_' . Str::lower(Str::random(10));
                $newBoard->name = 'Saved Products';
                $newBoard->visibility = '1';
                $newBoard->save();
            }
            if (!empty($request->board_id)) {
                $boardId = $request->board_id;
            } else {
                $boardId = 0;
            }
            $wishlist = new Wishlist;
            $wishlist->board_id = $boardId;
            $wishlist->user_id = $user_id;
            $wishlist->product_id = $product->id;
            $wishlist->brand_id = $product->user_id;
            $wishlist->product_name = $productName;
            $wishlist->product_sku = $productSKU;
            $wishlist->quantity = (int)$request->quantity;

            if (!empty($request->variant_id) && $variant) {
                $wishlist->price = $variant->price;
                if ($productType === 'OPEN_SIZING') {
                    $optionsArr = [];
                    $styleArr = [];
                    $styleGrpArr = [];
                    $priceArr = [];
                    $qtyArr = [];

                    if (!empty($request->openSizingArray)) {
                        foreach ($request->openSizingArray as $size) {
                            $sizeVariant = ProductVariation::where('product_id', $product->id)->where('options1', "Size")->where('value1', $size['value'])->where('value2', $variant->value2)->where('value3', $variant->value3)->first();
                            if ($variant->options1 === 'Size') {
                                $sizeVariant = ProductVariation::where('product_id', $product->id)->where('options1', "Size")->where('value1', $size['value'])->where('value2', $variant->value2)->where('value3', $variant->value3)->first();
                            }
                            if ($variant->options2 === 'Size') {
                                $sizeVariant = ProductVariation::where('product_id', $product->id)->where('options2', "Size")->where('value2', $size['value'])->where('value1', $variant->value1)->where('value3', $variant->value3)->first();
                            }
                            if ($variant->options3 === 'Size') {
                                $sizeVariant = ProductVariation::where('product_id', $product->id)->where('options3', "Size")->where('value3', $size['value'])->where('value2', $variant->value2)->where('value1', $variant->value1)->first();
                            }
                            if ($sizeVariant->stock < $wishlist->quantity || $sizeVariant->stock <= 0) {

                                return ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];
                            }
                            $optionsArr[$sizeVariant->id] = $size['qty'];

                            $styleGrpArr[] = "(" . $size['qty'] . ")" . $size['value'];
                            $priceArr[] = $sizeVariant->price;
                            $qtyArr[] = $size['qty'];
                        }
                    }
                    if ($variant->options1 === 'Size') {
                        $styleArr[] = $variant->value2;
                        $styleArr[] = $variant->value3;
                    }
                    if ($variant->options2 === 'Size') {
                        $styleArr[] = $variant->value1;
                        $styleArr[] = $variant->value3;
                    }
                    if ($variant->options3 === 'Size') {
                        $styleArr[] = $variant->value2;
                        $styleArr[] = $variant->value1;
                    }
                    $tPrice = 0;
                    foreach ($qtyArr as $qk => $qv) {
                        $tPrice += $priceArr[$qk] * $qv;
                    }
                    $wishlist->price = $tPrice;
                    $wishlist->style_name = rtrim(implode(',', $styleArr), ',');
                    $wishlist->style_group_name = rtrim(implode(',', $styleGrpArr), ',');
                    $wishlist->reference = serialize($optionsArr);
                    $wishlist->variant_id = $request->variant_id;
                }
                if ($productType === 'PREPACK') {
                    if ($variant->stock < $wishlist->quantity || $variant->stock <= 0) {

                        return ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];

                    }
                    $prdVariant = ProductPrepack::where('id', $request->prepack_id)->first();
                    $wishlist->price = $prdVariant->packs_price;
                    $wishlist->style_name = $prdVariant->style;
                    $wishlist->style_group_name = $prdVariant->size_ratio . ';' . $prdVariant->size_range;
                    $wishlist->reference = $request->variant_id;
                    $wishlist->variant_id = $request->prepack_id;
                }
                if ($productType === 'SINGLE_PRODUCT') {
                    if ($variant->stock < $wishlist->quantity || $variant->stock <= 0) {

                        return ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];
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

                    return ['res' => false, 'msg' => 'Stock not sufficient!.', 'data' => ""];
                }
            }
            $wishlist->amount = $wishlist->price * $wishlist->quantity;
            $wishlist->type = $productType;
            $wishlist->save();

            return ['res' => true, 'msg' => 'Product successfully added to wish list', 'data' => ""];
        }


    }

    /**
     * Fetch wishlist by user
     *
     * @param $request
     * @return array
     */
    public function fetch($request): array
    {
        $boardArr = [];
        $brandArr = [];
        $productArr = [];
        $id = auth()->user()->id;
        $user = User::find($id);
        $wishesBrand = '';
        $wishesByBoard = '';

        if ($user) {
            $wishesBrand = Wishlist::where('user_id', $id)->where('cart_id', null)->groupBy('brand_id')->get()->toArray();
            $wishesByBoard = Board::where('user_id', $id)->orderBy('updated_at', 'desc')->get();
        }
        if ($wishesBrand) {
            foreach ($wishesBrand as $brandK => $brandV) {
                $brand = Brand::where('user_id', $brandV['brand_id'])->first();
                $brandArr[$brandK]['brand_key'] = $brand->brand_key;
                $brandArr[$brandK]['brand_id'] = $brand->user_id;
                $brandArr[$brandK]['brand_name'] = $brand->brand_name;
                $brandArr[$brandK]['brand_logo'] = $brand->logo_image != '' ? asset('public') . '/' . $brand->logo_image : asset('public/img/logo-image.png');
                $brandArr[$brandK]['avg_lead_time'] = $brand->avg_lead_time;
                $brandArr[$brandK]['first_order_min'] = $brand->first_order_min;

                $country = Country::where('id', $brand->country)->first();
                $brandArr[$brandK]['country'] = $country->name;
                $headQuarter = Country::where('id', $brand->headquatered)->first();
                $brandArr[$brandK]['headquatered'] = $headQuarter->name;
                $productShipped = Country::where('id', $brand->product_shipped)->first();
                $brandArr[$brandK]['product_shipped'] = $productShipped->name;

                $prdArr = Wishlist::where('user_id', $id)->where('cart_id', null)->where('brand_id', $brandV['brand_id'])->get()->toArray();
                if (!empty($prdArr)) {
                    foreach ($prdArr as $prdK => $prdV) {
                        $product = Product::find($prdV['product_id']);
                        $productWholeSalePrice = $product->usd_wholesale_price;
                        $productRetailPrice = $product->usd_retail_price;
                        if (!empty($prdV['variant_id']) && $prdV['type'] != 'PREPACK') {
                            $variant = ProductVariation::find($prdV['variant_id']);
                            $productWholeSalePrice = $variant->price;
                            $productRetailPrice = $variant->retail_price;
                        }
                        if (!empty($prdV['variant_id']) && $prdV['type'] == 'PREPACK') {
                            $variant = ProductPrepack::find($prdV['variant_id']);
                            $productWholeSalePrice = $variant->packs_price;
                            $productRetailPrice = $variant->packs_price;
                        }
                        $brandArr[$brandK]['products'][$prdK]['id'] = $prdV['id'];
                        $brandArr[$brandK]['products'][$prdK]['product_id'] = $product->id;
                        $brandArr[$brandK]['products'][$prdK]['product_key'] = $product->product_key;
                        $brandArr[$brandK]['products'][$prdK]['product_name'] = $product->name;
                        $brandArr[$brandK]['products'][$prdK]['product_wholesale_price'] = $productWholeSalePrice;
                        $brandArr[$brandK]['products'][$prdK]['product_retail_price'] = $productRetailPrice;
                        $brandArr[$brandK]['products'][$prdK]['product_qty'] = $prdV['quantity'];
                        $brandArr[$brandK]['products'][$prdK]['style_name'] = $prdV['style_name'];
                        $brandArr[$brandK]['products'][$prdK]['style_group_name'] = $prdV['style_group_name'];
                        $brandArr[$brandK]['products'][$prdK]['type'] = $prdV['type'];
                        $brandArr[$brandK]['products'][$prdK]['product_image'] = $product->featured_image != '' ? $product->featured_image : asset('public/img/logo-image.png');

                        $productDet['id'] = $prdV['id'];
                        $productDet['product_id'] = $product->id;
                        $productDet['product_key'] = $product->product_key;
                        $productDet['product_name'] = $product->name;
                        $productDet['product_wholesale_price'] = $productWholeSalePrice;
                        $productDet['product_retail_price'] = $productRetailPrice;
                        $productDet['product_qty'] = $prdV['quantity'];
                        $productDet['style_name'] = $prdV['style_name'];
                        $productDet['style_group_name'] = $prdV['style_group_name'];
                        $productDet['type'] = $prdV['type'];
                        $productDet['product_image'] = $product->featured_image != '' ? $product->featured_image : asset('public/img/logo-image.png');
                        $productArr[] = $productDet;
                    }
                }
            }
        }
        if ($wishesByBoard) {
            foreach ($wishesByBoard as $brdK => $brdV) {
                $wishesByBoard = Wishlist::where('board_id', $brdV->id)->where('cart_id', null)->groupBy('board_id')->first();
                $wishesByBoardCount = Wishlist::where('board_id', $brdV->id)->where('cart_id', null)->get()->count();
                $productImage = '';
                if ($wishesByBoard && $wishesByBoardCount > 0) {
                    $productDetails = Product::find($wishesByBoard->product_id);
                    $productImage = $productDetails->featured_image;
                }
                $board = Board::find($brdV->id);
                if ($board) {
                    $boardArr[$brdK]['board_key'] = $board->board_key;
                    $boardArr[$brdK]['board_id'] = $board->id;
                    $boardArr[$brdK]['board_name'] = $board->name;
                    $boardArr[$brdK]['board_visibility'] = $board->visibility;
                    $boardArr[$brdK]['board_image'] = $productImage != '' ? $productImage : asset('public/img/board-image.png');
                    $boardArr[$brdK]['products_count'] = $wishesByBoardCount;
                }
            }
        }

        $data = array(
            'board_arr' => $boardArr,
            'brand_arr' => $brandArr,
            'product_arr' => $productArr,
        );


        return ['res' => true, 'msg' => "", 'data' => $data];
    }

    /**
     * Fetch Boards by logged user
     *
     * @param $request
     * @return array
     */
    public function fetchBoards($request): array
    {
        $boardArr = [];
        $id = auth()->user()->id;
        $user = User::find($id);
        $wishesByBoard = '';
        if ($user) {
            $wishesByBoard = Board::where('user_id', $id)->orderBy('updated_at', 'desc')->get();
        }
        if ($wishesByBoard) {
            foreach ($wishesByBoard as $brdK => $brdV) {
                $wishesByBoard = Wishlist::where('board_id', $brdV->id)->where('cart_id', null)->groupBy('board_id')->first();
                $wishesByBoardCount = Wishlist::where('board_id', $brdV->id)->where('cart_id', null)->get()->count();
                $productImage = '';
                if ($wishesByBoard && $wishesByBoardCount > 0) {
                    $productDetails = Product::find($wishesByBoard->product_id);
                    $productImage = $productDetails->featured_image;
                }
                $board = Board::find($brdV->id);
                if ($board) {
                    $boardArr[$brdK]['board_key'] = $board->board_key;
                    $boardArr[$brdK]['board_id'] = $board->id;
                    $boardArr[$brdK]['board_name'] = $board->name;
                    $boardArr[$brdK]['board_visibility'] = $board->visibility;
                    $boardArr[$brdK]['board_image'] = $productImage != '' ? $productImage : asset('public/img/board-image.png');
                    $boardArr[$brdK]['products_count'] = $wishesByBoardCount;
                }
            }
        }

        $data = $boardArr;


        return ['res' => true, 'msg' => "", 'data' => $data];
    }

    /**
     * Fetch board by Key
     *
     * @param $request
     * @return array
     */

    public function fetchBoard($request): array
    {
        $boardArr = [];
        $productArr = [];
        $key = $request->key;
        $board = Board::where('board_key', $key)->first();
        if ($board) {
            $wishesByBoard = Wishlist::where('board_id', $board->id)->where('cart_id', null)->get();
            if ($wishesByBoard) {
                foreach ($wishesByBoard as $wish) {
                    $product = Product::find($wish->product_id);
                    $productWholeSalePrice = $product->usd_wholesale_price;
                    $productRetailPrice = $product->usd_retail_price;
                    if (!empty($wish->variant_id) && $wish->type != 'PREPACK') {
                        $variant = ProductVariation::find($wish->variant_id);
                        $productWholeSalePrice = $variant->price;
                        $productRetailPrice = $variant->retail_price;
                    }
                    if (!empty($wish->variant_id) && $wish->type == 'PREPACK') {
                        $variant = ProductPrepack::find($wish->variant_id);
                        $productWholeSalePrice = $variant->packs_price;
                        $productRetailPrice = $variant->packs_price;
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
            $wishesBrandCount = Wishlist::where('board_id', $board->id)->where('cart_id', null)->get()->count();
            $board->products_count = $wishesBrandCount;
            $boardArr = $board;
        }
        $data = array(
            'board_arr' => $boardArr,
            'product_arr' => $productArr,
        );

        return ['res' => true, 'msg' => "", 'data' => $data];
    }

    /**
     * Add board by user
     *
     * @param $request
     * @return array
     */

    public function addBoard($request): array
    {

        $newBoard = new Board;
        $newBoard->user_id = auth()->user()->id;
        $newBoard->board_key = 'rb_' . Str::lower(Str::random(10));
        $newBoard->name = $request['name'];
        $newBoard->visibility = $request['visibility'];
        $status = $newBoard->save();
        if ($status) {

            return ['res' => true, 'msg' => "Successfully added your board", 'data' => ''];
        } else {

            return ['res' => false, 'msg' => "Please try again!", 'data' => ''];
        }


    }

    /**
     * Update board by key
     * @param $request
     * @return array
     */
    public function updateBoard($request): array
    {

        $board = Board::where('board_key', $request['key'])->first();
        $board->name = $request['name'];
        $board->visibility = $request['visibility'];
        $status = $board->save();
        if ($status) {
            return ['res' => true, 'msg' => "Successfully updated your board", 'data' => ''];
        } else {
            return ['res' => false, 'msg' => "Please try again!", 'data' => ''];
        }


    }

    /**
     * Delete board
     * @param $request
     * @return array
     */
    public function deleteBoard($request): array
    {
        $board = Board::where('board_key', $request->key)->first();
        if ($board) {
            $wishesByBoard = Wishlist::where('board_id', $board->id)->where('cart_id', null)->get();
            if ($wishesByBoard) {
                foreach ($wishesByBoard as $wish) {
                    Wishlist::where('id', $wish->id)->delete();
                }
            }
            Board::where('id', $board->id)->delete();

            return ['res' => true, 'msg' => 'Board successfully removed', 'data' => ""];

        }

        return ['res' => false, 'msg' => 'Error please try again', 'data' => ""];

    }

    /**
     * Change board by wishlist ID
     *
     * @param $request
     * @return array
     */
    public function changeBoard($request): array
    {
        $wishList = Wishlist::find($request->wish_id);
        if ($wishList) {
            $board = Board::where('board_key', $request->board_key)->first();
            $wishList->board_id = $board->id;
            $status = $wishList->save();
            if ($status) {

                return ['res' => true, 'msg' => 'Successfully updated', 'data' => ""];

            } else {

                return ['res' => false, 'msg' => "Please try again!", 'data' => ''];

            }
        }

        return ['res' => false, 'msg' => 'Error please try again', 'data' => ""];

    }

    /**
     *
     * Delete wishlist
     *
     * @param $request
     * @return array
     */

    public function delete($request): array
    {
        $wishlist = Wishlist::find($request->id);
        if ($wishlist) {
            $wishlist->delete();

            return ['res' => true, 'msg' => 'Wishlist successfully removed', 'data' => ""];

        }

        return ['res' => false, 'msg' => 'Error please try again', 'data' => ""];
    }

}
