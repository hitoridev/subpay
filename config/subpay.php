<?php

use App\Models\Fitur;
use App\Models\User;
use Hitoridev\Subpay\Models\Plan;
use Hitoridev\Subpay\Models\Subscription;

return [
    'tables' => [
        'plans' => 'plans',
        'fiturs' => 'fiturs',
        'subscriptions' => 'subscriptions',
    ],
    'models' => [
        'plan' => Plan::class,
        'fitur' => Fitur::class,
        'subscription' => Subscription::class,
    ],
];
