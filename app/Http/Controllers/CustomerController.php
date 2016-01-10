<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Stripe\Stripe;
use Symfony\Component\CssSelector\Exception\InternalErrorException;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show credit card info entry form
     *
     * After registration is done, the user is allowed
     * to input credit card information
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ccInfo() {
        if (Auth::check() && !Auth::user()->subscribed('main')) {
            return view('credit-card.cc-info');
        } else {
            return redirect("/home");
        }
    }


    /**
     * Save token from Stripe representing credit card details
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws InternalErrorException
     */
    public function processCard(Request $request) {
        $user = User::find(Auth::user()->id);
        if($user) {
            try {
                $customer = $user->createAsStripeCustomer($request->get('stripeToken'), ["source" => $request->get('stripeToken'), "description" => "Customer for {$user->email}"]);
                $user->stripe_id = $customer->id;
                $user->save();

                $amount = 100; // from config or db

                $charged = $user->charge($amount, [
                    'source' => $customer->id,
                    'receipt_email' => $user->email,
                    'currency' => 'usd',
                    'description' => 'First charge',
                ]);

                if ($charged) {
                    $subscription = $user->newSubscription('main', 'monthly')->create($customer->id);
                    if ($subscription->wasRecentlyCreated) {
                        return redirect("/home");
                    }
                } else {
                    return redirect()->back()->withErrors(['error' => "Processing charge failed"]);
                }
            } catch(\Exception $ex) {
                return redirect()->back()->withErrors(['error' => $ex->getMessage()]);
            }
        } else {
            return redirect("/login");
        }
    }
}
