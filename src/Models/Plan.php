<?php

namespace Hitoridev\Subpay\Models;

use Hitoridev\Subpay\Models\Fitur;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plan extends Model
{
    protected $guarded = [''];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'float',
        'invoice_period' => 'integer',
        'invoice_interval' => 'string',
    ];

    protected $rules = [];
    public function __construct(array $attributes = [])
    {
        $this->setTable(config('hitoridev.subpay.tables.plans'));

        parent::__construct($attributes);
    }

    public function fiturs()
    {
        return $this->belongsToMany(Fitur::class, config('subpay.tables.fiturs') . '_' . config('subpay.tables.plans'));
    }

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($plan) {
            $plan->planSubscriptions()->delete();
        });
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(config('subpay.models.subscription'), 'plan_id', 'id');
    }

    public function activate()
    {
        $this->update(['is_active' => true]);

        return $this;
    }


    public function deactivate()
    {
        $this->update(['is_active' => false]);

        return $this;
    }
}
