<?php

namespace App\Http\Controllers;

use Exception;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use App\Models\StripeCard;
use Stripe\Checkout\Session;
use App\Models\StripeSession;
use App\Models\StripeCustomer;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use stdClass;

class StripeController extends Controller
{
    private $stripe;

    public $payment_for;
    public $image;
    public $amount;
    public $user = NULL;
    public $user_type;

    public $success_url;
    public $cancel_url;

    /**
     * @param mixed $user
     */
    public function __construct($user)
    {
        $this->user_type = strtolower(substr(get_class($user), 11));
        $this->user = $user;

        Stripe::setApiKey(config('constants.stripe_secret_key'));
        
        $this->stripe = new StripeClient(config('constants.stripe_secret_key'));
    }

    // ! Session Methods.
    /**
     * ? Get Stripe Payment Session by id.
     * @param String
     * 
     * @return Stripe/Checkout/Session
     */
    public function getSession($session_id)
    {
        return $this->stripe->checkout->sessions->retrieve($session_id);
    }

    /**
     * ? This method creates the session for the stripe payment.
     * 
     * @return Session
     */
    private function createSession() {
        $this->createPaymentIntent();
        
        if(!$this->user->stripeAccount) {
            $this->createCustomer([
                'email' => $this->user->email,
                'name' => $this->user->name,
            ]);

            $this->user->refresh();
        }

        $stripeAccount = $this->user->stripeAccount;

        return Session::create([
            'payment_method_types' => ['card'],
            'customer' => $stripeAccount->customer_id,
            'line_items' => [[
                'price_data' => [
                    'currency' => 'inr',
                    'unit_amount' => $this->amount * 100,
                    'product_data' => [
                        'name' => "$this->payment_for",
                        'images' => ["$this->image"],
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => "$this->success_url",
            'cancel_url' => "$this->cancel_url",
        ]);
        
    }
    // ! Session Methods End Here.

    /**
     * ? This method gets the stripe payment session and creates a local stripe_session,
     * ? which can be used at the payment the time of successful payment to complete the transaction.
     * 
     * @param mixed $user
     * 
     * @return Session
     */
    public function newCardPayment()
    {
        if($this->user) {
            $response = $this->createSession();

            $intent = $this->getPaymentIntent($response->payment_intent);

            StripeSession::create([
                'session_id' => $response->id,
                'payment_status' => $intent->status,
                'amount' => $response->amount_total / 100,
                'request_from' => $this->user_type,
                'from_id' => $this->user->id,
                'payment_intent' => $response->payment_intent,
            ]);

            return $response;
        }
    }

    /**
     * ? Charges an Existing Card.
     * @param StripeCard $card
     * 
     * @return [type]
     */
    public function chargeExistingCard(StripeCard $card)
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $this->amount * 100,
                'currency' => 'inr',
                'customer' => $this->user->stripeAccount->customer_id,
                'payment_method' => $card->payment_method_id,
                // 'error_on_requires_action' => true, // ? if there is further action require fail transact.
                'confirm' => true,
                'confirmation_method' => 'manual', // ? if there is further action handle manually
            ]);

            // ? For Card that does not require authentication the paymentIntent Becomes successful instantly then the user cannot process paymentSuccess properly.
            $status = $paymentIntent->status == 'succeeded' ? 'processing' : $paymentIntent->status;

            StripeSession::create([
                'payment_status' => $status,
                'amount' => $paymentIntent->amount / 100,
                'request_from' => $this->user_type,
                'from_id' => $this->user->id,
                'payment_intent' => $paymentIntent->id,
            ]);

            return $this->paymentIntentResponse($paymentIntent->id);
        } catch (Exception $e) {
            Log::error("Error in charge existing card method: ".$e->getMessage());
            throw new Exception("Payment could not be processed right now. Please try again later and if the problem still persists contact the admin.");
        }
    }
    
    // ! Payment Intent Methods
    public function createPaymentIntent() {
        $this->stripe->paymentIntents->create([
            'amount' => $this->amount * 100,
            'currency' => 'inr',
            'payment_method_types' => ['card'],
        ]);
    }

    /**
     * ? Returns the Payment Intent Based On Payment Intent Id.
     * @param string
     * 
     * @return Stripe/PaymentIntent
     */
    public function getPaymentIntent($intentId)
    {
        $intent =  $this->stripe->paymentIntents->retrieve(
            $intentId, []
        );

        if($intent->status == 'requires_confirmation') {
            $intent = $this->confirmPaymentIntent($intentId);
        }

        return $intent;
    }

    /**
     * ? Confirm the paymentIntent. 
     * ? This moves intent from status of requires_confirmation.
     * @param mixed $intentId
     * 
     * @return [type]
     */
    private function confirmPaymentIntent($intentId)
    {
        return $this->stripe->paymentIntents->confirm(
            $intentId
        );
    }

    /**
     * @param string $intentId
     * @param mixed $values
     * 
     * @return PaymentIntent
     */
    public function updatePaymentIntent($intentId, $values)
    {
        $this->stripe->paymentIntents->update(
            $intentId,
            $values
        );
    }

    public function cancelPaymentIntent($intentId)
    {
        try {
            
            $intent = $this->getPaymentIntent($intentId);

            // ? If the status is already cancelled.
            if($intent->status != 'canceled') {
                $intent = $this->stripe->paymentIntents->cancel(
                    $intentId, []
                );
            }

            $localSession = StripeSession::where('payment_intent', $intentId)->latest()->first();
            
            if($intent->status == 'canceled') {
                $credentials['payment_status'] = 'canceled';
                $localSession->update($credentials);
            }

        } catch(Exception $e) {
            Log::error("Stripe Payment Could Not Be Cancelled StripeController:- ".$e->getMessage());
            throw new Exception("Some Error Occurred. Please Try Again Later.");
        }
    }

    /**
     * ? PaymentIntents are used for charging already added cards.
     * @param mixed $intent
     * 
     * @return [type]
     */
    public function paymentIntentResponse($intentId) {
        $intent = $this->getPaymentIntent($intentId);
        $response = new stdClass;

        $response->payment_intent = $intentId;
        $localSession = StripeSession::where('payment_intent', $intentId)->latest()->first();

        if ($intent->status == 'succeeded') {
            if($localSession->request_from == 'user') {
                $response->url = route('user.stripe.paymentSuccessful');
            }
            else if($localSession->request_from == 'provider') {
                $response->url = route('provider.stripe.paymentSuccessful');
            }
        } 
        else if ($intent->status == 'requires_action') {
            # Tell the client to handle the action
            $response->requiresAction = true;
            $response->clientSecret = $intent->client_secret;
            if($localSession->request_from = 'user') {
                $response->url = route('user.addToWallet');
            }
            else if($localSession->request_from = 'provider') {
                $response->url = route('provider.addToWallet');
            }

            // return json_encode([
            //     'requiresAction' => true,
            //     'clientSecret' => $intent->client_secret
            // ]);
        }
        else if($intent->status == 'requires_payment_method') {
            // TODO: Go the select the payment Method.
            $this->cancelPaymentIntent($intentId);

            Log::debug("Unattended payment status:- ".$intent->status);
            
            throw new Exception("Payment could not be processed right now. Please try again later and if the problem still persists contact the admin.");
        }
        // TODO: handle cancelled status.
        // else if($intent->status = 'canceled') {
        //      
        // }
        else {
            // Any other status would be unexpected, so error
            Log::debug("Unattended payment status:- ".$intent->status);
            throw new Exception("Payment could not be processed right now. Please try again later and if the problem still persists contact the admin.");
        }

        Log::info("Payment Intent Response");
        Log::Info($intent->status);
        Log::info($response->url);
        
        return $response;
    }
    // ! Payment Intent Methods End Here.

    // ! Payment Method Methods.
    /**
     * ? Gets all the saved Cards/PaymentMethods For Current User.
     * @return [type]
     */
    public function getPaymentMethods()
    {
        if($this->user->stripeAccount) {
            return $this->stripe->paymentMethods->all([
                'customer' => $this->user->stripeAccount->customer_id,
                'type' => 'card',
            ])->data;
        }
        else {
            return NULL;
        }
    }

    public function createPaymentMethod()
    {
        $this->stripe->paymentMethods->create([
            'type' => 'card',
            'card' => [
              'number' => '4242424242424242',
              'exp_month' => 7,
              'exp_year' => 2022,
              'cvc' => '314',
            ],
        ]);
    }

    /**
     * ? Attaches a Card To a User
     * @param mixed $payment_method_id
     * 
     * @return [type]
     */
    public function savePaymentMethod($payment_method_id)
    {
        try {
            $this->user->refresh();
            $stripeAccount = $this->user->stripeAccount;

            $payment_method = PaymentMethod::retrieve("$payment_method_id");
            // $this->detachPaymentMethod(($payment_method_id));
            $payment_method->attach(['customer' => $stripeAccount->customer_id]);

            $card = StripeCard::where('stripe_customer_id', $stripeAccount->id)
                ->where('payment_method_id', $payment_method_id)
                ->first();

            if(!$card) {
                StripeCard::create([
                    'stripe_customer_id' => $stripeAccount->id,
                    'payment_method_id' => $payment_method_id
                ]);
            }
            else {
                throw new Exception("Card Already Exist");
            }
    
            return $payment_method;
        }
        catch (Exception $e) {
            Log::error("Stripe SaveCard Error: ".$e->getMessage());
            throw new Exception("The card Could Not Be Saved.");
        }
    }

    private function detachPaymentMethod($payment_method_id)
    {
        return $this->stripe->paymentMethods->detach(
            $payment_method_id,
            []
        );
    }
    // ! Payment Method Methods.

    /**
     * ? Creates a Stripe Customer
     * @param Array $details
     * 
     * @return Customer
     */
    public function createCustomer($details)
    {
        $customer = $this->stripe->customers->create($details);

        StripeCustomer::create([
            'account_holder_type' => $this->user_type,
            'account_holder_id' => $this->user->id,
            'customer_id' => $customer->id,
        ]);

        return $customer;
    }
}
