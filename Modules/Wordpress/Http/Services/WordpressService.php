<?php

namespace Modules\Wordpress\Http\Services;

use Automattic\WooCommerce\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariation;
use Modules\Product\Entities\ProductImage;
use Modules\Wordpress\Entities\Store;
use Modules\Wordpress\Entities\Webhook;
use Modules\User\Entities\User;

class WordpressService
{
    protected Wordpress $wordpress;

    protected User $user;

    /**
     * Import Product
     *
     * @param array $requestData
     * @return array
     */
    public function create(array $requestData): array
    {

        $userId = auth()->user()->id;
        $storeUrl = $requestData['website'];
        $apiURL = '';
        $result = $this->curlCall($apiURL, [], 'GET', $requestData);
        $statusCode = $result['statusCode']['http_code'];
        if ($statusCode <> 200) {
            return ['res' => false, 'msg' => "Please enter valid information", 'data' => ""];
        }
        $woocommerce = new Client(
            $requestData['website'],
            $requestData['consumerKey'],
            $requestData['consumerSecret'],
            [
                'wp_api' => true,
                'version' => 'wc/v3',
            ]
        );

        $products = $woocommerce->get('products');

        if (!empty($products)) {
            $currencies = $woocommerce->get('data/currencies', ['per_page' => 1]);
            $defaultCurrency = $currencies[0]->code;
            foreach ($products as $product) {
                if (!empty($product->description)) {
                    $desc = $product->description;
                } else {
                    $desc = $product->short_description;
                }
                $productKey = 'p_' . Str::lower(Str::random(10));
                $ProductAdd = new Product();
                $ProductAdd->product_key = $productKey;
                $ProductAdd->slug = $product->slug;
                $ProductAdd->name = $product->name;
                $ProductAdd->user_id = $userId;
                $ProductAdd->status = "unpublish";
                $ProductAdd->description = $desc;
                $ProductAdd->sku = $product->sku;
                $ProductAdd->usd_retail_price = $product->price;
                $ProductAdd->usd_wholesale_price = $product->price / 2;
                $ProductAdd->stock = $product->stock_quantity ?? 0;
                $ProductAdd->product_id = $product->id;
                $ProductAdd->website = $storeUrl;
                $ProductAdd->featured_image = $product->images[0]->src ?? '';
                $ProductAdd->import_type = 'wordpress';
                $ProductAdd->default_currency = $defaultCurrency;
                $ProductAdd->save();
                $lastInsertId = $ProductAdd->id;
                if (!empty($product->images)) {
                    foreach ($product->images as $img) {
                        if ($img->src == $product->images[0]->src) {
                            $feature_key = 1;
                        } else {
                            $feature_key = 0;
                        }
                        $ImageAdd = new ProductImage();
                        $ImageAdd->product_id = $lastInsertId;
                        $ImageAdd->images = $img->src;
                        $ImageAdd->feature_key = $feature_key;
                        $ImageAdd->save();
                    }
                }
                if ($product->type == 'variable') {
                    $variations = $woocommerce->get('products/' . $product->id . '/variations');
                    if (!empty($variations)) {
                        foreach ($variations as $vars) {
                            $variantKey = 'v_' . Str::lower(Str::random(10));
                            $productVariation = new ProductVariation();
                            $productVariation->variant_key = $variantKey;
                            $productVariation->image = $vars->image->src ?? '';
                            $productVariation->swatch_image = $vars->image->src ?? '';
                            $productVariation->product_id = $lastInsertId;
                            $productVariation->price = $vars->price / 2;
                            $productVariation->retail_price = $vars->price;
                            $productVariation->options1 = $vars->attributes[0]->name ?? '';
                            $productVariation->options2 = $vars->attributes[1]->name ?? '';
                            $productVariation->options3 = $vars->attributes[2]->name ?? '';
                            $productVariation->sku = $vars->sku ?? '';
                            $productVariation->value1 = $vars->attributes[0]->option ?? '';
                            $productVariation->value2 = $vars->attributes[1]->option ?? '';
                            $productVariation->value3 = $vars->attributes[2]->option ?? '';
                            $productVariation->website_product_id = $product->id;
                            $productVariation->website = $storeUrl;
                            $productVariation->stock = $vars->stock_quantity ?? 0;
                            $productVariation->variation_id = $vars->id ?? 0;
                            $productVariation->save();
                        }
                        $getVar = ProductVariation::where('product_id', $lastInsertId)->get();
                        if (!empty($getVar)) {
                            $optionItemArr = [];
                            $values1Arr = [];
                            $values2Arr = [];
                            $values3Arr = [];
                            foreach ($getVar as $v) {
                                if (!empty($v->options1)) {
                                    if (!in_array($v->value1, $values1Arr)) {
                                        $values1Arr[] = $v->value1;
                                    }
                                }
                                if (!empty($v->options2)) {
                                    if (!in_array($v->value2, $values2Arr)) {
                                        $values2Arr[] = $v->value2;
                                    }
                                }
                                if (!empty($v->options3)) {
                                    if (!in_array($v->value3, $values3Arr)) {
                                        $values3Arr[] = $v->value3;
                                    }
                                }
                            }
                            if (!empty($values1Arr)) {
                                foreach ($values1Arr as $value1) {
                                    $optionItems1[] = array(
                                        "display" => $value1,
                                        "value" => $value1
                                    );
                                }
                                $optionItemArr[] = $optionItems1;
                            }
                            if (!empty($values2Arr)) {
                                foreach ($values2Arr as $value2) {
                                    $optionItems2[] = array(
                                        "display" => $value2,
                                        "value" => $value2
                                    );
                                }
                                $optionItemArr[] = $optionItems2;
                            }
                            if (!empty($values3Arr)) {
                                foreach ($values3Arr as $value3) {
                                    $optionItems3[] = array(
                                        "display" => $value3,
                                        "value" => $value3
                                    );
                                }
                                $optionItemArr[] = $optionItems3;
                            }


                            $optionItems = json_encode($optionItemArr);
                            Product::where('id', $lastInsertId)->update([
                                'option_items' => $optionItems
                            ]);
                        }
                    }

                }
            }
            $remove = array("http://", "https://", "www.", "/");
            $brandStore = new Store;
            $brandStore->brand_id = $userId;
            $brandStore->website = $storeUrl;
            $brandStore->api_key = $requestData['consumerKey'];
            $brandStore->api_password = $requestData['consumerSecret'];
            $brandStore->types = 'wordpress';
            $brandStore->url = str_replace($remove, "", $storeUrl);
            $brandStore->save();
        } else {
            return ['res' => false, 'msg' => "No product found !", 'data' => ""];
        }

        return ['res' => true, 'msg' => "Imported Successfully", 'data' => ""];
    }

    /**
     * Insert Product log which is update from import website
     *
     * @param array $request
     * @return array
     */
    public function webHookUpdate(array $request): array
    {

        $website = explode('/product', $request['permalink']);
        $store = Store::where('website', $website[0])->first();
        $infos = Webhook::where('user_id', $store->brand_id)->where('product_id', $request['id'])->where('actions', 'created')->count();
        if ($infos == 0) {
            $webHook = new Webhook;
            $webHook->user_id = $store->brand_id;
            $webHook->product_id = $request['id'];
            $webHook->website = $website[0];
            $webHook->api_key = $store->api_key;
            $webHook->api_password = $store->api_password;
            $webHook->types = 'wordpress';
            $webHook->actions = 'updated';
            $webHook->save();
        }

        return ['res' => true, 'msg' => "Imported Successfully", 'data' => ""];
    }

    /**
     * Insert Product log which is update from import website
     *
     * @param array $request
     * @return array
     */
    public function webHookDelete(array $request): array
    {
        $product = Product::where('product_id', $request['id'])->where('import_type', 'wordpress')->first();
        if (!empty($product)) {
            $website = explode('/product', $product->website);
            $store = Store::where('website', $website)->first();
            $webHook = new Webhook;
            $webHook->user_id = $store->brand_id;
            $webHook->product_id = $request['id'];
            $webHook->website = $website;
            $webHook->api_key = $store->api_key;
            $webHook->api_password = $store->api_password;
            $webHook->types = 'wordpress';
            $webHook->actions = 'deleted';
            $webHook->save();
        }

        return ['res' => true, 'msg' => "Imported Successfully", 'data' => ""];
    }

    /**
     * Insert Product log which is update from import website
     *
     * @param object $request
     * @return array
     */
    public function webHookCreate(object $request): array
    {

        $website = explode('/product', $request['permalink']);
        $store = Store::where('website', $website[0])->first();
        $webHook = new Webhook;
        $webHook->user_id = $store->brand_id;
        $webHook->product_id = $request['id'];
        $webHook->website = $website[0];
        $webHook->api_key = $store->api_key;
        $webHook->api_password = $store->api_password;
        $webHook->types = 'wordpress';
        $webHook->actions = 'created';
        $webHook->save();

        return ['res' => true, 'msg' => "Imported Successfully", 'data' => ""];
    }

    /**
     * Insert Product log which is update from import website
     *
     * @return array
     */
    public function actionInfo(): array
    {
        $userId = auth()->user()->id;

        $infos = Webhook::where('user_id', $userId)->get();
        $message = '';
        $data = [];
        if (!empty($infos)) {
            foreach ($infos as $info) {
                if ($info->actions == 'updated') {
                    $product = Product::where('product_id', $info->product_id)->where('website', $info->website)->first();
                    $message = 'A product called ' . $product->name . ' has been updated from ' . $info->website . '. Do you want to updated?';
                } else if ($info->actions == 'created') {
                    $message = 'A new product create from ' . $info->website . '. Do you want to add?';
                } else if ($info->actions == 'deleted') {
                    $product = Product::where('product_id', $info->product_id)->where('website', $info->website)->first();
                    $message = 'A product called ' . $product->name . ' has been deleted from ' . $info->website . '. Do you want to deleted?';
                }
                $data[] = array(
                    'id' => $info->id,
                    'message' => $message,

                );
            }
        }

        return ['res' => true, 'msg' => "", 'data' => $data];

    }

    /**
     * create Product from outside
     *
     * @param object $requestData
     * @return array
     */
    public function createProduct(object $requestData): array
    {
        $userId = auth()->user()->id;
        $id = $requestData->id;
        $store = Webhook::where("id", $id)->first();
        $woocommerce = new Client(
            $store->website,
            $store->api_key,
            $store->api_password,
            [
                'wp_api' => true,
                'version' => 'wc/v3',
            ]
        );
        $products = $woocommerce->get('products/' . $id);
        if (!empty($products)) {
            $currencies = $woocommerce->get('data/currencies', ['per_page' => 1]);
            $defaultCurrency = $currencies[0]->code;
            foreach ($products as $product) {

                if (!empty($product->description)) {
                    $desc = $product->description;
                } else {
                    $desc = $product->short_description;
                }

                $productKey = 'p_' . Str::lower(Str::random(10));
                $ProductAdd = new Product();
                $ProductAdd->product_key = $productKey;
                $ProductAdd->slug = $product->slug;
                $ProductAdd->name = $product->name;
                $ProductAdd->user_id = $userId;
                $ProductAdd->status = "unpublish";
                $ProductAdd->description = $desc;
                $ProductAdd->sku = $product->sku;
                $ProductAdd->usd_retail_price = $product->price;
                $ProductAdd->usd_wholesale_price = $product->price / 2;
                $ProductAdd->stock = $product->stock_quantity;
                $ProductAdd->product_id = $product->id;
                $ProductAdd->website = $store->website;
                $ProductAdd->featured_image = $product->images[0]->src ?? '';
                $ProductAdd->import_type = 'wordpress';
                $ProductAdd->default_currency = $defaultCurrency;
                $ProductAdd->save();
                $lastInsertId = $ProductAdd->id;


                if (!empty($product->images)) {
                    foreach ($product->images as $img) {
                        if ($img->src == $product->images[0]->src) {
                            $feature_key = 1;
                        } else {
                            $feature_key = 0;
                        }
                        $ImageAdd = new ProductImage();
                        $ImageAdd->product_id = $lastInsertId;
                        $ImageAdd->images = $img->src;
                        $ImageAdd->feature_key = $feature_key;
                        $ImageAdd->save();
                    }
                }
                if ($product->type == 'variable') {

                    $variations = $woocommerce->get('products/' . $product->id . '/variations');
                    if (!empty($variations)) {
                        foreach ($variations as $vars) {
                            if (!empty($vars->stock_quantity)) {
                                $stock = $vars->stock_quantity;
                            } else {
                                $stock = 0;
                            }
                            $variantKey = 'v_' . Str::lower(Str::random(10));
                            $productVariation = new ProductVariation();
                            $productVariation->variant_key = $variantKey;
                            $productVariation->image = $vars->image->src ?? '';
                            $productVariation->product_id = $lastInsertId;
                            $productVariation->price = $vars->price / 2;
                            $productVariation->retail_price = $vars->price;
                            $productVariation->options1 = $vars->attributes[0]->name ?? '';
                            $productVariation->options2 = $vars->attributes[1]->name ?? '';
                            $productVariation->options3 = $vars->attributes[2]->name ?? '';
                            $productVariation->sku = $vars->sku ?? '';
                            $productVariation->value1 = $vars->attributes[0]->option ?? '';
                            $productVariation->value2 = $vars->attributes[1]->option ?? '';
                            $productVariation->value3 = $vars->attributes[2]->option ?? '';
                            $productVariation->website_product_id = $product->id;
                            $productVariation->website = $store->website;
                            $productVariation->stock = $stock;
                            $productVariation->variation_id = $vars->id ?? 0;
                            $productVariation->save();
                        }
                    }

                }
            }

        } else {
            return ['res' => false, 'msg' => "Enter valid information", 'data' => ""];
        }

        return ['res' => true, 'msg' => "Created Successfully", 'data' => ""];
    }

    /**
     * Check if website is valid or not
     *
     * @param $url
     * @param $param
     * @param $method
     * @param array $request
     * @return array
     */
    private function curlCall($url, $param, $method, array $request): array
    {
        $consumer_key = $request['consumerKey'];
        $consumer_secret = $request['consumerSecret'];
        $website = $request['website'];
        $apiURL = $website . '/wp-json/wc/v3/products' . $url;
        if ($method == 'GET') {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiURL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, "$consumer_key:$consumer_secret");
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        } else {
            $headers = array(
                'Authorization' => 'Basic ' . base64_encode($consumer_key . ':' . $consumer_secret)
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiURL);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERPWD, "$consumer_key:$consumer_secret");
        }
        $result = curl_exec($ch);
        $http_status = curl_getinfo($ch);
        $statusCode = $http_status;
        $responseBody = json_decode($result, true);

        return array("statusCode" => $statusCode, "responseBody" => $responseBody);

    }
}
