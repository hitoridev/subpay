<?php

namespace Hitoridev\Subpay;

use App\Models\Fitur;
use Illuminate\Support\Str;
use Hitoridev\Subpay\Models\Plan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Hitoridev\Subpay\Models\Subscription;

class SubpayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/subpay.php',
            'subpay'
        );

        $models = [
            'subpay.plan' => Plan::class,
            'subpay.fitur' => Fitur::class,
            'subpay.subscription' => Subscription::class,
        ];

        foreach ($models as $service => $class) {
            $this->app->singleton($service, $model = $this->app['config'][Str::replaceLast('.', '.models.', $service)]);
            $model === $class || $this->app->alias($service, $class);
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/subpay.php' => config_path('subpay.php'),
        ]);

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if (File::exists(__DIR__ . '/Helper/Helpers.php')) {
            require __DIR__ . '/Helper/Helpers.php';
        }
    }
}
