@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Credit card information</div>
                <div class="panel-body">
                    <form id="payment-form" class="form-horizontal" role="form" method="POST" action="/process-card">
                        {!! csrf_field() !!}

                        <div class="alert alert-danger payment-errors hidden"></div>

                        <div class="form-group">
                            <label class="col-md-4 control-label">Card Number</label>

                            <div class="col-md-6">
                                <input type="text" class="form-control" size="20" data-stripe="number">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 control-label">CVC</label>

                            <div class="col-md-6">
                                <input type="text" class="form-control" size="4" data-stripe="cvc"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 control-label">Expiration (MM/YYYY)</label>

                            <div class="col-md-6">
                                <input style="width: 20% !important; display: inline !important;" type="text" class="form-control" size="2" data-stripe="exp-month"/>
                                <span style="font-size: 30px; vertical-align: middle">/</span>
                                <input style="width: 40% !important; display: inline !important;" type="text" class="form-control" size="4" data-stripe="exp-year"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button class="btn btn-default">
                                    <a  href="/home">
                                        <i class="fa fa-btn fa-arrow-left"></i>Cancel
                                    </a>
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-arrow-right"></i>Continue
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>


    <script type="text/javascript">
        // This identifies your website in the createToken call below
        Stripe.setPublishableKey('pk_test_6pRNASCoBOKtIshFeQd4XMUh');

        jQuery(function($) {
            $('#payment-form').submit(function(event) {
                var $form = $(this);

                // Disable the submit & cancel button to prevent repeated clicks
                $form.find($('.btn')).prop('disabled', true);

                Stripe.card.createToken($form, stripeResponseHandler);

                // Prevent the form from submitting with the default action
                return false;
            });
        });

        function stripeResponseHandler(status, response) {
            var $form = $('#payment-form');

            if (response.error) {
                // Show the errors on the form
                $form.find('.payment-errors').text(response.error.message);
                $form.find('.payment-errors').removeClass("hidden");
                $form.find($('.btn')).prop('disabled', false);
            } else {
                // response contains id and card, which contains additional card details
                var token = response.id;
                // Insert the token into the form so it gets submitted to the server
                $form.append($('<input type="hidden" name="stripeToken" />').val(token));
                // and submit
                $form.get(0).submit();
            }
        };
    </script>
@endsection