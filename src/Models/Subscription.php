<?php

namespace Hitoridev\Subpay\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Hitoridev\Subpay\Services\Period;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\Translation\Exception\LogicException;

class Subscription extends Model
{
    protected $guarded = [''];
    protected $casts = [
        'subscriber_id' => 'integer',
        'subscriber_type' => 'string',
        'plan_id' => 'integer',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        $this->setTable(config('hitoridev.subpay.tables.subscriptions'));
        parent::__construct($attributes);
    }

    //from trait
    public function plan(): BelongsTo
    {
        return $this->belongsTo(config('subpay.models.plan'), 'plan_id', 'id', 'plan');
    }

    public function scopeByPlanId(Builder $builder, int $planId): Builder
    {
        return $builder->where('plan_id', $planId);
    }

    public function subscriber(): MorphTo
    {
        return $this->morphTo('subscriber', 'subscriber_type', 'subscriber_id', 'id');
    }

    public function active(): bool
    {
        return !$this->ended();
    }

    public function ended(): bool
    {
        return $this->end_at ? Carbon::now()->gte($this->end_at) : false;
    }

    public function inactive(): bool
    {
        return !$this->active();
    }

    public function cancel($immediately = false)
    {
        $this->canceled_at = Carbon::now();

        if ($immediately) {
            $this->end_at = $this->canceled_at;
        }

        $this->save();

        return $this;
    }

    public function canceled(): bool
    {
        return $this->canceled_at ? Carbon::now()->gte($this->canceled_at) : false;
    }

    public function changePlan(Plan $plan)
    {
        if ($this->plan->invoice_type !== $plan->invoice_type || $this->plan->invoice_period !== $plan->invoice_period) {
            $this->setNewPeriod($plan->invoice_type, $plan->invoice_period);
        }

        // Attach new plan to subscription
        $this->plan_id = $plan->getKey();
        $this->save();

        return $this;
    }

    public function renew()
    {
        if ($this->ended() && $this->canceled()) {
            throw new LogicException('Unable to renew canceled ended subscription.');
        }

        $subscription = $this;

        DB::transaction(function () use ($subscription) {
            // Renew period
            $subscription->setNewPeriod();
            $subscription->save();
        });

        return $this;
    }

    protected function setNewPeriod($invoice_type = '', $invoice_period = '', $start = '')
    {
        if (empty($invoice_type)) {
            $invoice_type = $this->plan->invoice_type;
        }

        if (empty($invoice_period)) {
            $invoice_period = $this->plan->invoice_period;
        }

        $period = new Period($invoice_type, $invoice_period, $start);

        $this->start_at = $period->getStartDate();
        $this->end_at = $period->getEndDate();

        return $this;
    }

    //UTILITIES FUNCTION
    public function scopeOfSubscriber(Builder $builder, Model $subscriber): Builder
    {
        return $builder->where('subscriber_type', $subscriber->getMorphClass())->where('subscriber_id', $subscriber->getKey());
    }

    public function scopeFindEndingPeriod(Builder $builder, int $dayRange = 3): Builder
    {
        $from = Carbon::now();
        $to = Carbon::now()->addDays($dayRange);

        return $builder->whereBetween('end_at', [$from, $to]);
    }

    public function scopeFindEndedPeriod(Builder $builder): Builder
    {
        return $builder->where('end_at', '<=', now());
    }

    public function scopeFindActive(Builder $builder): Builder
    {
        return $builder->where('end_at', '>', now());
    }
}
