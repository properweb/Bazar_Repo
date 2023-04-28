<?php

namespace Modules\Message\Http\Services;


use Illuminate\Support\Str;
use Modules\Message\Entities\Message;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Order\Entities\Order;
use Modules\Retailer\Entities\Retailer;
use Illuminate\Support\Facades\DB;
use Modules\Wishlist\Entities\Board;


class MessageService
{
    protected Message $message;

    protected User $user;

    /**
     * Show chat user
     *
     * @return array
     */
    public function showMember(): array
    {

        $userId = auth()->user()->id;
        $user = auth()->user();
        $members = [];
        $response = '';
        if ($user) {
            if ($user->role === 'retailer') {
                $orders = Order::where('user_id', $userId)->groupBy('brand_id')->get();
                if (!empty($orders)) {
                    foreach ($orders as $order) {
                        $brand = Brand::where('user_id', $order->brand_id)->first();
                        $members[] = array(
                            'user' => $brand->brand_name,
                            'user_id' => $order->brand_id,
                            'role' => 'brand',
                            'logo' => $brand->logo_image != '' ? asset('public') . '/' . $brand->logo_image : asset('public/img/logo-image.png'),
                        );
                    }
                    $response = ['res' => true, 'msg' => "", 'data' => $members];
                } else {
                    $response = ['res' => false, 'msg' => "No user found", 'data' => ''];
                }
            }
            if ($user->role === 'brand') {

                $orders = Order::where('brand_id', $userId)->groupBy('user_id')->get();
                if (!empty($orders)) {
                    foreach ($orders as $order) {
                        $retailer = User::where('id', $order->user_id)->first();
                        $members[] = array(
                            'user' => $retailer->first_name . ' ' . $retailer->last_name,
                            'user_id' => $order->user_id,
                            'role' => 'retailer',
                            'logo' => '',
                        );
                    }
                    $response = ['res' => true, 'msg' => "", 'data' => $members];
                } else {
                    $response = ['res' => false, 'msg' => "No brand found", 'data' => ''];
                }
            }
        }

        return $response;

    }

    /**
     * Details of message
     *
     * @param object $request
     * @return array
     */
    public function chatDetail(object $request): array
    {


        $user = auth()->user();
        $userDetail = '';
        if ($user->role == 'retailer') {
            $brand = Brand::where('user_id', $request->user_id)->first();
            if (!empty($brand)) {
                $userDetail = array(
                    'user' => $brand->brand_name,
                    'user_id' => $request->user_id,
                    'logo' => $brand->logo_image != '' ? asset('public') . '/' . $brand->logo_image : asset('public/img/logo-image.png'),
                );


            }
        } else {
            $retailer = User::where('id', $request->user_id)->first();
            if (!empty($retailer)) {
                $userDetail = array(
                    'user' => $retailer->first_name . ' ' . $retailer->last_name,
                    'user_id' => $request->user_id,
                    'logo' => '',
                );
            }
        }

        return ['res' => true, 'msg' => "", 'data' => $userDetail];
    }

    /**
     * send message
     *
     * @param object $request
     * @return array
     */
    public function create(object $request): array
    {

        $message = new Message;
        $message->sender_id = $request->sender_id;
        $message->reciever_id = $request->reciever_id;
        $message->message = $request->message;
        $message->save();
        return ['res' => true, 'msg' => "", 'data' => ''];
    }

    /**
     * Details of message
     *
     * @param object $request
     * @return array
     */
    public function allChat(object $request): array
    {
        $userId = auth()->user()->id;
        $getUser = $request->user_id;

        $brandLogo = '';
        $brandName = '';
        $retailerName = '';
        $allDate = [];
        $getDate = Message::selectRaw("DATE_FORMAT(created_at, '%M %d, %Y') as date")
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                    ->orWhere('reciever_id', $userId);
            })
            ->where(function ($query) use ($getUser) {
                $query->where('sender_id', $getUser)
                    ->orWhere('reciever_id', $getUser);
            })
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%M %d, %Y')"))
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($getDate as $date) {
            $message = Message::selectRaw("sender_id,reciever_id,message,id")->where(DB::raw("DATE_FORMAT(created_at, '%M %d, %Y')"), $date->date)
                ->where(function ($query) use ($userId) {
                    $query->where('sender_id', $userId)
                        ->orWhere('reciever_id', $userId);
                })
                ->where(function ($query) use ($getUser) {
                    $query->where('sender_id', $getUser)
                        ->orWhere('reciever_id', $getUser);
                })
                ->orderBy('created_at', 'asc')
                ->get();

            $allMsg = [];
            foreach ($message as $mg)
            {
                $sender = User::where('id', $mg->sender_id)->first();
                $rec = User::where('id', $mg->reciever_id)->first();
                if ($sender->role == 'brand' || $rec->role == 'brand') {
                    $brand = Brand::where('user_id', $sender->id)
                        ->orWhere('user_id', $rec->id)
                        ->first();
                    $brandName = $brand->brand_name;
                    $brandLogo = $brand->logo_image != '' ? asset('public') . '/' . $brand->logo_image : asset('public/img/logo-image.png');
                }
                if ($sender->role == 'retailer' || $rec->role == 'retailer')
                {
                    $retailer = User::where('id', $sender->id)
                        ->orWhere('id', $rec->id)
                        ->first();
                    $retailerName = $retailer->first_name . ' ' . $retailer->last_name;
                }

                if($sender->role=='retailer' || $rec->role=='retailer')
                {
                    $senderRole = 'brand';
                    $recRole = 'retailer';
                }
                if($sender->role=='brand' || $rec->role=='brand')
                {
                    $recRole = 'retailer';
                    $senderRole = 'brand';
                }

                $allMsg[] = array(
                    'message' => $mg->message,
                    'senderRole' => $senderRole,
                    'recRole' => $recRole,
                    'brandLogo' => $brandLogo,
                    'brandName' => $brandName,
                    'retailerName' => $retailerName,
                    'logged_user' => $userId,
                    'sender' => $mg->sender_id
                );
            }

            $allDate[] = array(
                'date' => $date->date,
                'message' => $allMsg
            );
        }
        return ['res' => true, 'msg' => "", 'data' => $allDate];
    }

}
