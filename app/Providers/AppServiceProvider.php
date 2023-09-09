<?php

namespace App\Providers;

use App\Services\OpenAIService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('openai', function ($app) {
            $apiKey = config('services.openai.api_key');
            return new OpenAIService($apiKey);
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
