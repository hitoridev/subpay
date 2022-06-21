<?php

namespace Hitoridev\Subpay\Models;

use Hitoridev\Subpay\Models\Plan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fitur extends Model
{
    use HasFactory;

    public function plans()
    {
        return $this->belongsToMany(Plan::class, config('subpay.tables.fiturs') . '_' . config('subpay.tables.plans'));
    }
}
