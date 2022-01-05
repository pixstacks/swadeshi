<?php

namespace App\Http\Controllers\User\Auth;

use App\Models\User;
use App\Models\Setting;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    use RegistersUsers;

    public function __construct()
    {
        $this->middleware('guest:web')->only('showRegistrationForm');
    }

    /**
     * Where to redirect user after registration.
     * 
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::USER_HOME;

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        $results = Setting::where('key', 'like', 'register_user_%')->get();
        $settings = [];
        foreach($results as $result) {
            $settings[$result['key']] = $result['value'];
        }
        return view('user.auth.register', compact('settings'));
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $credentials = $request->except(['password_confirmation', 'terms']);
        $countryCodeLength = strlen($credentials['country_code'])+1; // For + sign in the mobile number.
        $credentials['mobile'] = substr($credentials['mobile'], $countryCodeLength, strlen($credentials['mobile'])-$countryCodeLength);

        $credentials['password'] = Hash::make($credentials['password']);

        $user = User::create($credentials);

        $this->guard()->login($user);

        if ($response = $this->registered($request, $user)) {
            return $response;
        }

        return $request->wantsJson()
                    ? new JsonResponse([], 201)
                    : redirect($this->redirectPath());
    }
    
    /**
     * Get a validator for an incoming registration request.
     * 
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|max:255|string',
            'mobile' => 'required',
            'country_code' => 'required',
            'email' => 'required|email|max:255',
            'password' => 'required|min:6|confirmed',
            'terms' => 'required|accepted',
        ]);
    }

    /**
     * Get the guard to be used during Registration.
     * 
     * @return Illuminate\Contracts\Auth\StatefulGuard;
     */
    public function guard()
    {
        return Auth::guard('web');
    }

}
