<?php

namespace Modules\Country\Http\Services;

use Modules\Country\Entities\Country;
use Modules\Country\Entities\State;
use Modules\Country\Entities\City;

class CountryService
{
    
    /**
     * Get all country
     *
     * @return array
     */
    public function fetch(): array
    {
        $countries = Country::orderBy('name', 'ASC')->get();
        $data = array();
        foreach ($countries as $v) {
            $data[] = array(
                'id' => $v->id,
                'country_code' => $v->shortname,
                'country_name' => $v->name,
                'phone_code' => $v->phonecode
            );
        }
        $response = ['res' => true, 'msg' => '', 'data' => $data];

        return ($response);
    }

    /**
     * Get all state by county
     *
     * @param $request
     * @return array
     */
    public function state($request): array
    {
        $states = State::where('country_id',$request->country_id)->orderBy('name', 'ASC')->get();
        $data = array();
        foreach ($states as $v) {
            $data[] = array(
                'id' => $v->id,
                'state_name' => $v->name
            );
        }
        $response = ['res' => true, 'msg' => '', 'data' => $data];

        return ($response);
    }

    /**
     * Get all city by state
     *
     * @param $request
     * @return array
     */
    public function city($request): array
    {
        $cities = City::where('state_id',$request->state_id)->orderBy('name', 'ASC')->get();
        $data = array();
        foreach ($cities as $v) {
            $data[] = array(
                'id' => $v->id,
                'city_name' => $v->name
            );
        }
        $response = ['res' => true, 'msg' => '', 'data' => $data];

        return ($response);
    }

    /**
     * Get all promotion country
     *
     * @return array
     */
    public function promotion(): array
    {
        $countries = Country::orderBy('name', 'ASC')->where('in_promotion','1')->get();
        $data = array();
        foreach ($countries as $v) {
            $data[] = array(
                'id' => $v->id,
                'country_code' => $v->shortname,
                'country_name' => $v->name,
                'phone_code' => $v->phonecode
            );
        }
        $response = ['res' => true, 'msg' => '', 'data' => $data];

        return ($response);
    }
}
