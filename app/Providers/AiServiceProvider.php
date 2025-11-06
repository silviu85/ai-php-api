<?php
// File: app/Providers/AiServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Config\Repository as Config;
use App\Services\SettingsService;
use App\Services\Ai\AiServiceFactory;
use App\Services\ConversationService;

class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register SettingsService
        $this->app->singleton(SettingsService::class, fn() => new SettingsService());

        // Register AiServiceFactory
        $this->app->singleton(AiServiceFactory::class, function ($app) {
            return new AiServiceFactory($app->make(Config::class));
        });

        $this->app->singleton(ConversationService::class, function ($app) {
            return new ConversationService($app->make(AiServiceFactory::class));
        });
    }
}
