<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\PaymentIntent;

class StripeService
{
    private $stripeSecretKey;

    public function __construct(string $stripeSecretKey)
    {
        $this->stripeSecretKey = $stripeSecretKey;
        Stripe::setApiKey($this->stripeSecretKey);
    }

    public function createPaymentIntent($amount, $currency = 'eur')
    {
        return PaymentIntent::create([
            'amount' => $amount,
            'currency' => $currency,
        ]);
    }
}
