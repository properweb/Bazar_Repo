<?php

namespace Modules\Promotion\Http\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Promotion\Entities\Promotion;


class PromotionService
{
    protected Promotion $promotion;

    protected Brand $brand;

    /**
     * Save order
     *
     * @param array $request
     * @return array
     */
    public function store(array $requestData): array
    {
        DB::beginTransaction();

        try {
            $this->promotion = $this->createPromotion($requestData);

            $response = [
                'res' => true,
                'msg' => 'Your promotion created successfully',
                'data' => ""
            ];

            DB::commit();
            //todo Log successfull creation
        } catch (\Exception $e) {
            // something went wrong
            //todo Log exception
            DB::rollback();
            $response = [
                'res' => false,
                'msg' => 'Someting went wrong !',
                'data' => ""
            ];

        }

        return $response;
    }

    /**
     * Create new promotion
     *
     * @param  array  $promotionData
     * @return Promotion
     */
    public function createPromotion(array $promotionData): Promotion
    {
        //create promotion
        $promotion = new Promotion();
        $promotion->title = $promotionData['title'];
        $promotion->from_date = $promotionData['from_date'];
        $promotion->to_date = $promotionData['to_date'];
        $promotion->promotion_to = $promotionData['promotion_to'];
        $promotion->promotion_country = $promotionData['promotion_country'];
        $promotion->promotion_tier = $promotionData['promotion_tier'];
        $promotion->promotion_offer_type = $promotionData['promotion_offer_type'];
        $promotion->order_amount = $promotionData['order_amount'];
        $promotion->discount_amount = $promotionData['discount_amount'];
        $promotion->brand_id = $promotionData['brand_id'];
        $promotion->offer_free_shipping = $promotionData['offer_free_shipping'];
        $promotion->create_date = date('Y-m-d');
        $promotion->product_id = $promotionData['product_id'];
        $promotion->save();

        return $promotion;
    }

    /**
     * Get all Promotions
     *
     * @return array
     */
    public function getPromotions($requestData): array
    {
        $user = User::find($requestData->user_id);
        if ($user) {
            $brand = Brand::where('user_id', $user->id)->first();
            $allPromotionsCount = Promotion::where('brand_id', $brand->user_id)->count();
            $draftPromotionsCount = Promotion::where('brand_id', $brand->user_id)->where('status', 'draft')->count();
            $scheduledPromotionsCount = Promotion::where('brand_id', $brand->user_id)->where('status', 'schedule')->count();
            $completedPromotionsCount = Promotion::where('brand_id', $brand->user_id)->where('status', 'completed')->count();
            $promotions = Promotion::where('brand_id', $brand->user_id);
            $status = strtolower($request->status);
            switch ($status) {
                case 'all':
                    break;
                default:
                    $promotions->where('status', $status);
                    break;
            }
            $ppromotions = $promotions->paginate(10);
            $rpromotions = [];
            if ($ppromotions) {
                foreach ($ppromotions as $promotion) {
                    $rpromotions[] = array(
                        'title' => $promotion->title,
                        'promotion_key' => $promotion->promotion_key,
                        'updated_at' => date("F j, Y, g:i a", strtotime($promotion->updated_at)),
                    );
                }
            }
            $data = array(
                "promotions" => $rpromotions,
                "allPromotionsCount" => $allPromotionsCount,
                "draftPromotionsCount" => $draftPromotionsCount,
                "scheduledPromotionsCount" => $scheduledPromotionsCount,
                "completedPromotionsCount" => $completedPromotionsCount,
            );
            $response = ['res' => true, 'msg' => "", 'data' => $data];
        } else {
            $response = ['res' => false, 'msg' => "No record found", 'data' => ""];
        }
        
        

        return $response;
    }

}
