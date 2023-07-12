<?php

namespace Modules\Message\Http\Services;


use Illuminate\Support\Str;
use Modules\Message\Entities\Message;
use Modules\User\Entities\User;
use Modules\Brand\Entities\Brand;
use Modules\Order\Entities\Order;
use Modules\Retailer\Entities\Retailer;
use Illuminate\Support\Facades\DB;



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
                $orders = Order::where('orders.user_id', $userId)
                    ->leftJoin('messages', 'messages.reciever_id', '=', 'orders.user_id')
                    ->groupBy('orders.brand_id')
                    ->orderBy('messages.created_at', 'DESC')->get();

                if (!empty($orders)) {
                    foreach ($orders as $order) {
                        $brand = Brand::where('user_id', $order->brand_id)->first();
                        $getUser = $order->brand_id;
                        $message = Message::selectRaw("sender_id,reciever_id,message,id,read_at")
                            ->where(function ($query) use ($userId) {
                                $query->where('sender_id', $userId)
                                    ->orWhere('reciever_id', $userId);
                            })
                            ->where(function ($query) use ($getUser) {
                                $query->where('sender_id', $getUser)
                                    ->orWhere('reciever_id', $getUser);
                            })
                            ->orderBy('created_at', 'DESC')
                            ->first();
                        if (!empty($message->read_at) && $userId==$message->sender_id) {
                            $status = 1;
                        } else {
                            $status = 0;
                        }
                        $members[] = array(
                            'user' => $brand->brand_name,
                            'user_id' => $order->brand_id,
                            'role' => 'brand',
                            'logo' => $brand->logo_image != '' ? asset('public') . '/' . $brand->logo_image : asset('public/img/logo-image.png'),
                            'message' => $message,
                            'status' => $status
                        );
                    }
                    $response = ['res' => true, 'msg' => "", 'data' => $members];
                } else {
                    $response = ['res' => false, 'msg' => "No user found", 'data' => ''];
                }
            }
            if ($user->role === 'brand') {

                $orders = Message::where('orders.brand_id', $userId)
                    ->leftJoin('orders', 'orders.brand_id', '=', 'messages.sender_id')
                    ->groupBy('orders.user_id')
                    ->orderBy('messages.created_at', 'DESC')
                    ->get();


                if (!empty($orders)) {
                    foreach ($orders as $order) {
                        $getUser = $order->user_id;
                        $message = Message::selectRaw("sender_id,reciever_id,message,id,read_at")
                            ->where(function ($query) use ($userId) {
                                $query->where('sender_id', $userId)
                                    ->orWhere('reciever_id', $userId);
                            })
                            ->where(function ($query) use ($getUser) {
                                $query->where('sender_id', $getUser)
                                    ->orWhere('reciever_id', $getUser);
                            })

                            ->orderBy('created_at', 'DESC')
                            ->first();

                        $retailer = User::where('id', $order->user_id)->first();
                        if (!empty($message->read_at) && $userId==$message->sender_id) {
                            $status = 1;
                        } else {
                            $status = 0;
                        }
                        $members[] = array(
                            'user' => $retailer->first_name . ' ' . $retailer->last_name,
                            'user_id' => $order->user_id,
                            'role' => 'retailer',
                            'logo' => '',
                            'message' => $message,
                            'status' => $status,
                            'created_at' => $order->created_at
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
        $userId = auth()->user()->id;
        $getUser = $request->user_id;
        Message::where(function ($query) use ($userId) {
            $query->where('sender_id', $userId)
                ->orWhere('reciever_id', $userId);
        })
            ->where(function ($query) use ($getUser) {
                $query->where('sender_id', $getUser)
                    ->orWhere('reciever_id', $getUser);
            })
            ->update([
                'read_at' => date('Y-m-d h:i:s')
            ]);

        if ($user->role == 'retailer') {
            $brand = Brand::where('user_id', $getUser)->first();
            if (!empty($brand)) {
                $userDetail = array(
                    'user' => $brand->brand_name,
                    'user_id' => $getUser,
                    'logo' => $brand->logo_image != '' ? asset('public') . '/' . $brand->logo_image : asset('public/img/logo-image.png'),
                    'brand_key' => $brand->brand_key
                );


            }
        } else {
            $retailer = User::where('id', $getUser)->first();
            $retailerDetails = Retailer::where('user_id', $getUser)->first();
            if (!empty($retailer)) {
                $userDetail = array(
                    'user' => $retailer->first_name . ' ' . $retailer->last_name,
                    'user_id' => $getUser,
                    'logo' => '',
                    'retailer_key' => $retailerDetails->retailer_key
                );
            }
        }

        return ['res' => true, 'msg' => "", 'data' => $userDetail];
    }

    /**
     * Show Real time message
     *
     * @param object $request
     * @return array
     */
    public function realChat(object $request): array
    {
        $userId = auth()->user()->id;
        $getUser = $request->user_id;

        $brandLogo = '';
        $brandName = '';
        $retailerName = '';
        $message = Message::selectRaw("sender_id,reciever_id,message,id")->whereNull('read_at')
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                    ->orWhere('reciever_id', $userId);
            })
            ->where(function ($query) use ($getUser) {
                $query->where('sender_id', $getUser)
                    ->orWhere('reciever_id', $getUser);
            })
            ->orderBy('created_at', 'asc')
            ->limit(1)
            ->get();

        $allMsg = [];
        $senderRole = '';
        $recRole ='';
        foreach ($message as $mg) {
            $sender = User::where('id', $mg->sender_id)->first();
            $rec = User::where('id', $mg->reciever_id)->first();
            if ($sender->role == 'brand' || $rec->role == 'brand') {
                $brand = Brand::where('user_id', $sender->id)
                    ->orWhere('user_id', $rec->id)
                    ->first();
                $brandName = $brand->brand_name;
                $brandLogo = $brand->logo_image != '' ? asset('public') . '/' . $brand->logo_image : asset('public/img/logo-image.png');
            }
            if ($sender->role == 'retailer' || $rec->role == 'retailer') {
                $retailer = User::where('id', $sender->id)
                    ->orWhere('id', $rec->id)
                    ->first();
                $retailerName = $retailer->first_name . ' ' . $retailer->last_name;
            }

            if ($sender->role == 'retailer' || $rec->role == 'retailer') {
                $senderRole = 'brand';
                $recRole = 'retailer';
            }
            if ($sender->role == 'brand' || $rec->role == 'brand') {
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
            Message::where('id', $mg->id)
                ->update(['read_at' => date('Y-m-d h:i:s')]);
        }

        return ['res' => true, 'msg' => "", 'data' => $allMsg];
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
        $message->read_at = date('Y-m-d h:i:s');
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
            foreach ($message as $mg) {
                $sender = User::where('id', $mg->sender_id)->first();
                $rec = User::where('id', $mg->reciever_id)->first();
                if ($sender->role == 'brand' || $rec->role == 'brand') {
                    $brand = Brand::where('user_id', $sender->id)
                        ->orWhere('user_id', $rec->id)
                        ->first();
                    $brandName = $brand->brand_name;
                    $brandLogo = $brand->logo_image != '' ? asset('public') . '/' . $brand->logo_image : asset('public/img/logo-image.png');
                }
                if ($sender->role == 'retailer' || $rec->role == 'retailer') {
                    $retailer = User::where('id', $sender->id)
                        ->orWhere('id', $rec->id)
                        ->first();
                    $retailerName = $retailer->first_name . ' ' . $retailer->last_name;
                }

                if ($sender->role == 'retailer' || $rec->role == 'retailer') {
                    $senderRole = 'brand';
                    $recRole = 'retailer';
                }
                if ($sender->role == 'brand' || $rec->role == 'brand') {
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
