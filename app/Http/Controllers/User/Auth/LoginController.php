<?php

namespace App\Http\Controllers\User\Auth;

use App\Models\Setting;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Instance a new Controller Instance
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:web')->except('logout');
        $this->middleware('auth:web')->only('logout');
    }
    
    /**
     * Where to redirect user after login
     * 
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::USER_HOME;

    /**
     * Show the user's login form.
     * 
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm() {
        $results = Setting::where('key', 'like', 'login_user_%')->get();
        $settings = [];
        foreach($results as $result) {
            $settings[$result['key']] = $result['value'];
        }
        return view('user.auth.login', compact('settings'));
    }

    public function guard()
    {
        return Auth::guard('web');
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('user.login'));
    }
}
