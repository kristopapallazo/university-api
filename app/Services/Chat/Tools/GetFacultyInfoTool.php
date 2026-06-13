<?php

namespace App\Services\Chat\Tools;

use App\Models\Faculty;
use App\Models\User;

class GetFacultyInfoTool implements ChatTool
{
    public function name(): string
    {
        return 'get_faculty_info';
    }

    public function description(): string
    {
        return 'Returns public information about UAMD faculties: name, dean, and departments. Pass faculty_id for a specific faculty, or omit to list all.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'faculty_id' => [
                    'type' => 'integer',
                    'description' => 'The ID of a specific faculty. Omit to return all faculties.',
                ],
            ],
            'required' => [],
        ];
    }

    public function execute(array $input, User $user): array
    {
        $query = Faculty::with(['dean', 'departments']);

        if (! empty($input['faculty_id'])) {
            $query->where('FAK_ID', (int) $input['faculty_id']);
        }

        $faculties = $query->get();

        if ($faculties->isEmpty()) {
            return ['faculties' => [], 'message' => 'No faculties found.'];
        }

        $result = $faculties->map(fn ($f) => [
            'id' => $f->FAK_ID,
            'name' => $f->FAK_EM,
            'dean' => $f->dean !== null ? trim($f->dean->PED_EM . ' ' . $f->dean->PED_MB) : '',
            'departments' => $f->departments->map(fn ($d) => [
                'id' => $d->DEP_ID,
                'name' => $d->DEP_EM,
            ])->toArray(),
        ])->toArray();

        return ['faculties' => $result];
    }
}
