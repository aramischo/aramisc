<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscPaymentGatewaySetting extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_payment_gateway_settings';
    public static function getStripeDetails()
    {

        try {
            $stripeDetails = AramiscPaymentGatewaySetting::select('*')->where('gateway_name', '=', 'Stripe')->first();
            if (!empty($stripeDetails)) {
                return $stripeDetails->stripe_publisher_key;
            }
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }
}
