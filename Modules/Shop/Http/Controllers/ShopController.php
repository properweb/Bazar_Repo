<?php

namespace Modules\Shop\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Product\Entities\Product;
use Modules\Shop\Http\Services\ShopService;


class ShopController extends Controller
{
    private ShopService $shopService;

    public function __construct(ShopService $shopService)
    {
        $this->shopService = $shopService;
    }

    /**
     * Get list of products by brand
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function fetchBrandProducts(Request $request): JsonResponse
    {
        $response = $this->shopService->getBrandProducts($request);

        return response()->json($response);
    }

    /**
     * Get list of products by category
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function fetchCategoryProducts(Request $request): JsonResponse
    {
        $response = $this->shopService->getCategoryProducts($request);

        return response()->json($response);
    }

    /**
     * Get list of filters for product listing
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function fetchProductFilters(Request $request): JsonResponse
    {
        $response = $this->shopService->getProductFilters($request);

        return response()->json($response);
    }

    /**
     * Fetch Product details
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function fetchProductDetail(Request $request): JsonResponse
    {
        $product = Product::where('product_key', $request->id)->first();

        // return error if no product found
        if (!$product) {
            return response()->json([
                'res' => false,
                'msg' => 'Product not found !',
                'data' => ""
            ]);
        }

        $response = $this->shopService->getProduct($product->id);

        return response()->json($response);
    }

    /**
     * Get list of newly added brands
     *
     * @return JsonResponse
     */
    public function fetchNewBrands(): JsonResponse
    {
        $response = $this->shopService->getNewBrands();

        return response()->json($response);
    }

    /**
     * Get list of testimonials
     *
     * @return JsonResponse
     */
    public function fetchTestimonials(): JsonResponse
    {
        $response = $this->shopService->getTestimonials();

        return response()->json($response);
    }

    /**
     * Get list of testimonials
     *
     * @return JsonResponse
     */
    public function fetchTrendingCategories(): JsonResponse
    {
        $response = $this->shopService->getTrendingCategories();

        return response()->json($response);
    }

    /**
     * Fetch brand's reviews.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function fetchBrandReviews(Request $request): JsonResponse
    {
        $response = $this->shopService->getBrandReviews($request);

        return response()->json($response);
    }
}
