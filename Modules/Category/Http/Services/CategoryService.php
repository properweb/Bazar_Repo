<?php

namespace Modules\Category\Http\Services;

use Illuminate\Support\Facades\DB;
use Modules\Category\Entities\Category;


class CategoryService
{

    public function __construct()
    {
    }

    /**
     * Get a listing of all product categories
     *
     * @return array
     */
    public function getCategories(): array
    {

        $mainCategories = Category::where('parent_id', 0)->where('status', '1')->get();
        if ($mainCategories) {
            foreach ($mainCategories as $mainCategory) {
                $resultCategories = [];
                $categories = Category::where('parent_id', $mainCategory->id)->where('status', '1')->get();
                if ($categories) {
                    foreach ($categories as $category) {
                        $resultSubCategories = [];
                        $subCategories = Category::where('parent_id', $category->id)->where('status', '1')->get();
                        if ($subCategories) {
                            foreach ($subCategories as $subCategory) {
                                $resultSubCategories[] = array(
                                    "id" => $subCategory->id,
                                    "title" => $subCategory->title,
                                );
                            }
                        }
                        $resultCategories[] = array(
                            "id" => $category->id,
                            "title" => $category->title,
                            "sub_categories" => $resultSubCategories,
                            "image" => $category->image != '' ? asset('public') . '/' . $category->image : asset('public/img/nav-category-image.png'),
                        );
                    }
                }
                $data[] = array(
                    "id" => $mainCategory->id,
                    "title" => $mainCategory->title,
                    "categories" => $resultCategories,
                );
            }
        }

        return ['res' => true, 'msg' => "", 'data' => $data];
    }

    /**
     * Get a listing of the product categories featured in home
     *
     * @return array
     */
    public function getFeaturedCategories(): array
    {

        $featuredCategories = Category::where('home_featured', 1)->where('status', '1')->get();
        if ($featuredCategories) {
            foreach ($featuredCategories as $featuredCategory) {
                $category = '';
                if ($featuredCategory->parent_id != 0) {
                    $parentCategory = Category::find($featuredCategory->parent_id);
                    if ($parentCategory->parent_id != 0) {
                        $parentParentCategory = Category::find($parentCategory->parent_id);
                        $mainCategory = $parentParentCategory->title;
                        $category = $parentCategory->title;
                        $categoryType = 'sub-category';
                    } else {
                        $category = $featuredCategory->title;
                        $mainCategory = $parentCategory->title;
                        $categoryType = 'category';
                    }
                } else {
                    $mainCategory = $featuredCategory->title;
                    $categoryType = 'main-category';
                }

                $data[] = array(
                    "id" => $featuredCategory->id,
                    "title" => $featuredCategory->title,
                    "main_category" => $mainCategory,
                    "category" => $category,
                    "cat_type" => $categoryType,
                    "image" => $featuredCategory->image != '' ? asset('public') . '/' . $featuredCategory->image : asset('public/img/featured-brand-image.png'),
                );

            }
        }

        return ['res' => true, 'msg' => "", 'data' => $data];
    }

    /**
     * Get a listing of all product categories shown in product pages
     *
     * @return array
     */
    public function getProductCategories(): array
    {

        $categories = DB::table('categories AS cat')
            ->leftJoin('categories AS main_cat', 'main_cat.id', '=', 'cat.parent_id')
            ->leftJoin('categories AS sub_cat', 'cat.id', '=', 'sub_cat.parent_id')
            ->select('main_cat.title AS main_cat_name',
                'main_cat.id AS main_cat_id',
                'cat.id AS cat_id',
                'cat.title AS cat_name',
                'sub_cat.id AS sub_cat_id',
                'sub_cat.title AS sub_cat_name')
            ->where('sub_cat.parent_id', '>', 0)
            ->where('main_cat.status', '=', 1)
            ->where('cat.status', '=', 1)
            ->where('sub_cat.status', '=', 1)
            ->get();
        $data = [];
        if (count($categories) > 0) {
            foreach ($categories as $var) {
                $data[] = array(
                    'category' => $var->main_cat_name . '->' . $var->cat_name . '->' . $var->sub_cat_name,
                    'last_id' => $var->sub_cat_id
                );
            }
        }

        return ['res' => true, 'msg' => "", 'data' => $data];
    }

    /**
     * Get a listing of only main product categories
     *
     * @return array
     */
    public function getParentCategories(): array
    {
        $data = [];
        $mainCategories = Category::where('parent_id', 0)->where('status', '1')->orderBy('title', 'ASC')->get();
        if ($mainCategories) {
            foreach ($mainCategories as $mainCategory) {
                $data[] = array(
                    'category' => $mainCategory->title,
                    'cat_id' => $mainCategory->id
                );
            }
        }

        return ['res' => true, 'msg' => "", 'data' => $data];
    }
}
