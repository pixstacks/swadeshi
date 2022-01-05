<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Provider;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthentication extends Controller
{

    public function _construct()
    {
        $this->middleware('guest');
    }

    // Google Logins
    public function googleLogin()
    {
        $from = url()->previous();

        // Determine the Type Of User Depending On Where User Is Coming From.
        $user_login_url = route('user.login');
        $user_registration_url = route('user.register');
        $provider_login_url = route('provider.login');
        $provider_registration_url = route('provider.register');

        if($from === $user_login_url || $from === $user_registration_url) {
            $user_type = 'user';
        }
        else if($from === $provider_login_url || $from === $provider_registration_url) {
            $user_type = 'provider';
        }

        session()->flash('user_type', $user_type);
        return Socialite::driver('google')
            ->redirect();
    }

    public function googleRedirect()
    {
        $user_type = session()->get('user_type'); // Type Of User: User or Provider

        try {
            $google = Socialite::driver('google')->stateless()->user();

            if($user_type == 'user') {
                $user = User::where('email', $google->user['email'])->first();

                if(!$user) { // If user does not already exist Register First
                    $user = $this->createUser($google, $user_type);
                }

                // login the user.
                Auth::guard('web')->login($user);

                return redirect()
                    ->route('user.serviceCheckout');
            }
            else if($user_type == 'provider') {
                $user = Provider::where('email', $google->user['email'])->first();


                if(!$user) { // If provider does not already exist Register First
                    $user = $this->createUser($google, $user_type);
                }

                Auth::guard('provider')->login($user);

                return redirect()
                    ->route('provider.home');
            }
            else {
                throw new Exception("Undefined User Type.");
            }
        } catch (Exception $e) {
            Log::critical("Google Authentication Error Message:- ".$e->getMessage());
            if($user_type == 'user') {
                return redirect(route('user.login'))
                    ->withErrors($e->getMessage());
            }
            else if($user_type == 'provider') {
                return redirect(route('provider.login'))
                    ->withErrors($e->getMessage());
            }
        }
    }

    private function createUser($google, $user_type)
    {
        $credentials['first_name'] = $google->user['given_name'];
        $credentials['last_name'] = $google->user['family_name'];
        $credentials['mobile'] = '';
        $credentials['password'] = Hash::make($google->user['id'].'password');
        $credentials['login_by'] = 'google';
        $credentials['social_unique_id'] = $google->user['id'];
        $credentials['email'] = $google->user['email'];

        if($user_type == 'user') {
            $credentials['avatar'] = 'storage/public/user/avatars/'.Str::random(50).'.jpg';
            // Copy Avatar File.
            copy($google->avatar, $credentials['avatar']);
            $credentials['email_verified_at'] = $google->user['verified_email'] ? Carbon::now()->toDateTimeString() : null;
            $user = User::create($credentials);
        }
        else if($user_type == 'provider') {
            $credentials['avatar'] = 'storage/public/provider/avatars/'.Str::random(50).'.jpg';
            // Copy Avatar File.
            copy($google->avatar, $credentials['avatar']);
            $user = Provider::create($credentials);
        }
        return $user;
    }
}