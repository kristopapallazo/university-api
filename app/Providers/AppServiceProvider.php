<?php

namespace App\Providers;

use App\Services\Chat\ChatProvider;
use App\Services\Chat\FakeChatProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Dija chatbot. Bound to the echo provider in Phase 1;
        // task 14 swaps this for AnthropicChatProvider.
        $this->app->bind(ChatProvider::class, FakeChatProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
