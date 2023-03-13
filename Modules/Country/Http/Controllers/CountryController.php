<?php

namespace Modules\Country\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Country\Entities\Country;
use DB;

class CountryController extends Controller
{


    public function index(Request $request)
    {

        $result_array = array();
        $countries = Country::orderBy('name', 'ASC')->get();;
        foreach ($countries as $v) {
            $result_array[] = array(
                'id' => $v->id,
                'country_code' => $v->shortname,
                'country_name' => $v->name,
                'phone_code' => $v->phonecode
            );
        }


        $response = ['res' => true, 'msg' => "", 'data' => $result_array];
        return response()->json($response);
    }

    public function state(Request $request)
    {
        $result_array = [];
        $states = DB::table('states')
            ->where('country_id', $request->country_id)
            ->orderBy('name', 'ASC')
            ->get();
        foreach ($states as $v) {
            $result_array[] = array(
                'id' => $v->id,
                'state_name' => $v->name
            );
        }
        $response = ['res' => true, 'msg' => "", 'data' => $result_array];
        return response()->json($response);
    }

    public function city(Request $request)
    {
        $result_array = [];

        $states = DB::table('cities')
            ->where('state_id', $request->state_id)
            ->orderBy('name', 'ASC')
            ->get();

        foreach ($states as $v) {
            $result_array[] = array(
                'id' => $v->id,
                'city_name' => $v->name
            );
        }

        $response = ['res' => true, 'msg' => "", 'data' => $result_array];
        return response()->json($response);
    }
    
    public function promotion(Request $request)
    {

        $result_array = array();
        $countries = Country::orderBy('name', 'ASC')->where('in_promotion','1')->get();
        foreach ($countries as $v) {
            $result_array[] = array(
                'id' => $v->id,
                'country_code' => $v->shortname,
                'country_name' => $v->name,
                'phone_code' => $v->phonecode
            );
        }


        $response = ['res' => true, 'msg' => "", 'data' => $result_array];
        return response()->json($response);
    }
}
