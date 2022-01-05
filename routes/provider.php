<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\SocialAuthentication;
use App\Http\Controllers\Provider\HomeController;
use App\Http\Controllers\Provider\ProviderController;
use App\Http\Controllers\Provider\Auth\LoginController;
use App\Http\Controllers\Provider\Auth\RegisterController;
use App\Http\Controllers\Provider\Auth\ResetPasswordController;
use App\Http\Controllers\Provider\Auth\ForgotPasswordController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Authentication Routes
// Route::get('/login', [LoginController::class, 'showLoginForm'])->name('loginForm');
// Route::post('/login', [LoginController::class, 'login'])->name('login');
// Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('registrationForm');
// Route::post('/register', [RegisterController::class, 'register'])->name('register');
// Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('guest')->name('password.email');
// Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->middleware('guest')->name('password.update');
// Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->middleware('guest')->name('password.reset');

// // Google
// Route::middleware('guest')->group(function() {
//     // Todo: there can be only one social login because the url can be only one so make it in in user.
//     // Google
//     Route::get('/provider/sign-up/google', [SocialAuthentication::class, 'googleLogin'])->name('google.registration');
//     Route::get('/provider/sign-in/google', [SocialAuthentication::class, 'googleLogin'])->name('google.login');
//     Route::get('google/success', [SocialAuthentication::class, 'googleRedirect']);
// });

// // Provider Routes After Authentication
// Route::middleware('auth:provider')->group(function() {
//     Route::get('/dashboard', [HomeController::class, 'index'])->name('home');
//     Route::get('/activity', [HomeController::class, 'activity'])->name('activity');
    
//     Route::get('/settings', [HomeController::class, 'settings'])->name('settings');
//     Route::post('/changePassword', [HomeController::class, 'changePassword'])->name('changePassword');
//     Route::post('/updateProfile', [HomeController::class, 'updateProfile'])->name('updateProfile');
//     Route::post('/uploadVerificationDocument', [HomeController::class, 'uploadVerificationDocument'])->name('uploadVerificationDocument');

//     Route::get('/notification', [HomeController::class, 'notification'])->name('notification');

//     // Payment
//     Route::get('/makePayment', [StripeController::class, 'newCardPayment'])->name('makePayment');
//     Route::get('/stripe/paymentSuccessful/{intentId?}', [ProviderController::class, 'processStripeSuccess'])->name('stripe.paymentSuccessful');
//     Route::get('/stripe/paymentFailed/{intentId?}', [ProviderController::class, 'processStripeFailure'])->name('stripe.paymentFailed');

//     // Money Related Routes.
//     Route::post('/addCard', [ProviderController::class, 'addCard'])->name('addCard');
//     Route::get('/wallet', [ProviderController::class, 'getWallet'])->name('wallet');
//     Route::post('/addToWallet', [ProviderController::class, 'addToWallet'])->name('addToWallet');

//     // ! Test Routes
//     Route::get('/track', [HomeController::class, 'track'])->name('track');
//     Route::post('/track', [HomeController::class, 'track_location'])->name('track_location');

//     Route::get('/request/history', [HomeController::class, 'requestHistory'])->name('requestHistory');
//     Route::get('/request/{userRequest}', [HomeController::class, 'showRequest'])->name('showRequest');
// });