<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Product\Entities\Product;
use Modules\Product\Http\Requests\ProductRequest;
use Modules\Product\Http\Services\ProductService;
use Illuminate\Http\Request;


class ProductController extends Controller
{

    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Create New Product By logged Brand
     *
     * @param ProductRequest $request
     * @return JsonResponse
     */
    public function create(ProductRequest $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('create', Product::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->productService->create($request);
        return response()->json($response);
    }

    /**
     * Fetch All Product Respected Logged Brand
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function fetch(Request $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('viewAny', Product::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->productService->fetch($request);
        return response()->json($response);
    }

    /**

     * Fetch Arrange Product List
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function arrangeProduct(Request $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('viewAny', Product::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->productService->arrangeProduct($request);
        return response()->json($response);
    }

    /**
     * Fetching product inventory by Logged brand
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function fetchStock(Request $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('viewAny', Product::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->productService->fetchStock($request);

        return response()->json($response);
    }

    /**
     * Product Details for respective product
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function details(Request $request): JsonResponse
    {
        $user = auth()->user();
        $product = Product::where('id', $request->id)->where('user_id', $user->id)->first();
        if ($user->cannot('view', $product)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->productService->details($request);

        return response()->json($response);
    }

    /**
     * Update product by ID
     *
     * @param ProductRequest $request
     * @return JsonResponse
     */
    public function update(ProductRequest $request): JsonResponse
    {
        $user = auth()->user();
        $product = Product::where('id', $request->id)->where('user_id', $user->id)->first();
        if ($user->cannot('update', $product)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->productService->update($request);

        return response()->json($response);
    }

    /**
     * Change status like published, Unpublished of products
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function status(Request $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('viewAny', Product::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->productService->status($request);

        return response()->json($response);
    }

    /**
     * Delete product by logged brand
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $user = auth()->user();
        $product = Product::where('id', $request->id)->where('user_id', $user->id)->first();
        if ($user->cannot('delete', $product)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $response = $this->productService->delete($request);

        return response()->json($response);
    }

    /**

     * Delete product image by respected product and image id
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteImage(Request $request): JsonResponse
    {
        $user = auth()->user();

        $image = Product::where('id', $request->product_id)->where('user_id', $user->id)->first();

        if ($user->cannot('delete', $image)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->productService->deleteImage($request);

        return response()->json($response);
    }

    /**

     * Delete product video by respected product and image id
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteVideo(Request $request): JsonResponse
    {
        $user = auth()->user();
        $video = Product::where('id', $request->product_id)->where('user_id', $user->id)->first();

        if ($user->cannot('delete', $video)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->productService->deleteVideo($request);

        return response()->json($response);
    }

    /**
     * Update product sorting by logged brand
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reorderProduct(Request $request): JsonResponse

    {
        $user = auth()->user();
        if ($user->cannot('viewAny', Product::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->productService->reorderProduct($request);
        return response()->json($response);
    }

    /**
     * Inventory stock by product
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateStock(Request $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('viewAny', Product::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->productService->updateStock($request);
        return response()->json($response);
    }

    /**
     * Convert price from api for other currency from USD
     *
     * @param Request $request
     * @param $price
     * @return JsonResponse
     */
    public function convertPrice(Request $request, $price): JsonResponse
    {
        $response = '';
        $req_url = 'https://api.exchangerate.host/latest?base=USD&symbols=USD,CAD,GBP,AUD,EUR&places=2&amount=' . $price;
        $response_json = file_get_contents($req_url);
        if (false !== $response_json) {
            try {
                $response_obj = json_decode($response_json);
                if ($response_obj->success === true) {
                    $response = ['res' => true, 'msg' => "", 'data' => $response_obj->rates];
                }
            } catch (\Exception $e) {
                $response = ['res' => false, 'msg' => "Something went wrong", 'data' => ""];
            }
        }

        return response()->json($response);
    }


}
