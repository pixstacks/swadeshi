<?php

namespace App\Http\Controllers\Provider\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    
    use SendsPasswordResetEmails;

    protected function broker()
    {
        return Password::broker('providers');
    }
}
