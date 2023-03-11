<?php

namespace Modules\Brand\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Modules\Brand\Http\Requests\StoreBrandRequest;
use Modules\Brand\Http\Requests\UpdateBrandRequest;
use Modules\Brand\Http\Requests\UpdateBrandAccountRequest;
use Modules\Brand\Http\Requests\UpdateBrandShopRequest;
use Modules\Brand\Http\Services\BrandService;
use Modules\Brand\Entities\Brand;


class BrandController extends Controller
{

    private $brandAbsPath = "";

    private $brandRelPath = "";

    private BrandService $brandService;

    public function __construct(BrandService $brandService)
    {
        $this->brandService = $brandService;
        $this->brandAbsPath = public_path('uploads/brands');
        $this->brandRelPath = 'uploads/brands/';
    }


    /**
     * Get list of brands
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $response = $this->brandService->getBrands($request);

        return response()->json($response);
    }

    /**
     * Store a newly created brand in storage
     *
     * @param StoreBrandRequest $request
     * @return JsonResponse
     */
    public function store(StoreBrandRequest $request): JsonResponse
    {
        $response = $this->brandService->store($request->validated());

        return response()->json($response);

    }

    /**
     * Store a newly created brand in storage
     *
     * @param UpdateBrandRequest $request
     * @return JsonResponse
     */
    public function update(UpdateBrandRequest $request): JsonResponse
    {
        $response = $this->brandService->update($request);

        return response()->json($response);

    }

    /**
     * Fetch the specified Brand's account details.
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function show(int $userId): JsonResponse
    {
        $response = $this->brandService->get($userId);

        return response()->json($response);
    }

    /**
     * Fetch the specified Brand's shop details.
     *
     * @param string $brandKey
     * @return JsonResponse
     */
    public function showShop(string $brandKey): JsonResponse
    {
        $response = $this->brandService->getShop($brandKey);

        return response()->json($response);
    }

    /**
     * Update account details of the specified Brand.
     *
     * @param UpdateBrandAccountRequest $request
     * @return JsonResponse
     */
    public function updateAccount(UpdateBrandAccountRequest $request): JsonResponse
    {
        $user = auth()->user();

        $brand = Brand::where('user_id', $request->user_id)->first();

        // return error if no Brand found
        if (!$brand) {
            return [
                'res' => false,
                'msg' => 'Brand not found !',
                'data' => ""
            ];
        }

        // return error if user can not update the brand
        if ($user->cannot('update', $brand)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->brandService->updateAccount($request->validated());

        return response()->json($response);
    }

    /**
     * Update shop details of the specified Brand.
     *
     * @param UpdateBrandShopRequest $request
     * @return JsonResponse
     */
    public function updateShop(UpdateBrandShopRequest $request): JsonResponse
    {
        $user = auth()->user();
        $brand = Brand::where('user_id', $request->user_id)->first();

        // return error if no Brand found
        if (!$brand) {
            return [
                'res' => false,
                'msg' => 'Brand not found !',
                'data' => ""
            ];
        }

        // return error if user can not update the brand
        if ($user->cannot('update', $brand)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }
        $request->brand_slug = Str::slug($request->brand_name, '-');

        $response = $this->brandService->updateShop($request->validated());

        return response()->json($response);
    }

    /**
     * Update the specified Brand's status to live.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function liveShop(Request $request): JsonResponse
    {

        $user = auth()->user();
        $brand = Brand::where('user_id', $request->user_id)->first();

        // return error if no Brand found
        if (!$brand) {
            return [
                'res' => false,
                'msg' => 'Brand not found !',
                'data' => ""
            ];
        }

        // return error if user can not update the brand
        if ($user->cannot('update', $brand)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->brandService->liveShop($brand->id);

        return response()->json($response);
    }

    /**
     * Get count of brands.
     *
     * @return JsonResponse
     */
    public function count(): JsonResponse
    {
        $brandCount = Brand::where('go_live', 2)->count();
        $response = ['res' => true, 'msg' => "", 'data' => $brandCount];

        return response()->json($response);
    }

}
