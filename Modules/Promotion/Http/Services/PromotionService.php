<?php

namespace Modules\Promotion\Http\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
     * @param array $request
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
        $user = User::find($promotionData['user_id']);
        // return error if no user or user is not brand
        if (!$user || $user->role !== 'brand') {
            return [
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ];
        }
        //create promotion
        $promotion = new Promotion();
        $promotion->user_id = $promotionData['user_id'];
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
        if (!empty($promotionData['products'])) {
            $productsStr = implode(',', $promotionData["products"]);
        }
        $promotion->products = $productsStr;
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
        $user = User::find($requestData->user_id);
        // return error if no user or user is not brand
        if (!$user || $user->role !== 'brand') {
            return [
                'res' => false,
                'msg' => 'No record found !',
                'data' => ""
            ];
        }

        $brand = Brand::where('user_id', $user->id)->first();
        $allPromotionsCount = Promotion::where('user_id', $user->id)->count();
        $draftPromotionsCount = Promotion::where('user_id', $user->id)->where('status', 'draft')->count();
        $scheduledPromotionsCount = Promotion::where('user_id', $user->id)->where('status', 'schedule')->count();
        $completedPromotionsCount = Promotion::where('user_id', $user->id)->where('status', 'completed')->count();
        $promotions = Promotion::where('user_id', $user->id);
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
     * @param int $userId
     * @param string $promotionKey
     * @return array
     */
    public function get($userId, $promotionKey)
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
     * @param array $request
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
            // return error if user not created the promotion
            if ($user->id !== $promotion->user_id) {
                return [
                    'res' => false,
                    'msg' => 'User is not authorized !',
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

        } catch (\Exception $e) {
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
