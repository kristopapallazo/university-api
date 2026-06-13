<?php

namespace App\Providers;

use App\Services\Chat\AnthropicChatProvider;
use App\Services\Chat\ChatProvider;
use App\Services\Chat\FakeChatProvider;
use App\Services\Chat\SystemPrompt;
use App\Services\Chat\ToolDispatcher;
use App\Services\Chat\Tools\GetFacultyInfoTool;
use App\Services\Chat\Tools\GetMyExamsTool;
use App\Services\Chat\Tools\GetMyGradesTool;
use App\Services\Chat\Tools\GetMyInvoicesTool;
use App\Services\Chat\Tools\GetMyScheduleTool;
use App\Services\Chat\Tools\GetUserProfileTool;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Dija chatbot. Use the real Claude provider when a real key is present;
        // fall back to the echo bot otherwise so CI, fresh clones, and the
        // feature tests stay deterministic and offline (task 14).
        $this->app->singleton(ToolDispatcher::class, fn () => new ToolDispatcher([
            new GetUserProfileTool,
            new GetMyScheduleTool,
            new GetMyGradesTool,
            new GetMyExamsTool,
            new GetMyInvoicesTool,
            new GetFacultyInfoTool,
        ]));

        $this->app->bind(ChatProvider::class, function ($app) {
            $key = config('services.anthropic.key');

            if (! $key || $key === 'sk-ant-placeholder') {
                return new FakeChatProvider;
            }

            // System prompt needs the authenticated user; resolve it per-request.
            $user = $app['request']->user();

            return new AnthropicChatProvider(
                apiKey: $key,
                model: config('services.anthropic.model'),
                systemPrompt: SystemPrompt::for($user),
            );
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
