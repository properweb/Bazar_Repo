<?php

namespace Modules\Backend\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\User\Entities\User;
use Modules\Product\Entities\Products;
use Modules\Order\Entities\Order;
use Session;

class DashboardController extends Controller
{

    public function index()
    {
        if (!Session::has('AdminId')) {
            return redirect('/backend');
        }
        $totalBrand = User::where('role', 'brand')->get()->count();
        $totalRetailer = User::where('role', 'retailer')->get()->count();
        $totalProduct = Products::where('status', 'publish')->get()->count();
        $totalOrder = Order::get()->count();
        $counter = range(1, 12);
        $totalBrandByMonth = array();
        $totalRetailerByMonth = array();
        $totalOrderByMonth = array();
        foreach ($counter as $v) {
            $totalBrandByMonth[] = User::where('role', 'brand')->whereMonth('created_at', '=', $v)->whereYear('created_at', '=', date('Y'))->get()->count();
            $totalRetailerByMonth[] = User::where('role', 'retailer')->whereMonth('created_at', '=', $v)->whereYear('created_at', '=', date('Y'))->get()->count();
            $totalOrderByMonth[] = Order::whereMonth('created_at', '=', $v)->whereYear('created_at', '=', date('Y'))->get()->count();
        }

        return view('backend::Dashboard', ['totalBrand' => $totalBrand, 'totalRetailer' => $totalRetailer, 'totalProduct' => $totalProduct, 'totalOrder' => $totalOrder, 'totalBrandByMonth' => implode(", ", $totalBrandByMonth), 'totalRetailerByMonth' => implode(", ", $totalRetailerByMonth), 'totalOrderByMonth' => implode(", ", $totalOrderByMonth)]);
    }

}
