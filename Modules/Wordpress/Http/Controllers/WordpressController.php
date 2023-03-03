<?php

namespace Modules\Wordpress\Http\Controllers;

use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Entities\Products;
use Modules\Product\Entities\ProductVariation;
use Modules\Product\Entities\ProductImage;
use Modules\Product\Entities\BrandStore;
use DB;
use Illuminate\Support\Str;

class WordpressController extends Controller
{

    public function index(Request $request)
    {
        $user_id = $request->user_id;
        $storeUrl = preg_replace("#^[^:/.]*[:/]+#i", "", $request->website);
        $resultsWebsite = BrandStore::where('website', $storeUrl)->get();
        if (count($resultsWebsite) ==0) {
            $apiURL = '';
            $result = $this->curlCall($apiURL, [], 'GET', $request);
            $statusCode = $result['statusCode'];
            $responseBody = $result['responseBody'];

            if (!empty($responseBody)) {
                foreach ($responseBody as $product) {

                    if (!empty($product['description'])) {
                        $desc = $product['description'];
                    } else {

                        $desc = $product['short_description'];
                    }
                    if (!empty($product['stock_quantity'])) {
                        $stock = $product['stock_quantity'];
                    } else {
                        $stock = 0;
                    }


                    if (!empty($product['images'])) {
                        $image = $product['images'][0]['src'];
                    } else {
                        $image = '';
                    }

                    $title = str_replace("'", "`", $product['name']);
                    $desc = str_replace("'", "`", $desc);
                    $productKey = 'p_' . Str::lower(Str::random(10));
                    $productSlug = Str::slug($title, '-');
                    $ProductAdd = new Products();
                    $ProductAdd->product_key = $productKey;
                    $ProductAdd->slug = $productSlug;
                    $ProductAdd->name = addslashes($title);
                    $ProductAdd->user_id = $request->input('user_id');
                    $ProductAdd->status = "unpublish";
                    $ProductAdd->description = addslashes($desc);
                    $ProductAdd->sku = $product['sku'];
                    $ProductAdd->stock = $stock;
                    $ProductAdd->product_id = $product['id'];
                    $ProductAdd->website = $storeUrl;
                    $ProductAdd->featured_image = $image;
                    $ProductAdd->import_type = 'wordpress';
                    $ProductAdd->default_currency = 'JOD';
                    $ProductAdd->created_at = date('Y-m-d H:i:s');
                    $ProductAdd->updated_at = date('Y-m-d H:i:s');
                    $ProductAdd->save();
                    $lastProductId = DB::getPdo()->lastInsertId();


                    $images = $product['images'];
                    if (!empty($images)) {

                        foreach ($images as $img) {

                            if ($img['src'] == $image) {
                                $feature_key = 1;
                            } else {
                                $feature_key = 0;
                            }

                            $ImageAdd = new ProductImage();
                            $ImageAdd->product_id = $lastProductId;
                            $ImageAdd->images = $img['src'];

                            $ImageAdd->feature_key = $feature_key;
                            $ImageAdd->save();
                        }
                    }


                    $variations = $product['variations'];
                    if (!empty($variations) && count($variations) > 0) {

                        foreach ($variations as $vars) {
                            if (!empty($vars['stock_quantity'])) {
                                $stock = $vars['stock_quantity'];
                            } else {
                                $stock = 0;
                            }

                            $variantKey = 'v_' . Str::lower(Str::random(10));

                            $productVariation = new ProductVariation();
                            $productVariation->variant_key = $variantKey;
                            $productVariation->image = $vars['image'][0]['src'] ?? '';
                            $productVariation->product_id = $lastProductId;
                            $productVariation->price = 0;
                            $productVariation->options1 = $vars['attributes'][0]['name'] ?? '';
                            $productVariation->options2 = $vars['attributes'][1]['name'] ?? '';
                            $productVariation->options3 = $vars['attributes'][2]['name'] ?? '';
                            $productVariation->sku = $vars['sku'] ?? '';
                            $productVariation->value1 = $vars['attributes'][0]['option'] ?? '';
                            $productVariation->value2 = $vars['attributes'][1]['option'] ?? '';
                            $productVariation->value3 = $vars['attributes'][2]['option'] ?? '';
                            $productVariation->website_product_id = $product['id'];
                            $productVariation->website = $storeUrl;
                            $productVariation->stock = $stock;
                            $productVariation->variation_id = $vars['id'] ?? 0;
                            $productVariation->save();
                        }
                    }
                }

                $brandStore = new BrandStore();
                $brandStore->brand_id = $request->user_id;
                $brandStore->website = $storeUrl;
                $brandStore->api_key = $request->consumer_key;
                $brandStore->api_password = $request->consumer_secret;
                $brandStore->types = 'wordpress';
                $brandStore->save();
                Redis::flushDB();
                $response = ['res' => true, 'msg' => "Imported Successfully", 'data' => ""];
            } else {
                $response = ['res' => false, 'msg' => "Enter valid information", 'data' => ""];
            }
        } else {
            $response = ['res' => true, 'msg' => "Already import", 'data' => ""];
        }
        return response()->json($response);
    }

    public function wordpressSync(Request $request)
    {
        $syncs = BrandStore::where('id', $request->id)
            ->get()->first();
        $request->consumer_key = $syncs->api_key;
        $request->consumer_secret = $syncs->api_password;
        $request->website = 'https://' . $syncs->website;
        $apiURL = '';
        $result = $this->curlCall($apiURL, [], 'GET', $request);
        $statusCode = $result['statusCode'];
        $responseBody = $result['responseBody'];

        if (!empty($responseBody)) {
            foreach ($responseBody as $product) {
                if (!empty($product['stock_quantity'])) {
                    $stock = $product['stock_quantity'];
                } else {
                    $stock = 0;
                }

                Products::where('website', $syncs->website)->where('product_id', $product['id'])
                    ->update([
                        'stock' => $stock
                    ]);

            }
            Redis::flushDB();
            $response = ['res' => true, 'msg' => "Sync Successfully", 'data' => ""];
        } else {
            $response = ['res' => false, 'msg' => "Enter valid information", 'data' => ""];
        }
        return response()->json($response);
    }

    public function syncWordpress(Request $request)
    {
        $result_array = array();
        $user_id = $request->user_id;
        $syncs = BrandStore::where('brand_id', $request->user_id)
            ->where('website', $request->website)
            ->get()->first();
        $request->consumer_key = $syncs->api_key;
        $request->consumer_secret = $syncs->api_password;
        $request->website = 'https://' . $syncs->website;
        $qry = Products::where('id', $request->product_id)->get()->first();
        $data = array(
            'stock_quantity' => $qry->stock
        );
        $apiURL = '/' . $qry->product_id;
        $result = $this->curlCall($apiURL, $data, 'POST', $request);
        $response = ['res' => true, 'msg' => "Sync Successfully", 'data' => ""];
        return response()->json($response);
    }

    private function curlCall($url, $param, $method, $request)
    {
        $consumer_key = $request->consumer_key;
        $consumer_secret = $request->consumer_secret;
        $website = $request->website;
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
        $resultObject = array("statusCode" => $statusCode, "responseBody" => $responseBody);
        return $resultObject;

    }


}
