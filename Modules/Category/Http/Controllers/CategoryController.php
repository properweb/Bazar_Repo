<?php

namespace Modules\Category\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Category\Http\Services\CategoryService;


class CategoryController extends Controller
{
    private CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Get list of categories
     *
     * @return JsonResponse
     */
    public function fetchCategories(): JsonResponse
    {
        $response = $this->categoryService->getCategories();

        return response()->json($response);
    }

    /**
     * Get list of categories featured in home page
     *
     * @return JsonResponse
     */
    public function fetchFeaturedCategories(): JsonResponse
    {
        $response = $this->categoryService->getFeaturedCategories();

        return response()->json($response);
    }

    /**
     * Get list of categories shown in product
     *
     * @return JsonResponse
     */
    public function fetchProductCategories(): JsonResponse
    {
        $response = $this->categoryService->getProductCategories();

        return response()->json($response);
    }

    /**
     * Get list of only main categories
     *
     * @return JsonResponse
     */
    public function fetchParentCategories(): JsonResponse
    {
        $response = $this->categoryService->getParentCategories();

        return response()->json($response);
    }
}
