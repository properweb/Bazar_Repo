<?php

namespace Modules\Backend\Http\Controllers;


use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use DB;
use Illuminate\Routing\Redirector;
use Session;
use Modules\User\Entities\User;

/**
 *
 */
class VendorController extends Controller
{

    /**
     * @return View|Factory|Redirector|RedirectResponse|Application
     */
    public function index(): View|Factory|Redirector|RedirectResponse|Application
    {
        if (!Session::has('AdminId')) {
            return redirect('/');
        }
        return view('backend::vendorlist');
    }

    /**
     * @return Application|RedirectResponse|Redirector|void
     */
    public function vendorList()
    {
        if (!Session::has('AdminId')) {
            return redirect('/');
        }
        $qry = User::select('users.first_name', 'users.last_name', 'users.about', 'users.gender', 'users.email', 'users.youtube', 'users.active', 'users.country_id', 'category.name as category_name', 'countries.name as country_name', 'brands.id', 'brands.brand_name', 'brands.website_url', 'brands.num_products_sell', 'brands.num_store', 'brands.country_code', 'brands.phone_number', 'brands.language', 'brands.established_year', 'brands.first_order_min', 'brands.re_order_min', 'brands.bazaar_direct_link', 'brands.featured_image', 'brands.profile_photo', 'brands.cover_image', 'brands.logo_image', 'brands.user_id', 'brands.go_live', 'users.created_at', DB::raw("CASE
    WHEN users.active='0' THEN 'Inactive'
    ELSE 'Active'
END AS active_status,
CASE
    WHEN brands.go_live='0' THEN 'Not Requested Yet'
    WHEN brands.go_live='1' THEN 'Waiting For Approval'
    ELSE 'Live'
END AS live_status"))
            ->join('brands', 'users.id', '=', 'brands.user_id')
            ->join('category', 'category.id', '=', 'brands.prime_cat')
            ->join('countries', 'countries.id', '=', 'brands.country')
            ->where('users.role', '=', 'brand')
            ->orderBy('users.id', 'desc')
            ->get();

        $count = 1;
        foreach ($qry as $items) {
            $data[] = array(
                'slno' => $count,
                'name' => $items->first_name . ' ' . $items->last_name,
                'email' => $items->email,
                'gender' => $items->gender,
                'created_at' => date("D M, Y", strtotime($items->created_at)),
                'active' => $items->active,
                'active_status' => $items->active_status,
                'go_live' => $items->go_live,
                'live_status' => $items->live_status,
                'prim_cat' => $items->category_name,
                'country' => $items->country_name,
                'vendor_id' => $items->id,
                'user_id' => $items->user_id,
                'website_url' => $items->website_url,
                'brand_name' => $items->brand_name,
                'num_products_sell' => $items->num_products_sell,
                'phone_number' => $items->country_code . '-' . $items->phone_number,
                'language' => $items->language,
                'established_year' => $items->established_year,
                'featured_image' => $items->featured_image,
                'profile_photo' => $items->profile_photo,
                'cover_image' => $items->cover_image,
                'logo_image' => $items->logo_image

            );
            $count++;
        }
        echo $json_data = json_encode(array("data" => $data));
        exit;
    }

    /**
     * @param Request $request
     * @return View|Factory|Redirector|RedirectResponse|Application
     */
    public function vendorDetails(Request $request): View|Factory|Redirector|RedirectResponse|Application
    {
        if (!Session::has('AdminId')) {
            return redirect('/');
        }
        $vendorID = $request->id;
        $qry = User::select('users.first_name', 'users.last_name', 'users.about', 'users.gender', 'users.email', 'users.youtube', 'users.active', 'users.country_id', 'category.name as category_name', 'countries.name as country_name', 'brands.id', 'brands.brand_name', 'brands.website_url', 'brands.num_products_sell', 'brands.num_store', 'brands.country_code', 'brands.phone_number', 'brands.language', 'brands.established_year', 'brands.first_order_min', 'brands.re_order_min', 'brands.bazaar_direct_link', 'brands.featured_image', 'brands.profile_photo', 'brands.cover_image', 'brands.logo_image', 'brands.user_id', 'brands.go_live', 'brands.insta_handle', 'brands.avg_lead_time', 'brands.shared_brd_story', 'users.created_at', DB::raw("CASE
    WHEN users.active='0' THEN 'Inactive'
    ELSE 'Active'
END AS active_status,
CASE
    WHEN brands.go_live='0' THEN 'Not Requested Yet'
    WHEN brands.go_live='1' THEN 'Waiting For Approval'
    ELSE 'Live'
END AS live_status"))
            ->join('brands', 'users.id', '=', 'brands.user_id')
            ->join('category', 'category.id', '=', 'brands.prime_cat')
            ->join('countries', 'countries.id', '=', 'brands.country')
            ->where('users.id', '=', $vendorID)
            ->get();


        foreach ($qry as $items) {
            $data[] = array(

                'name' => $items->first_name . ' ' . $items->last_name,
                'email' => $items->email,
                'gender' => $items->gender,
                'created_at' => date("D M, Y", strtotime($items->created_at)),
                'active' => $items->active,
                'active_status' => $items->active_status,
                'go_live' => $items->go_live,
                'live_status' => $items->live_status,
                'prim_cat' => $items->category_name,
                'country' => $items->country_name,
                'vendor_id' => $items->id,
                'user_id' => $items->user_id,
                'website_url' => $items->website_url,
                'brand_name' => $items->brand_name,
                'num_products_sell' => $items->num_products_sell,
                'phone_number' => $items->country_code . '-' . $items->phone_number,
                'language' => $items->language,
                'established_year' => $items->established_year,
                'featured_image' => $items->featured_image,
                'profile_photo' => $items->profile_photo,
                'cover_image' => $items->cover_image,
                'logo_image' => $items->logo_image,
                'insta_handle' => $items->insta_handle,
                'avg_lead_time' => $items->avg_lead_time,
                'num_store' => $items->num_store,
                'first_order_min' => $items->first_order_min,
                're_order_min' => $items->re_order_min,
                'shared_brd_story' => $items->shared_brd_story


            );


        }
        return view('backend::VendorDetails', ['brand' => $data]);

    }


}
