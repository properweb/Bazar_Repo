<?php

namespace Modules\Country\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Country\Http\Requests\StateRequest;
use Modules\Country\Http\Requests\CityRequest;
use Modules\Country\Http\Services\CountryService;

class CountryController extends Controller
{
    private CountryService $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }
    /**
     * Get All country
     *
     * @return mixed
     */
    public function index(): mixed
    {
        $response = $this->countryService->fetch();

        return response()->json($response);
    }

    /**
     * Get All state By country
     *
     * @param StateRequest $request
     * @return mixed
     */
    public function state(StateRequest $request): mixed
    {
        $response = $this->countryService->state($request);

        return response()->json($response);
    }

    /**
     * Get all city by state
     *
     * @param CityRequest $request
     * @return mixed
     */
    public function city(CityRequest $request): mixed
    {
        $response = $this->countryService->city($request);

        return response()->json($response);
    }

    /**
     * Get all promotion country
     *
     * @return mixed
     */
    public function promotion(): mixed
    {
        $response = $this->countryService->promotion();

        return response()->json($response);
    }
}
