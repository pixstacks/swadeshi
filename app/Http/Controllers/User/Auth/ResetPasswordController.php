<?php

namespace App\Http\Controllers\User\Auth;

use App\Models\Setting;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;
    
    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::USER_HOME;
    
    protected function guard()
    {
        return Auth::guard('web');
    }

    protected function broker()
    {
        return Password::broker('users');
    }

    public function showResetForm($token)
    {
        $results = Setting::where('key', 'like', 'login_user_%')->get();
        $settings = [];
        foreach($results as $result) {
            $settings[$result['key']] = $result['value'];
        }
        return view('user.auth.reset-password', compact('token', 'settings'));
    }
}
