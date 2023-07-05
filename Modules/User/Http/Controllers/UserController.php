<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\User\Http\Requests\LoginUserRequest;
use Modules\User\Http\Requests\ResetPasswordRequest;
use Modules\User\Http\Requests\ExistsEmailRequest;
use Modules\User\Http\Services\UserService;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Sign in request by user.
     *
     * @param LoginUserRequest $request
     * @return JsonResponse
     */
    public function login(LoginUserRequest $request): JsonResponse
    {
        $response = $this->userService->login($request->validated());

        return response()->json($response);
    }

    /**
     * Forget password request by user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function forgetPassword(Request $request): JsonResponse
    {
        $response = $this->userService->forgetPassword($request);

        return response()->json($response);
    }

    /**
     * Reset password request by user.
     *
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {

        $response = $this->userService->resetPassword($request->validated());

        return response()->json($response);
    }

    /**
     * Check a new email's existence in storage.
     *
     * @param ExistsEmailRequest $request
     * @return JsonResponse
     */
    public function checkEmail(ExistsEmailRequest $request): JsonResponse
    {
        $request->validated();

        return response()->json(['res' => true, 'msg' => "", 'data' => '']);
    }


}
