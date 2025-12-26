<?php

namespace Modules\AdminPlans\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Payment\Events\PaymentSuccess;
use Modules\Auth\Events\AuthEvent;
use Modules\AdminPlans\Listeners\HandlePlan;
use Modules\AdminPlans\Listeners\HandleAccessPlan;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        PaymentSuccess::class => [
            HandlePlan::class,
        ],
        AuthEvent::class => [
            HandleAccessPlan::class,
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void
    {
        //
    }
}
