<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
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
        $ccDetails = $request->all();
        try {
            $amount = 100; // from config or db
            $user = User::find(Auth::user()->id);

            if($user) {
                $subscription = $user->newSubscription('main', 'monthly')->create($ccDetails['stripeToken']);

                if ($subscription->wasRecentlyCreated) {

                    $charged = $user->charge($amount, [
                        'source' => $user->stripe_id,
                        'receipt_email' => $user->email,
                        'currency' => 'usd',
                        'description' => 'First charge',
                    ]);
                    if ($charged) {
                        return redirect("/home");
                    } else {
                        $user->subscription('main')->cancel();
                    }
                }
            }

        } catch(\Exception $ex) {
            return redirect()->back()->with('error', $ex->getMessage());
        }
    }
}
