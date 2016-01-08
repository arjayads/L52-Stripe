<?php

namespace App\Http\Controllers;

use App\Models\CreditCard;
use App\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\CssSelector\Exception\InternalErrorException;

class CreditCardController extends Controller
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
        return view('credit-card.cc-info');
    }


    /**
     * Save token from Stripe representing credit card details
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws InternalErrorException
     */
    public function process(Request $request) {
        $ccDetails = $request->all();
        try {
            $cc = CreditCard::where('user_id', Auth::user()->id)->first();
            if($cc) {
                $cc->token = $ccDetails['stripeToken'];
                $cc->save();
            } else {
                CreditCard::create(['token' => $ccDetails['stripeToken'], 'user_id' => Auth::user()->id]);
            }

            $user = User::find(Auth::user()->id);
            if ($user) {
                $user->is_subscribed = true;
                $user->save();
            }

            return redirect("/home");
        } catch(\Exception $ex) {
            throw new InternalErrorException($ex);
        }
    }
}
