<?php

declare(strict_types=1);

namespace Hitoridev\Subpay\Traits;

use Carbon\Carbon;
use Hitoridev\Subpay\Models\Plan;
use Hitoridev\Subpay\Services\Period;
use Illuminate\Database\Eloquent\Collection;
use Hitoridev\Subpay\Models\PlanSubscription;
use Hitoridev\Subpay\Models\Subscription;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasPlanSubscriptions
{
    /**
     * Define a polymorphic one-to-many relationship.
     *
     * @param string $related
     * @param string $name
     * @param string $type
     * @param string $id
     * @param string $localKey
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    abstract public function morphMany($related, $name, $type = null, $id = null, $localKey = null);

    /**
     * Boot the HasPlanSubscriptions trait for the model.
     *
     * @return void
     */
    protected static function bootHasSubscriptions()
    {
        static::deleted(function ($plan) {
            $plan->planSubscriptions()->delete();
        });
    }

    /**
     * The subscriber may have many plan subscriptions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function planSubscriptions(): MorphMany
    {
        return $this->morphMany(config('subpay.models.subscription'), 'subscriber', 'subscriber_type', 'subscriber_id');
    }

    /**
     * A model may have many active plan subscriptions.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function activePlanSubscriptions(): Collection
    {
        return $this->planSubscriptions->reject->inactive();
    }

    /**
     * Get a plan subscription by slug.
     *
     * @param string $subscriptionSlug
     *
     * @return \Hitoridev\Subpay\Models\Subscription|null
     */
    public function mySubscription(string $name): ?Subscription
    {
        return $this->planSubscriptions()->where('name', $name)->first();
    }

    /**
     * Get subscribed plans.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function subscribedPlans(): Collection
    {
        $planIds = $this->planSubscriptions->reject->inactive()->pluck('plan_id')->unique();

        return app('hitoridev.subpay.plan')->whereIn('id', $planIds)->get();
    }

    /**
     * Check if the subscriber subscribed to the given plan.
     *
     * @param int $planId
     *
     * @return bool
     */
    public function subscribedTo($planId): bool
    {
        $subscription = $this->planSubscriptions()->where('plan_id', $planId)->first();

        return $subscription && $subscription->active();
    }

    /**
     * Subscribe subscriber to a new plan.
     *
     * @param string                            $subscription
     * @param \Hitoridev\Subpay\Models\Plan $plan
     * @param \Carbon\Carbon|null               $startDate
     *
     * @return \Hitoridev\Subpay\Models\Subscription
     */
    public function newPlanSubscription($subscription, Plan $plan, Carbon $startDate = null): Subscription
    {
        $period = new Period($plan->invoice_type, $plan->invoice_period);

        return $this->planSubscriptions()->create([
            'name' => $subscription,
            'plan_id' => $plan->getKey(),
            'start_at' => $period->getStartDate(),
            'end_at' => $period->getEndDate(),
        ]);
    }
}
