<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * Show the application login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        if (auth()->check()) {
            return redirect('admin/dashboard'); // Already logged in, redirect to dashboard
        }
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
        }

        if (Auth::attempt([
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ], $request->has('remember'))) {
            $user = Auth::user();
            $user->update([
                'last_logged_in_at' => Carbon::now(),
            ]);
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        return redirect()->back()
                         ->withErrors(['email' => 'The provided credentials are incorrect.'])
                         ->withInput();
    }

    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
        Auth::logout();

        return redirect()->route('admin.login');
    }
}
