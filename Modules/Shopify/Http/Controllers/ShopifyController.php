<?php

namespace Modules\Shopify\Http\Controllers;


use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Entities\Products;
use Modules\Product\Entities\ProductVariation;
use Modules\Product\Entities\ProductImage;
use Modules\Product\Entities\BrandStore;
use DB;
use Illuminate\Support\Str;

class ShopifyController extends Controller
{
    public function __construct()
    {
        Redis::connection();
    }

    public function index(Request $request)
    {


        $storeUrl = preg_replace("#^[^:/.]*[:/]+#i", "", $request->store_url);
        $apiURL = '/admin/shop.json';
        $result = $this->curlCall($apiURL, [], 'GET', $request);
        $statusCode = $result['statusCode'];
        $responseBody = $result['responseBody'];

        if ($statusCode <> 200) {
            $response = ['res' => false, 'msg' => "Please enter valid information.", 'data' => ""];
            return response()->json($response);
            exit;
        }
        $defaultCurrency = $responseBody['shop']['currency'];
        $resultsWebsite = BrandStore::where('website', $storeUrl)->get();
        if (count($resultsWebsite) == 0) {
            $apiURL = '/admin/products/count.json';
            $result = $this->curlCall($apiURL, [], 'GET', $request);
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
                $resultSync = $this->curlCall($url, [], 'GET', $request);
                $imageMain = '';
                $productsArr = $resultSync['responseBody']['products'];
                foreach ($productsArr as $product) {
                    if (empty($product['image'])) {
                        $imageMain = '';
                    } else {
                        $imageMain = $product['image']['src'];
                    }

                    if (empty($product['variants'][0]['inventory_quantity'])) {
                        $stock = 0;
                    } else {
                        $stock = $product['variants'][0]['inventory_quantity'];
                    }

                    $productKey = 'p_' . Str::lower(Str::random(10));
                    $productSlug = Str::slug($product['title']);
                    $ProductAdd = new Products();
                    $ProductAdd->product_key = $productKey;
                    $ProductAdd->slug = $productSlug;
                    $ProductAdd->name = addslashes($product['title']);
                    $ProductAdd->user_id = $request->input('user_id');
                    $ProductAdd->status = "unpublish";
                    $ProductAdd->description = addslashes($product['body_html']);
                    $ProductAdd->sku = $product['variants'][0]['sku'];
                    $ProductAdd->stock = $stock;
                    $ProductAdd->product_id = $product['id'];
                    $ProductAdd->website = $storeUrl;
                    $ProductAdd->featured_image = $imageMain;
                    $ProductAdd->import_type = 'shopify';
                    $ProductAdd->default_currency = $defaultCurrency;
                    $ProductAdd->created_at = date('Y-m-d H:i:s');
                    $ProductAdd->updated_at = date('Y-m-d H:i:s');
                    $ProductAdd->save();
                    $last_product_id = DB::getPdo()->lastInsertId();

                    $images = $product['images'];
                    if (!empty($images)) {
                        foreach ($images as $img) {
                            if ($img['src'] == $imageMain) {
                                $feature_key = 1;
                            } else {
                                $feature_key = 0;
                            }

                            $ImageAdd = new ProductImage();
                            $ImageAdd->product_id = $last_product_id;
                            $ImageAdd->images = $img['src'];
                            $ImageAdd->image_id = $img['id'];
                            $ImageAdd->feature_key = $feature_key;
                            $ImageAdd->save();

                        }
                    }

                    $variations = count($product['variants']);

                    if ($variations > 0) {
                        foreach ($product['variants'] as $vars) {
                            $options = count($product['options']);

                            $variantKey = 'v_' . Str::lower(Str::random(10));

                            if (empty($vars['inventory_quantity'])) {
                                $stock = 0;
                            } else {
                                $stock = $vars['inventory_quantity'];
                            }
                            if (!empty($product['options'][0]['name'])) {
                                $productVariation = new ProductVariation();
                                $productVariation->variant_key = $variantKey;
                                $productVariation->image = $imageMain;
                                $productVariation->product_id = $last_product_id;
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
                                $productVariation->stock = $stock;
                                $productVariation->variation_id = $vars['id'];
                                $productVariation->inventory_item_id = $vars['inventory_item_id'];
                                $productVariation->save();
                            }
                        }
                    }
                }

                $newProduct = $product['id'];

            }
            $brandStore = new BrandStore();
            $brandStore->brand_id = $request->user_id;
            $brandStore->website = $storeUrl;
            $brandStore->api_key = $request->api_key;
            $brandStore->api_password = $request->api_password;
            $brandStore->types = 'shopify';
            $brandStore->save();
            $response = ['res' => true, 'msg' => "Successfully Imported", 'data' => ""];
            Redis::flushDB();
        } else {
            $response = ['res' => true, 'msg' => "Already import", 'data' => ""];
        }

        return response()->json($response);

    }

    public function syncToShopify(Request $request)
    {
        $result_array = array();
        $user_id = $request->user_id;
        $syncs = BrandStore::where('brand_id', $request->user_id)
            ->where('website', $request->website)
            ->get()->first();
        $request->api_key = $syncs->api_key;
        $request->api_password = $syncs->api_password;
        $request->store_url = $syncs->website;

        $res = ProductVariation::where('website', $syncs->website)->where('product_id', $request->product_id)->get();
        $totalCount = count($res);
        if ($totalCount > 0) {
            foreach ($res as $var) {
                $qry = Products::where('id', $request->product_id)->get()->first();
                $payload = array(
                    "location_id" => 36132814934,
                    "inventory_item_id" => $var->inventory_item_id,
                    "available" => $totalCount == 1 ? $qry->stock : $var->stock
                );
                $payload = json_encode($payload, JSON_NUMERIC_CHECK);
                $apiURL = '/admin/api/2022-07/inventory_levels/set.json';
                $result = $this->curlCall($apiURL, $payload, 'POST', $request);


            }
        }


        $response = ['res' => true, 'msg' => "Sync Successfully", 'data' => ""];


        return response()->json($response);
    }

    public function syncAll(Request $request)
    {

        $syncs = BrandStore::where('id', $request->id)->get()->first();
        $request->api_key = $syncs->api_key;
        $request->api_password = $syncs->api_password;
        $request->store_url = $syncs->website;

        $apiURL = '/admin/products/count.json';
        $result = $this->curlCall($apiURL, [], 'GET', $request);
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
            $resultSync = $this->curlCall($url, [], 'GET', $request);

            $productsArr = $resultSync['responseBody']['products'];
            foreach ($productsArr as $product) {
                if (empty($product['variants'][0]['inventory_quantity'])) {
                    $stock = 0;
                } else {
                    $stock = $product['variants'][0]['inventory_quantity'];
                }
                Products::where('website', $syncs->website)->where('product_id', $product['id'])
                    ->update([
                        'stock' => $stock
                    ]);
                $variations = count($product['variants']);

                if ($variations > 0) {
                    foreach ($product['variants'] as $vars) {
                        ProductVariation::where('website_product_id', $product['id'])
                            ->where('variation_id', $vars['id'])
                            ->update([
                                'stock' => $vars['inventory_quantity'],
                            ]);
                    }
                }
            }

            $newProduct = $product['id'];

        }
        Redis::flushDB();
        $response = ['res' => true, 'msg' => "Sync Successfully", 'data' => ""];
        return response()->json($response);
    }

    private function curlCall($url, $param, $method, $request)
    {
        $API_KEY = $request->api_key;
        $PASSWORD = $request->api_password;
        $STORE_URL = $request->store_url;
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
        $resultObject = array("statusCode" => $statusCode, "responseBody" => $responseBody);
        return $resultObject;

    }
}
