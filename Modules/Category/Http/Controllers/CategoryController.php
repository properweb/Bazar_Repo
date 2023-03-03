<?php

namespace Modules\Category\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redis;
use Modules\Category\Entities\Category;
use DB;

class CategoryController extends Controller
{
    public function __construct()
    {
        Redis::connection();
    }

    public function index()
    {

        $existredis = Redis::exists("allcategory");
        if ($existredis > 0) {
            $cachedcategory = Redis::get("allcategory");
            $category = json_decode($cachedcategory, false);
            $response = ['res' => true, 'msg' => "", 'data' => $category];
        } else {
            $categories = DB::table('category AS r')
                ->leftJoin('category AS e', 'e.id', '=', 'r.parent_id')
                ->leftJoin('category AS l', 'r.id', '=', 'l.parent_id')
                ->select('e.name AS parent_name',
                    'e.id AS parent_id',
                    'r.id AS child_id',
                    'r.name AS child_name',
                    'l.id AS last_id',
                    'l.name AS last_name')
                ->where('l.parent_id', '>', 0)
                ->where('e.status', '=', 0)
                ->where('r.status', '=', 0)
                ->where('l.status', '=', 0)
                ->get();
            $allcategory = [];
            if (count($categories) > 0) {
                foreach ($categories as $var) {
                    $allcategory[] = array(
                        'category' => $var->parent_name . '->' . $var->child_name . '->' . $var->last_name,
                        'last_id' => $var->last_id
                    );
                }
            }
            $category = Redis::set('allcategory', json_encode($allcategory));
            $response = ['res' => true, 'msg' => "", 'data' => $allcategory];
        }


        return response()->json($response);
    }
    public function parentCategory() {

        $qry = Category::where('parent_id',0)->where('status',0)->orderBy('name', 'ASC')->get();
        $allCategory = array();
        if (count($qry) > 0) {
            foreach ($qry as $var) {
                $allCategory[] = array(
                    'category' => $var->name,
                    'cat_id' => $var->id
                );
            }
        }
        $response = ['res' => true, 'msg' => "", 'data' => $allCategory];
        return response()->json($response);
    }


}
