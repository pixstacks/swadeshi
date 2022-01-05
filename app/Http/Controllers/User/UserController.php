<?php

namespace App\Http\Controllers\User;

use Exception;
use App\Models\StripeCard;
use App\Models\UserWallet;
use Illuminate\Http\Request;
use App\Models\StripeSession;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\TransactionResource;

class UserController extends Controller
{
    private $user;

    public function __construct($user = null)
    {
        $this->user = $user;
    }

    /**
     * ? Couldn't get auth user instance in __construct & some functions may be called
     * ? without passing in the user liked stripe methods. Find some way so container 
     * ? can resolve that dependency.
     * 
     * @return [type]
     */
    private function setUser()
    {
        if(empty($this->user)) {
            $this->user = auth()->user('web');
        }

        if(empty($this->user)) {
            abort(403);
        }
    }

    // TODO: Create a cron job that will process the unfinished transactions every hour.
    /**
     * ? This method is the method that is called if the payment is successful.
     * @return [type]
     */
    public function processStripeSuccess(Request $request, $intentId = NULL)
    {
        $this->setUser();
        
        // TODO: Remove this session thing entirely. 
        if(session()->has('intentId')) {
            $intentId = session()->pull('intentId');
        }
        else if($intentId) {
            $intentId = $intentId;
        }
        else {
            abort(403);
        }

        if(session()->has('functionCall')) {
            $returnToFunction = session()->pull("functionCall");
        }
        else {
            $returnToFunction = false;
        }

        $localSession = StripeSession::where('payment_intent', $intentId)->latest()->first();
        $stripe = new StripeController($this->user);
        $intent = $stripe->getPaymentIntent($intentId);

        // ? Stops payment from being processed twice.
        if($localSession->payment_status == 'succeeded') {
            return redirect(route('user.wallet'));
        }

        $credentials['payment_status'] = $intent->status;

        if($credentials['payment_status'] == 'succeeded') {
            try {
                if($intent->status != $localSession->payment_status) {
                    // ? Create The Wallet Request.
                    (new TransactionResource)->userCreditDebit($localSession->amount, $this->user->id, 1);

                    // ? Updating the status of the request.
                    $localSession->update($credentials);

                    try {
                        if(session()->has('saveCard') && session()->pull('saveCard')) {
                            $payment_method_id = $intent->payment_method;
                            $stripe->savePaymentMethod($payment_method_id);
                        }
                    } catch(Exception $e) {
                        Log::error("Could Not Save This Card: ".$e->getMessage());
                    }

                    if($returnToFunction)
                        return true;

                    return redirect(route('user.wallet'))->withSuccess("Amount Added To Wallet Successfully");
                }
                else if($intent->status != $localSession->payment_status && $intent->status == 'canceled') {
                    return $this->processStripeFailure($request);
                }
            } catch(Exception $e) {
                Log::error("USER STRIPE PAYMENT:- " . $e->getMessage());
                if($returnToFunction) {
                    throw new Exception($e->getMessage());
                }
                return redirect(route('user.wallet'))->withErrors("Something Went Wrong. Please Try Again Later.");
            }
        }
    }

    public function processStripeFailure(Request $request)
    {
        $this->setUser();

        if(!session()->has('intentId')) {
            abort(403);
        }

        if(session()->has('functionCall')) {
            $returnToFunction = session()->pull("functionCall");
        }
        else {
            $returnToFunction = false;
        }

        $intentId = session()->get('intentId');
        
        $localSession = StripeSession::where('payment_intent', $intentId)->latest()->first();
        $stripe = new StripeController($this->user);

        try {
            $intent = $stripe->cancelPaymentIntent($intentId);
        } catch(Exception $e) {
            if($returnToFunction) {
                throw new Exception($e->getMessage());
            }
            return redirect(route('user.wallet'))->withErrors($e->getMessage());
        }

        if($returnToFunction) {
            return true;
        }

        return redirect(route('user.wallet'))->withErrors("Payment could not be processed right now. Please try again later and if the problem still persists contact the admin.");

    }

    /**
     * ? Process all the payment that couldn't be completed at the time of flow.
     * @return [type]
     */
    public function processPendingPayments(Request $request)
    {
        $this->setUser();

        $unfinished = StripeSession::whereNotIn('payment_status', ['succeeded', 'canceled'])->get();
        $stripe = new StripeController($this->user);
        
        try {
            foreach($unfinished as $uf) {
                $pi = $uf->payment_intent;
                $intent = $stripe->getPaymentIntent($pi);
                if($intent->status == 'succeeded') {
                    session()->put('intentId', $pi);
                    session()->put("functionCall", true);
                    $this->processStripeSuccess($request);
                }
                else if($intent->status == 'canceled') {
                    session()->put('intentId', $pi);
                    session()->put("functionCall", true);
                    $this->processStripeFailure($request);
                }
            }
        } catch(Exception $e) {
            Log::error("Could Not Process User Pending Payment: ". $e->getMessage());
            return false;
        }

        return true;
    }

    public function addCard(Request $request)
    {
        $credentials = $this->validate($request, [
            'name' => 'required|string',
            'payment_method_id' => 'required|string',
        ]);
        try {
            $credentials = $request->all();
            if($request->get('payment_method_id')) {
                $newStripe = new StripeController(auth()->user('web'));

                if(!auth()->user('web')->stripeAccount) {
                    $newStripe->createCustomer([
                        'email' => auth()->user('web')->email,
                        'name' => $credentials['name']
                    ]);
                }

                $response = $newStripe->savePaymentMethod($credentials['payment_method_id']);

                return redirect()
                    ->back()
                    ->withSuccess("Card Added Successfully.");
            }
        } catch(Exception $e) {
            return redirect()
                ->back()
                ->withErrors($e->getMessage());
        }
    }
    
    public function getWallet(Request $request)
    {
        $this->processPendingPayments($request);

        $transactions = UserWallet::where('user_id', auth()->user('web')->id)
            ->latest()
            ->paginate();

        $cards = (new StripeController(auth()->user('web')))->getPaymentMethods();
        
        return view('user.addToWallet', compact('transactions', 'cards'));
    }

    public function addToWallet(Request $request)
    {
        $credentials = $this->validate($request, [
            'amount' => 'required|integer|min:10',
            'card_id' => 'exclude_if:card_id,null|exists:stripe_cards,payment_method_id',
            'saveCard' => 'sometimes',
            // TODO: open when included cvc check.
            // 'cvc' => 'required_with:card_id',
        ]);

        // ? Check there is no ongoing transaction.
        $activeCount = StripeSession::where('request_from', 'user')
            ->where('from_id', auth()->user('web')->id)
            ->whereNotIn('payment_status', ['succeeded', 'canceled'])
            ->count();

        $payment = new StripeController(auth()->user('web'));

        // ? If there is no previous request.
        if($activeCount == 0 || !array_key_exists('card_id', $credentials)) {

            if($activeCount) {
                $activeRequest = StripeSession::where('request_from', 'provider')
                    ->where('from_id', auth()->user('provider')->id)
                    ->whereNotIn('payment_status', ['succeeded', 'canceled'])
                    ->first();
                $payment->cancelPaymentIntent($activeRequest->payment_intent);
            }

            $payment->amount = $credentials['amount'];
            $payment->payment_for = "Add Money To Wallet";
            $payment->image = asset('storage/'.config('constants.site_logo'));
            $payment->success_url = route('user.stripe.paymentSuccessful');
            $payment->cancel_url = route('user.stripe.paymentFailed');

            if(array_key_exists('card_id', $credentials)) {
                // ? This means that it is an existing card.

                try {
                    $card = StripeCard::where('payment_method_id', $credentials['card_id'])->first();
                    if(!$card) {
                        // If this card is not saved.
                        throw new Exception("No Such Card Exist.");
                    }
                    Log::Info("Charging Existing Card UserController");
                    $response = $payment->chargeExistingCard($card);
                }
                catch (Exception $e) {
                    return redirect()
                        ->back()
                        ->withErrors($e->getMessage());
                }

                Log::info("Putting Value in session");
                $request->session()->put('intentId', $response->payment_intent);

                // ! starts here
                if(!empty($response->requiresAction) && $response->requiresAction) {
                    $response = json_encode($response);
                    
                    return view('user.addToWallet', compact('response'));
                }
                // ! ends here

                Log::info("Redirecting to response url");
                return redirect($response->url);
            }
            else {
                // ? This response url is the url to the stripe checkout page for new card.

                $response = $payment->newCardPayment();

                if(array_key_exists('saveCard', $credentials)) {
                    session()->put('saveCard', true);
                }
                
                session()->put('intentId', $response->payment_intent);
                return redirect($response->url)->with('sessionId', $response->id);
            }
        }
        else {
            // ? If there is a previous pending request update the payment intent and restart the 
            $stripeSession = StripeSession::where('request_from', 'user')
                ->where('from_id', auth()->user('web')->id)
                ->whereNotIn('payment_status', ['succeeded', 'canceled'])
                ->first();

            session()->put('intentId', $stripeSession->payment_intent);

            // TODO: See if the payment method is changed.
            try {
                try {
                    $intent = $payment->updatePaymentIntent($stripeSession->payment_intent, [
                        'amount' => $credentials['amount'] * 100
                    ]);

                    $updateCredentials['amount'] = $credentials['amount'];
                    $stripeSession->update($updateCredentials);

                    $response = $payment->paymentIntentResponse($stripeSession->payment_intent);
                }
                catch (Exception $e) {
                    Log::info("ERROR addToWallet: ".$e->getMessage());
                    return redirect()
                        ->back()
                        ->withErrors($e->getMessage());
                }

                $request->session()->put('intentId', $stripeSession->payment_intent);

                if(!empty($response->requiresAction) && $response->requiresAction) {
                    $response = json_encode($response);
                    return view('user.addToWallet', compact('response'));
                }

                return redirect($response->url);
            } catch (Exception $e) {
                Log::info("ERROR addToWallet: ".$e->getMessage());
                
                return redirect()
                    ->route('user.wallet')
                    ->withErrors($e->getMessage());
            }

            return view('user.addToWallet', compact('response'));
        }
    }
}
