<?php

namespace Modules\Promotion\Http\Services;

use Illuminate\Support\Str;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariation;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Promotion\Entities\Promotion;
use Modules\Promotion\Entities\PromotionProduct;
use Modules\Promotion\Entities\Feature;
use Modules\Promotion\Entities\PromotionFeature;
use Carbon\Carbon;

class PromotionService
{
    protected Promotion $promotion;

    protected User $user;

    /**
     * Save a new promotion
     *
     * @param array $requestData
     * @return array
     */
    public function store(array $requestData): array
    {

        $this->promotion = $this->createPromotion($requestData);

        return [
            'res' => true,
            'msg' => 'Your promotion created successfully',
            'data' => ""
        ];

    }

    /**
     * Create new promotion
     *
     * @param array $promotionData
     * @return Promotion
     */
    public function createPromotion(array $promotionData): Promotion
    {
        $user = auth()->user();
        $promotionData["user_id"] = $user->id;
        $promotionData["status"] = Promotion :: STATUS_ACTIVE;
        $promotionData['promotion_key'] = 'bpc_' . Str::lower(Str::random(10));

        //create promotion
        $promotion = new Promotion();
        $promotion->save();
        $promotion->fill($promotionData);
        $promotion->save();

        if (!empty($promotionData['products'])) {
            foreach ($promotionData['products'] as $product) {
                //store promotion's product
                $promotionProductData = [];
                $promotionProduct = new PromotionProduct();
                $promotionProductData['promotion_id'] = $promotion->id;
                $promotionProductData['product_id'] = $product['id'];
                $promotionProduct->fill($promotionProductData);
                $promotionProduct->save();
            }
        }

        //store promotion's featured ad.
        $promotionFeature = new PromotionFeature();
        $promotionFeatureData['promotion_id'] = $promotion->id;
        $promotionFeatureData['feature_id'] = $promotionData['feature_id'];
        $promotionFeature->fill($promotionFeatureData);
        $promotionFeature->save();

        return $promotion;
    }

    /**
     * Get a listing of the promotions
     *
     * @param int $userId
     * @return array
     */
    public function getPromotions(int $userId): array
    {
        $brand = Brand::where('user_id', $userId)->first();

        $allPromotionsCount = Promotion::where('user_id', $userId)->count();
        $draftPromotionsCount = Promotion::where('user_id', $userId)->where('status', 'draft')->count();
        $scheduledPromotionsCount = Promotion::where('user_id', $userId)->where('status', 'schedule')->count();
        $completedPromotionsCount = Promotion::where('user_id', $userId)->where('status', 'completed')->count();
        $promotions = Promotion::where('user_id', $userId);
        $paginatedPromotions = $promotions->paginate(10);
        $filteredPromotions = [];
        if ($paginatedPromotions) {
            foreach ($paginatedPromotions as $promotion) {
                $filteredPromotions[] = array(
                    'title' => $promotion->title,
                    'promotion_key' => $promotion->promotion_key,
                    'promotion_type' => $promotion->promotion_type,
                    'dates' => date("F j, Y", strtotime($promotion->from_date)).' - '.date("F j, Y", strtotime($promotion->to_date)),
                    'status' => $promotion->status,
                    'updated_at' => date("F j, Y, g:i a", strtotime($promotion->updated_at)),
                    'orders' => 0,
                );
            }
        }
        $data = array(
            "bazaar_direct_link" => $brand->bazaar_direct_link,
            "promotions" => $filteredPromotions,
            "allPromotionsCount" => $allPromotionsCount,
            "draftPromotionsCount" => $draftPromotionsCount,
            "scheduledPromotionsCount" => $scheduledPromotionsCount,
            "completedPromotionsCount" => $completedPromotionsCount,
        );
        return ['res' => true, 'msg' => "", 'data' => $data];

    }

    /**
     * Get the specified promotion
     *
     * @param string $promotionKey
     * @return array
     */
    public function get(string $promotionKey): array
    {
        $promotion = Promotion::where('promotion_key', $promotionKey)->first();

        // return error if no promotion found
        if (!$promotion) {
            return [
                'res' => false,
                'msg' => 'Promotion not found !',
                'data' => ""
            ];
        }

        $promotion->country = explode(',', $promotion->country);
        $promotion->from_date_str = date("l,F j, Y", strtotime($promotion->from_date));
        $promotion->to_date_str = date("l,F j, Y", strtotime($promotion->to_date));
        $promotion->updated_at_str = date("F j, Y, g:i a", strtotime($promotion->updated_at));
        $date = $promotion->to_date;
        $diff = now()->diffInDays(Carbon::parse($date));
        $promotion->remaining_days = $diff;
        $promotionProducts = [];
        $products = PromotionProduct::where('promotion_id', $promotion->id)->get();

        if ($products) {
            foreach ($products as $product) {
                $productDetails = Product::find($product->product_id);
                $usdWholesalePrice = $productDetails->usd_wholesale_price ?? 0;
                $usdRetailPrice = $productDetails->usd_retail_price ?? 0;
                $productOptionsCount = ProductVariation::where('product_id', $product->product_id)->where('status', '1')->count();
                if ($productOptionsCount > 0) {
                    $productFirstVariation = ProductVariation::where('product_id', $product->product_id)->where('status', '1')->first();
                    $usdWholesalePrice = $productFirstVariation->price ?? 0;
                    $usdRetailPrice = $productFirstVariation->retail_price ?? 0;
                }
                $promotionProducts[] = array(
                    'name' => $productDetails->name,
                    'usd_wholesale_price' => $usdWholesalePrice,
                    'usd_retail_price' => $usdRetailPrice,
                    'featured_image' => $productDetails->featured_image,
                );

            }
        }
        $promotion->products = $promotionProducts;

        return [
            'res' => true,
            'msg' => '',
            'data' => $promotion
        ];
    }

    /**
     * Update the specified promotion in storage.
     *
     * @param array $requestData
     * @return array
     */
    public function update(array $requestData): array
    {

        $promotion = Promotion::where('promotion_key', $requestData['promotion_key'])->first();

        // return error if no promotion found
        if (!$promotion) {
            return [
                'res' => false,
                'msg' => 'Promotion not found !',
                'data' => ""
            ];
        }

        $productsStr = '';
        if (!empty($requestData['products'])) {
            $productsStr = implode(',', $requestData["products"]);
        }
        $requestData['products'] = $productsStr;
        $promotion->update($requestData);
        return [
            'res' => true,
            'msg' => 'Your promotion updated successfully',
            'data' => $promotion
        ];
    }


    /**
     * Remove the specified promotion from storage.
     *
     * @param string $promotionKey
     * @return array
     */
    public function delete(string $promotionKey): array
    {
        $promotion = Promotion::where('promotion_key', $promotionKey)->first();

        // return error if no promotion found
        if (!$promotion) {
            return [
                'res' => false,
                'msg' => 'Promotion not found !',
                'data' => ""
            ];
        }

        $promotion->status = Promotion::STATUS_DEACTIVATED;
        $promotion->save();

        //$promotion->delete();

        return [
            'res' => true,
            'msg' => 'Promotion successfully deleted',
            'data' => ""
        ];

    }

    /**
     * Get a listing of the promotion featured ads
     *
     * @return array
     */
    public function getFeatures(): array
    {
        $features = Feature::get();

        return ['res' => true, 'msg' => "", 'data' => $features];

    }

}
