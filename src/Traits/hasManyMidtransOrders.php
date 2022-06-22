<?php

declare(strict_types=1);

namespace Hitoridev\Subpay\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;

trait hasManyMidtransOrders
{
    public function midtransOrders(): HasMany
    {
        if (config('subpay.midtrans_orders' != false)) {
            return $this->HasMany(config('subpay.models.midtrans'));
        }
    }
}
