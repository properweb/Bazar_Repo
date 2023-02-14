<?php

namespace Modules\Promotion\Http\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Campaign\Entities\Campaign;
use Modules\User\Entities\User;
use Modules\Promotion\Entities\Promotion;
use Carbon\Carbon;

class PromotionService
{
    protected Promotion $promotion;

    protected User $user;

    /**
     * Save order
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
        $promotionData["status"] = Promotion :: STATUS_ACTIVE;
        $promotionData['promotion_key'] = 'bpc_' . Str::lower(Str::random(10));
        $productsStr = '';
        if (!empty($promotionData['products'])) {
            $productsStr = implode(',', $promotionData["products"]);
        }
        $promotionData['products'] = $productsStr;

        //create promotion
        $promotion = new Promotion();
        $promotion->save();
        $promotion->fill($promotionData);
        $promotion->save();

        return $promotion;
    }

    /**
     * Get a listing of the promotions
     *
     * @param $requestData
     * @return array
     */
    public function getPromotions($requestData): array
    {

        $brand = Brand::where('user_id', $requestData->user_id)->first();
        $allPromotionsCount = Promotion::where('user_id', $requestData->user_id)->count();
        $draftPromotionsCount = Promotion::where('user_id', $requestData->user_id)->where('status', 'draft')->count();
        $scheduledPromotionsCount = Promotion::where('user_id', $requestData->user_id)->where('status', 'schedule')->count();
        $completedPromotionsCount = Promotion::where('user_id', $requestData->user_id)->where('status', 'completed')->count();
        $promotions = Promotion::where('user_id', $requestData->user_id);
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
        $date = $promotion->from_date;
        $diff = now()->diffInDays(Carbon::parse($date));
        $promotion->remaining_days = $diff;
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

        try {
            $user = User::find($requestData['user_id']);
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
                'data' => $this->promotion
            ];

        } catch (Exception $e) {
            // something went wrong

            return [
                'res' => false,
                'msg' => $e->getMessage(),
                'data' => ""
            ];

        }
    }


    /**
     * Remove the specified promotion from storage.
     *
     * @param int $userId
     * @param string $promotionKey
     * @return array
     */
    public function delete($userId, $promotionKey)
    {
        $user = User::find($userId);
        $promotion = Promotion::where('promotion_key', $promotionKey)->first();

        // return error if no promotion found
        if (!$promotion) {
            return [
                'res' => false,
                'msg' => 'Promotion not found !',
                'data' => ""
            ];
        }
        // return error if user not created the promotion
        if ($user->id !== $promotion->user_id) {
            return [
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ];
        }
        $this->promotion->delete();

        return [
            'res' => true,
            'msg' => 'Promotion successfully deleted',
            'data' => ""
        ];

    }

}
