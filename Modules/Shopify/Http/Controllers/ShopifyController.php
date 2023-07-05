<?php

namespace Modules\Shopify\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Product\Entities\Product;
use Modules\Wordpress\Entities\Webhook;
use Modules\Shopify\Http\Requests\ShopifyRequest;
use Modules\Shopify\Http\Services\ShopifyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class ShopifyController extends Controller
{

    private ShopifyService $shopifyService;

    public function __construct(ShopifyService $shopifyService)
    {
        $this->shopifyService = $shopifyService;
    }

    /**
     * Import Shopify
     *
     * @param ShopifyRequest $request
     * @return JsonResponse
     */
    public function importShopify(ShopifyRequest $request): JsonResponse
    {
        $user = auth()->user();

        if ($user->cannot('viewAny', Product::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->shopifyService->importShopify($request->validated());

        return response()->json($response);
    }

    /**
     * Sync To Shopify
     *
     * @param ShopifyRequest $request
     * @return JsonResponse
     */
    public function syncShopify(Request $request): JsonResponse
    {
        $user = auth()->user();

        if ($user->cannot('viewAny', Product::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->shopifyService->syncShopify($request->all());

        return response()->json($response);
    }

    /**
     * Webhook for create
     *
     * @param Request $request
     * @return mixed
     */
    public function webHookCreate(Request $request): mixed
    {

        $product = $request->json()->all();
        $url = $request->header('X-Shopify-Shop-Domain');
        $response = $this->shopifyService->webHookCreate($product, $url);
        return response()->json($response);

    }

    /**
     * Webhook for update
     *
     * @param Request $request
     * @return mixed
     */
    public function webHookUpdate(Request $request): mixed
    {
        $product = $request->json()->all();
        $url = $request->header('X-Shopify-Shop-Domain');
        $response = $this->shopifyService->webHookUpdate($product, $url);
        return response()->json($response);
    }

    /**
     * Webhook for update
     *
     * @param Request $request
     * @return mixed
     */
    public function webHookOrder(Request $request): mixed
    {

        $product = $request->json()->all();
        $url = $request->header('X-Shopify-Shop-Domain');
        $response = $this->shopifyService->webHookOrder($product, $url);
        return response()->json($response);


        return response()->json(['success' => true]);
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
