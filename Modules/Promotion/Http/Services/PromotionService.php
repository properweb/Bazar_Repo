<?php

namespace Modules\Promotion\Http\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Promotion\Entities\Promotion;
use Carbon\Carbon;

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

        $this->promotion = $this->createPromotion($requestData);

        $response = [
            'res' => true,
            'msg' => 'Your promotion created successfully',
            'data' => ""
        ];

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
        $promotion->brand_id = $promotionData['brand_id'];
        $promotion->promotion_key = 'bpc_' . Str::lower(Str::random(10));
        $promotion->title = $promotionData['title'];
        $promotion->from_date = $promotionData['from_date'];
        $promotion->to_date = $promotionData['to_date'];
        $promotion->type = $promotionData['type'];
        $promotion->country = $promotionData['country'];
        $promotion->tier = $promotionData['tier'];
        $promotion->discount_type = $promotionData['discount_type'];
        $promotion->ordered_amount = $promotionData['ordered_amount'];
        $promotion->discount_amount = $promotionData['discount_amount'];
        $promotion->free_shipping = $promotionData['free_shipping'];
        $promotion->create_date = date('Y-m-d');
        $productsStr = '';
        if(!empty($promotionData['products'])){
            
        }
        $promotion->products = $productsStr;
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
            $paginatedPromotions = $promotions->paginate(10);
            $filteredPromotions = [];
            if ($paginatedPromotions) {
                foreach ($paginatedPromotions as $promotion) {
                    $filteredPromotions[] = array(
                        'title' => $promotion->title,
                        'promotion_key' => $promotion->promotion_key,
                        'updated_at' => date("F j, Y, g:i a", strtotime($promotion->updated_at)),
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
            $response = ['res' => true, 'msg' => "", 'data' => $data];
        } else {
            $response = ['res' => false, 'msg' => "No record found", 'data' => ""];
        }
        
        

        return $response;
    }
    
    /**
     * Get specified Promotion
     *
     * @return array
     */
    public function get($promotionKey)
    {
        $promotion = Promotion::where('promotion_key', $promotionKey)->first();
        if ($promotion) {
            $promotion->country = explode(',', $promotion->country);
            $promotion->from_date_str = date("l,F j, Y", strtotime($promotion->from_date));
            $promotion->to_date_str = date("l,F j, Y", strtotime($promotion->to_date));
            $promotion->updated_at_str = date("F j, Y, g:i a", strtotime($promotion->updated_at));
            $date = $promotion->from_date;
            $diff = now()->diffInDays(Carbon::parse($date));
            $promotion->remaining_days = $diff;
            $response = ['res' => true, 'msg' => "", 'data' => $promotion];
        } else {
            $response = ['res' => false, 'msg' => "No record found", 'data' => ""];
        }
        return $response;
    }
    
    /**
     * update promotion
     *
     * @param array $request
     * @return array
     */
    public function update(array $requestData): array
    {
        DB::beginTransaction();

        try {
            $this->promotion = Promotion::where('promotion_key', $requestData['promotion_key'])->first();
            $productsStr = '';
            if(!empty($requestData['products'])){

            }
            $requestData['products'] = $productsStr;
            $this->promotion = $this->updatePromotion($requestData,$this->promotion);
            
            $response = [
                'res' => true,
                'msg' => 'Your promotion updated successfully',
                'data' => $this->promotion
            ];

            DB::commit();
            //todo Log successfull creation
        } catch (\Exception $e) {
            // something went wrong
            //todo Log exception
            DB::rollback();
            $response = [
                'res' => false,
                'msg' => $e->getMessage(),
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
    public function updatePromotion(array $promotionData, Promotion $promotion): Promotion
    {
        $promotion->update($promotionData);

        return $promotion;
    }
    
    /**
     * Delete promotion
     *
     * @param array $request
     * @return array
     */
    public function delete(array $requestData): array
    {
        $promotion = Promotion::where('promotion_key', $requestData['promotion_key'])->first();
        

        // return error if no promotion found
        if (empty($promotion)) {
            return [
                'res' => false,
                'msg' => 'No record found !',
                'data' => ""
            ];
        }
        
        $this->promotion = Promotion::findOrFail($promotion->id);
        $this->promotion->delete();
        $response = [
                'res' => true,
                'msg' => 'Your promotion deleted successfully',
                'data' => $this->promotion
            ];
        return $response;
    }
    
}
