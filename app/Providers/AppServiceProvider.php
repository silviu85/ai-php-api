<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ConversationService;
use App\Services\Ai\AiServiceInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
         $this->app->singleton(ConversationService::class, function ($app) {
        return new ConversationService($app->make(AiServiceInterface::class));
    });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
