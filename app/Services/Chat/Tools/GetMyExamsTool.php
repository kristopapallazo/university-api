<?php

namespace App\Services\Chat\Tools;

use App\Models\Provim;
use App\Models\Regjistrim;
use App\Models\Student;
use App\Models\User;
use RuntimeException;

class GetMyExamsTool implements ChatTool
{
    public function name(): string
    {
        return 'get_my_exams';
    }

    public function description(): string
    {
        return "Returns the authenticated student's exams (upcoming or all), including course name, exam type, and date. Only works for students.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'upcoming' => [
                    'type' => 'boolean',
                    'description' => 'If true, return only exams scheduled from today onwards. Defaults to true.',
                ],
            ],
            'required' => [],
        ];
    }

    public function execute(array $input, User $user): array
    {
        if ($user->role !== 'student') {
            throw new RuntimeException('get_my_exams is only available to students.');
        }

        $student = Student::where('STU_EMAIL', $user->email)->first();
        if (! $student) {
            return ['error' => 'Student record not found for this account.'];
        }

        // Get the section IDs the student is registered for
        $sectionIds = Regjistrim::where('STU_ID', $student->STU_ID)
            ->pluck('SEK_ID');

        if ($sectionIds->isEmpty()) {
            return ['exams' => [], 'message' => 'No exam registrations found.'];
        }

        $upcoming = $input['upcoming'] ?? true;

        $query = Provim::whereIn('SEK_ID', $sectionIds)
            ->with(['seksion.lenda']);

        if ($upcoming) {
            $query->whereDate('DAT_PROVIM', '>=', now()->toDateString());
        }

        $exams = $query->orderBy('DAT_PROVIM')->get();

        if ($exams->isEmpty()) {
            $msg = $upcoming ? 'No upcoming exams found.' : 'No exams found.';

            return ['exams' => [], 'message' => $msg];
        }

        $result = $exams->map(fn ($p) => [
            'course' => $p->seksion?->lenda?->LEND_EMER,
            'type' => $p->TIP_EMER,
            'date' => $p->DAT_PROVIM->toDateString(),
        ])->toArray();

        return ['exams' => $result];
    }
}
