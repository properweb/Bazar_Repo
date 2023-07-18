<?php

namespace Modules\Shopify\Http\Services;


use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariation;
use Modules\Product\Entities\ProductImage;
use Modules\Wordpress\Entities\Store;
use Modules\User\Entities\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Wordpress\Entities\Webhook;

class ShopifyService
{
    protected Shopify $shopify;

    protected User $user;

    /**
     * Import Product
     *
     * @param array $requestData
     * @return array
     */
    public function importShopify(array $requestData): array
    {

        $userId = auth()->user()->id;
        $storeUrl = $requestData['store_url'];
        $apiURL = '/admin/shop.json';
        $result = $this->curlCall($apiURL, [], 'GET', $requestData);
        $statusCode = $result['statusCode'];
        $responseBody = $result['responseBody'];
        $locationID = $responseBody['shop']['primary_location_id'];


        if ($statusCode <> 200) {

            return ['res' => false, 'msg' => "Please enter valid information", 'data' => ""];
        }
        $defaultCurrency = $responseBody['shop']['currency'];
        $resultsWebsite = Store::where('website', $storeUrl)->get();
        if (count($resultsWebsite) == 0) {
            $apiURL = '/admin/products/count.json';
            $result = $this->curlCall($apiURL, [], 'GET', $requestData);
            $responseBody = $result['responseBody'];
            $totalCount = ceil($responseBody['count'] / 250);
            $newProduct = '';
            for ($ks = 1; $ks <= $totalCount; $ks++) {
                $sincID = $newProduct;
                if ($ks == 1) {
                    $url = '/admin/products.json?limit=250';
                } else {
                    $url = '/admin/products.json?limit=250&since_id=' . $sincID;
                }
                $resultSync = $this->curlCall($url, [], 'GET', $requestData);

                $productsArr = $resultSync['responseBody']['products'];
                foreach ($productsArr as $product) {


                    $productKey = 'p_' . Str::lower(Str::random(10));
                    $productSlug = Str::slug($product['title']);
                    $ProductAdd = new Product();
                    $ProductAdd->product_key = $productKey;
                    $ProductAdd->slug = $productSlug;
                    $ProductAdd->name = $product['title'];
                    $ProductAdd->user_id = $userId;
                    $ProductAdd->status = "unpublish";
                    $ProductAdd->description = $product['body_html'];
                    $ProductAdd->sku = $product['variants'][0]['sku'] ?? '';
                    $ProductAdd->stock = $product['variants'][0]['inventory_quantity'] ?? 0;
                    $ProductAdd->product_id = $product['id'];
                    $ProductAdd->website = $storeUrl;
                    $ProductAdd->featured_image = $product['image']['src'] ?? '';
                    $ProductAdd->import_type = 'shopify';
                    $ProductAdd->default_currency = $defaultCurrency;
                    $ProductAdd->save();
                    $lastInsertId = $ProductAdd->id;

                    $images = $product['images'];
                    if (!empty($images)) {
                        foreach ($images as $img) {
                            if ($img['src'] == $product['image']['src']) {
                                $feature_key = 1;
                            } else {
                                $feature_key = 0;
                            }

                            $ImageAdd = new ProductImage();
                            $ImageAdd->product_id = $lastInsertId;
                            $ImageAdd->images = $img['src'];
                            $ImageAdd->image_id = $img['id'];
                            $ImageAdd->feature_key = $feature_key;
                            $ImageAdd->save();

                        }
                    }

                    $variations = count($product['variants']);

                    if ($variations > 0) {
                        foreach ($product['variants'] as $vars) {

                            $variantKey = 'v_' . Str::lower(Str::random(10));
                            if (!empty($product['options'][0]['name'])) {
                                $productVariation = new ProductVariation();
                                $productVariation->variant_key = $variantKey;
                                $productVariation->image = $product['image']['src'] ?? '';
                                $productVariation->product_id = $lastInsertId;
                                $productVariation->price = 0;
                                $productVariation->options1 = $product['options'][0]['name'];
                                $productVariation->options2 = $product['options'][1]['name'] ?? '';
                                $productVariation->options3 = $product['options'][2]['name'] ?? '';
                                $productVariation->sku = $vars['sku'];
                                $productVariation->value1 = $vars['option1'];
                                $productVariation->value2 = $vars['option2'] ?? '';
                                $productVariation->value3 = $vars['option3'] ?? '';
                                $productVariation->image_id = $vars['image_id'];
                                $productVariation->website_product_id = $product['id'];
                                $productVariation->website = $storeUrl;
                                $productVariation->stock = $vars['inventory_quantity'] ?? 0;
                                $productVariation->variation_id = $vars['id'];
                                $productVariation->inventory_item_id = $vars['inventory_item_id'];
                                $productVariation->save();
                            }
                        }
                    }


                }

                $newProduct = $product['id'];

            }
            $remove = array("http://", "https://", "www.", "/");
            $brandStore = new Store;
            $brandStore->brand_id = $userId;
            $brandStore->website = $storeUrl;
            $brandStore->api_key = $requestData['api_key'];
            $brandStore->api_password = $requestData['api_password'];
            $brandStore->types = 'shopify';
            $brandStore->location_id = $locationID;
            $brandStore->url = str_replace($remove, "", $storeUrl);
            $brandStore->save();
            $response = ['res' => true, 'msg' => "Successfully Imported", 'data' => ""];

        } else {
            $response = ['res' => true, 'msg' => "Already import", 'data' => ""];
        }

        return $response;
    }

    /**
     * Sync to shopify
     *
     * @param array $request
     * @return array
     */
    public function syncShopify(array $request): array
    {

        $userId = auth()->user()->id;
        $syncs = Store::where('brand_id', $userId)
            ->where('website', $request['website'])
            ->get()->first();
        $request['api_key'] = $syncs->api_key;
        $request['api_password'] = $syncs->api_password;
        $request['store_url'] = $syncs->website;


        $res = ProductVariation::where('website', $syncs->website)->where('product_id', $request['product_id'])->get();
        $totalCount = count($res);
        if ($totalCount > 0) {
            foreach ($res as $var) {
                //$qry = Product::where('id', $request['product_id'])->get()->first();
                $payload = array(
                    "location_id" => $syncs->location_id,
                    "inventory_item_id" => $var->inventory_item_id,
                    "available" => $totalCount == 1 ? $var->stock : $var->stock
                );
                $payload = json_encode($payload, JSON_NUMERIC_CHECK);
                $apiURL = '/admin/api/2022-07/inventory_levels/set.json';
                $this->curlCall($apiURL, $payload, 'POST', $request);


            }
        }

        return ['res' => true, 'msg' => "Sync Successfully", 'data' => ""];

    }

    /**
     * Insert Product from shopify
     *
     * @param array $product
     * @param string $url
     * @return array
     */
    public function webHookCreate(array $product, string $url): array
    {

        $store = Store::where('website', $url)->first();
        $userId = $store->brand_id;
        $productKey = 'p_' . Str::lower(Str::random(10));
        $productSlug = Str::slug($product['title']);
        $ProductAdd = new Product();
        $ProductAdd->product_key = $productKey;
        $ProductAdd->slug = $productSlug;
        $ProductAdd->name = $product['title'];
        $ProductAdd->user_id = $userId;
        $ProductAdd->status = "unpublish";
        $ProductAdd->description = $product['body_html'];
        $ProductAdd->sku = $product['variants'][0]['sku'] ?? '';
        $ProductAdd->stock = $product['variants'][0]['inventory_quantity'] ?? 0;
        $ProductAdd->product_id = $product['id'];
        $ProductAdd->website = $url;
        $ProductAdd->usd_retail_price = (float)$product['variants'][0]['price'];
        $ProductAdd->usd_wholesale_price = (float)$product['variants'][0]['price'] / 2;
        $ProductAdd->featured_image = $product['image']['src'] ?? '';
        $ProductAdd->import_type = 'shopify';
        $ProductAdd->default_currency = 'JOD';
        $ProductAdd->save();
        $lastInsertId = $ProductAdd->id;

        $images = $product['images'];
        if (!empty($images)) {
            foreach ($images as $img) {
                if ($img['src'] == $product['image']['src']) {
                    $feature_key = 1;
                } else {
                    $feature_key = 0;
                }

                $ImageAdd = new ProductImage();
                $ImageAdd->product_id = $lastInsertId;
                $ImageAdd->images = $img['src'];
                $ImageAdd->image_id = $img['id'];
                $ImageAdd->feature_key = $feature_key;
                $ImageAdd->save();

            }
        }

        $variations = count($product['variants']);

        if ($variations > 0) {
            foreach ($product['variants'] as $vars) {

                $variantKey = 'v_' . Str::lower(Str::random(10));
                if (!empty($product['options'][0]['name'])) {
                    $productVariation = new ProductVariation();
                    $productVariation->variant_key = $variantKey;
                    $productVariation->image = $product['image']['src'] ?? '';
                    $productVariation->product_id = $lastInsertId;
                    $productVariation->price = (float)$vars['price'] / 2;
                    $productVariation->retail_price = (float)$vars['price'];
                    $productVariation->options1 = $product['options'][0]['name'];
                    $productVariation->options2 = $product['options'][1]['name'] ?? '';
                    $productVariation->options3 = $product['options'][2]['name'] ?? '';
                    $productVariation->sku = $vars['sku'];
                    $productVariation->value1 = $vars['option1'];
                    $productVariation->value2 = $vars['option2'] ?? '';
                    $productVariation->value3 = $vars['option3'] ?? '';
                    $productVariation->image_id = $vars['image_id'];
                    $productVariation->website_product_id = $product['id'];
                    $productVariation->website = $url;
                    $productVariation->stock = $vars['inventory_quantity'] ?? 0;
                    $productVariation->variation_id = $vars['id'];
                    $productVariation->inventory_item_id = $vars['inventory_item_id'];
                    $productVariation->save();
                }
            }
        }
        $this->storeNotification($store->brand_id, $lastInsertId, $url, $store->api_key, $store->api_password, 'shopify', 'created');

        return ['res' => true, 'msg' => "Sync Successfully", 'data' => ""];
    }

    /**
     * Update Product which is created from shopify
     *
     * @param array $product
     * @param string $url
     * @return array
     */
    public function webHookUpdate(array $product, string $url): array
    {

        $store = Store::where('website', $url)->first();
        $exitHook = Webhook::where('user_id', $store->brand_id)->where('product_id', $product['id'])->where('website', $store->website)->count();

        if ($exitHook == 0) {

            $exits = Product::where('user_id', $store->brand_id)->where('product_id', $product['id'])->where('website', $store->website)->first();

            $productSlug = Str::slug($product['title']);
            Product::where('product_id', $product['id'])->where('website', $store->website)
                ->update([
                    'slug' => $productSlug,
                    'name' => $product['title'],
                    'sku' => $product['variants'][0]['sku'] ?? '',
                    'stock' => $product['variants'][0]['inventory_quantity'] ?? 0,
                    'featured_image' => $product['image']['src'] ?? '',
                    'usd_retail_price' => (float)$product['variants'][0]['price'],
                    'usd_wholesale_price' => (float)$product['variants'][0]['price'] / 2
                ]);

            $variations = count($product['variants']);

            if ($variations > 0) {
                foreach ($product['variants'] as $vars) {

                    ProductVariation::where('variation_id', $vars['id'])->where('website', $store->website)
                        ->update([

                            'stock' => $vars['inventory_quantity'] ?? 0,
                            'price' => (float)$vars['price'] / 2,
                            'retail_price' => (float)$vars['price']

                        ]);
                }
            }

            $this->storeNotification($store->brand_id, $exits->id, $store->website, $store->api_key, $store->api_password, 'shopify', 'updated');
        }

        return ['res' => true, 'msg' => "Sync Successfully", 'data' => ""];
    }

    /**
     * Update Product stock from shopify order
     *
     * @param array $product
     * @param string $url
     * @return array
     */
    public function webHookOrder(array $product, string $url): array
    {

        $store = Store::where('website', $url)->first();
        foreach ($product['line_items'] as $vars) {
            $lastPr = Product::where('product_id', $vars['product_id'])->where('website', $store->website)->first();
            Product::where('product_id', $vars['product_id'])->where('website', $store->website)
                ->update([
                    'stock' => $lastPr->stock - $vars['quantity'],
                ]);

            $lastVr = ProductVariation::where('variation_id', $vars['variant_id'])->where('website', $store->website)->first();
            ProductVariation::where('variation_id', $vars['variant_id'])->where('website', $store->website)
                ->update([

                    'stock' => $lastVr->stock - $vars['quantity'],
                ]);

        }

        return ['res' => true, 'msg' => "Sync Successfully", 'data' => ""];
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
        $API_KEY = $request['api_key'];
        $PASSWORD = $request['api_password'];
        $STORE_URL = $request['store_url'];
        $apiURL = 'https://' . $API_KEY . ':' . $PASSWORD . '@' . $STORE_URL . $url;
        $postInput = $param;
        if ($method == 'GET') {
            $ch = curl_init($apiURL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiURL);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postInput);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
        }
        $result = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $statusCode = $http_status;
        $responseBody = json_decode($result, true);
        return array("statusCode" => $statusCode, "responseBody" => $responseBody);

    }

    /**
     * Save database of notifications
     *
     * @param $brandId
     * @param $id
     * @param $website
     * @param $apiKey
     * @param $apiPwd
     * @param $type
     * @param $actions
     * @return void
     */

    private function storeNotification($brandId, $id, $website, $apiKey, $apiPwd, $type, $actions): void
    {
        $webHook = new Webhook;
        $webHook->user_id = $brandId;
        $webHook->product_id = $id;
        $webHook->website = $website;
        $webHook->api_key = $apiKey;
        $webHook->api_password = $apiPwd;
        $webHook->types = $type;
        $webHook->actions = $actions;
        $webHook->save();
    }

}
