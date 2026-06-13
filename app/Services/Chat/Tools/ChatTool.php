<?php

namespace App\Services\Chat\Tools;

use App\Models\User;

interface ChatTool
{
    public function name(): string;

    public function description(): string;

    /** JSON Schema object for the tool's input parameters. */
    public function inputSchema(): array;

    /** Execute the tool and return a plain array the LLM can read. */
    public function execute(array $input, User $user): array;
}
