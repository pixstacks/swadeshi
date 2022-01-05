<?php

namespace App\Http\Controllers\Provider\Auth;

use App\Models\Setting;

use Illuminate\Http\Request;
use App\Models\ProviderService;
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
        $this->middleware(['guest:provider', 'guest'])->except('logout');
        $this->middleware('auth:provider')->only('logout');
    }

    /**
     * Where to redirect provider after login
     * 
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::PROVIDER_HOME;

    /**
     * Show the provider's login form.
     * 
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm() {
        $results = Setting::where('key', 'like', 'login_provider_%')->get();
        $settings = [];
        foreach($results as $result) {
            $settings[$result['key']] = $result['value'];
        }
        return view('provider.auth.login', compact('settings'));
    }

    public function guard()
    {
        return Auth::guard('provider');
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('provider.login'));
    }
}
