<?php

namespace Modules\Backend\Http\Controllers;


use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Modules\User\Entities\User;
use Session;

class BackendController extends Controller
{

    /**
     * @return Factory|View|Application
     */
    public function index(): Factory|View|Application
    {
        return view('backend::index');
    }

    /**
     * @param Request $request
     * @return Redirector|Application|RedirectResponse
     */
    public function checkLogin(Request $request): Redirector|Application|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'email_address' => 'string|email|required',
            'password' => 'required|min:6',
        ]);
        if ($validator->fails()) {
            return back()->with('errors', $validator->errors()->first());
        } else {
            $user = User::where('email', $request->email_address)->where('role', 'admin')->first();
            if ($user) {
                if (Hash::check($request->password, $user->password)) {

                    $request->session()->put('AdminId', $user->id);
                    return redirect('backend/dashboard');
                } else {
                    return back()->with('errors', 'Your password is wrong');
                }
            } else {
                return back()->with('errors', 'Email address not found');
            }
        }

    }

    /**
     * @return Redirector|Application|RedirectResponse
     */
    public function logOut(): Redirector|Application|RedirectResponse
    {

        Session::forget('AdminId');
        return redirect('backend');

    }

    /**
     * @return Factory|View|Application
     */
    public function changePassword(): Factory|View|Application
    {
        return view('backend::ChangePassword');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = User::find($request->session()->get('AdminId'));
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:6|different:old_password',
            'confirm_password' => 'required|same:new_password'
        ]);
        if ($validator->fails()) {
            return back()->with('errors', $validator->errors()->first());
        } else {
            if (Hash::check($request->old_password, $user->password)) {
                $user->password = Hash::make($request->new_password);
                $user->save();
                return back()->with('success', 'Password updated successfully');
            } else {
                return back()->with('errors', 'Old password does not match');
            }
        }
    }


}
