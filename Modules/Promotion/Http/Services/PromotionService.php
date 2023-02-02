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
        $promotion->brand_id = $promotionData['brand_id'];
        $promotion->free_shipping = $promotionData['free_shipping'];
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
            $status = strtolower($requestData->status);
            switch ($status) {
                case 'all':
                    break;
                default:
                    $promotions->where('status', '1');
                    break;
            }
            $paginatedPromotions = $promotions->paginate(10);
            $filteredCampaigns = [];
            if ($paginatedPromotions) {
                foreach ($paginatedPromotions as $promotion) {
                    $filteredCampaigns[] = array(
                        'title' => $promotion->title,
                        'promotion_key' => $promotion->promotion_key,
                        'updated_at' => date("F j, Y, g:i a", strtotime($promotion->updated_at)),
                    );
                }
            }
            $data = array(
                "bazaar_direct_link" => $brand->bazaar_direct_link,
                "promotions" => $filteredCampaigns,
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
     * Delete promotion
     *
     * @param array $request
     * @return array
     */
    public function delete( $promotionKey) 
    {
        $promotion = Promotion::where('promotion_key', $promotionKey)->first();
        

        // return error if no promotion found
        if (empty($promotion)) {
            return [
                'res' => false,
                'msg' => 'No record found !',
                'data' => ""
            ];
        }

        DB::beginTransaction();

        try {
            $this->promotion = Promotion::where('promotion_key', $promotionKey)->first();

            $this->deleteCampaign($promotion);

            
            $response = [
                'res' => true,
                'msg' => 'Campaign successfully deleted',
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
                'msg' => 'Error while deleting promotion !',
                'data' => ""
            ];

        }

        return $response;
    }
    
    /**
     * @param Campaign|null $existingCampaign
     */
    private function deleteCampaign(DeleteCampaign $deleteCampaign): void
    {
        $deleteCampaign->delete();
    }

}
