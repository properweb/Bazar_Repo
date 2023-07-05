<?php

namespace Modules\Analytic\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Order\Entities\Order;
use Modules\Analytic\Http\Requests\AnalyticRequest;
use Modules\Analytic\Http\Services\AnalyticService;

class AnalyticController extends Controller
{

    private AnalyticService $analyticService;

    public function __construct(AnalyticService $analyticService)
    {
        $this->analyticService = $analyticService;
    }


    /**
     * Show all order for logged user
     *
     * @return JsonResponse
     */
    public function orderCancel(): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('viewAny', Order::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->analyticService->orderCancel();

        return response()->json($response);
    }

    /**
     * Show all order for logged user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function topSelling(Request $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('viewAny', Order::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->analyticService->topSelling($request);

        return response()->json($response);
    }

    /**
     * Show total sales by date
     *
     * @param AnalyticRequest $request
     * @return JsonResponse
     */
    public function totalSales(AnalyticRequest $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('viewAny', Order::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->analyticService->totalSales($request);

        return response()->json($response);
    }

    /**
     * Show total sales by date
     *
     * @param AnalyticRequest $request
     * @return JsonResponse
     */
    public function totalSalesDates(AnalyticRequest $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('viewAny', Order::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->analyticService->totalSalesDates($request);

        return response()->json($response);
    }

    /**
     * Add visit of brand shop
     *
     * @param Request $request
     * @return mixed
     */
    public function shopVisit(Request $request): mixed
    {

        $response = $this->analyticService->shopVisit($request);

        return response()->json($response);
    }

    /**
     * Traffic Report by brand
     *
     * @param Request $request
     * @return mixed
     */
    public function traffic(Request $request): mixed
    {

        $response = $this->analyticService->traffic($request);

        return response()->json($response);
    }

    /**
     * Total traffic Report by brand
     *
     * @param Request $request
     * @return mixed
     */
    public function totalTraffic(Request $request): mixed
    {

        $response = $this->analyticService->totalTraffic($request);

        return response()->json($response);
    }

    /**
     * Show listing of orders with issues
     *
     * @return JsonResponse
     */
    public function orderIssues(): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('viewAny', Order::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->analyticService->fetchOrderIssues();

        return response()->json($response);
    }

    /**
     * Show sell through details
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sellThrough(Request $request): JsonResponse
    {

        $response = $this->analyticService->fetchSellThrough($request);

        return response()->json($response);
    }

}
