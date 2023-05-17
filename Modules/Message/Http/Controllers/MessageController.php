<?php

namespace Modules\Message\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Message\Entities\Message;
use Modules\Message\Http\Requests\MessageRequest;
use Modules\Message\Http\Services\MessageService;

class MessageController extends Controller
{

    private MessageService $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    /**
     * Show all chat member
     *
     * @return JsonResponse
     */
    public function showMember(): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('viewAny', Message::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->messageService->showMember();

        return response()->json($response);
    }

    /**
     * Show all chat member
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function chatDetail(Request $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('viewAny', Message::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->messageService->chatDetail($request);

        return response()->json($response);
    }

    /**
     * Show Real time message
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function realChat(Request $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('viewAny', Message::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->messageService->realChat($request);

        return response()->json($response);
    }

    /**
     * Create chat
     *
     * @param MessageRequest $request
     * @return JsonResponse
     */
    public function create(MessageRequest $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('create', Message::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->messageService->create($request);

        return response()->json($response);
    }

    /**
     * Show chat
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function allChat(Request $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->cannot('create', Message::class)) {
            return response()->json([
                'res' => false,
                'msg' => 'User is not authorized !',
                'data' => ""
            ]);
        }

        $response = $this->messageService->allChat($request);

        return response()->json($response);
    }

}
