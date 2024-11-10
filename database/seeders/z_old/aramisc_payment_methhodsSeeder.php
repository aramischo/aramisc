<?php

namespace Database\Seeders;

use App\AramiscPaymentMethhod;
use Illuminate\Database\Seeder;

class aramisc_payment_methhodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // AramiscPaymentMethhod::query()->truncate();

        // DB::table('aramisc_payment_methhods')->insert([
        //     [
        //         'method' => 'Cash',
        //         'type' => 'System'
        //     ],
        //     [
        //         'method' => 'Cheque',
        //         'type' => 'System'
        //     ],
        //     [
        //         'method' => 'Bank',
        //         'type' => 'System'
        //     ],
        //     [
        //         'method' => 'Paypal',
        //         'type' => 'System'
        //     ],
        //     [
        //         'method' => 'Stripe',
        //         'type' => 'System'
        //     ],
        //     [
        //         'method' => 'Paystack',
        //         'type' => 'System'
        //     ]
        // ]);
    }
}
