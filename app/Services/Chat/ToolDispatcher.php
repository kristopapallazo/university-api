<?php

namespace App\Services\Chat;

use App\Models\User;
use App\Services\Chat\Tools\ChatTool;

class ToolDispatcher
{
    /** @var array<string, ChatTool> */
    private array $tools;

    /** @param  ChatTool[]  $tools */
    public function __construct(array $tools)
    {
        foreach ($tools as $tool) {
            $this->tools[$tool->name()] = $tool;
        }
    }

    /** Returns the tool schemas array to pass to the Anthropic API. */
    public function schemas(): array
    {
        return array_values(array_map(fn (ChatTool $t) => [
            'name' => $t->name(),
            'description' => $t->description(),
            'input_schema' => $t->inputSchema(),
        ], $this->tools));
    }

    /**
     * Execute a tool by name. Returns the result array, or an error array if
     * the tool is unknown or throws. Never throws — Claude gets a structured error instead.
     */
    public function dispatch(string $name, array $input, User $user): array
    {
        $tool = $this->tools[$name] ?? null;

        if (! $tool) {
            return ['error' => "Unknown tool: {$name}"];
        }

        try {
            return $tool->execute($input, $user);
        } catch (\Throwable $e) {
            report($e);

            return ['error' => $e->getMessage()];
        }
    }
}
