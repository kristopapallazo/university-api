<?php

namespace App\Services\Chat\Tools;

use App\Models\Nota;
use App\Models\Student;
use App\Models\User;
use RuntimeException;

class GetMyGradesTool implements ChatTool
{
    public function name(): string
    {
        return 'get_my_grades';
    }

    public function description(): string
    {
        return "Returns the authenticated student's grades, including course name, grade value, exam type, and date. Only works for students.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'semester_id' => [
                    'type' => 'integer',
                    'description' => 'Filter grades by a specific semester ID.',
                ],
            ],
            'required' => [],
        ];
    }

    public function execute(array $input, User $user): array
    {
        if ($user->role !== 'student') {
            throw new RuntimeException('get_my_grades is only available to students.');
        }

        $student = Student::where('STU_EMAIL', $user->email)->first();
        if (! $student) {
            return ['error' => 'Student record not found for this account.'];
        }

        $query = Nota::where('STU_ID', $student->STU_ID)
            ->with(['provim.seksion.lenda', 'provim.seksion.semester']);

        if (! empty($input['semester_id'])) {
            $query->whereHas(
                'provim.seksion',
                fn ($q) => $q->where('SEM_ID', (int) $input['semester_id'])
            );
        }

        $grades = $query->orderByDesc('NOTA_DAT')->get();

        if ($grades->isEmpty()) {
            return ['grades' => [], 'message' => 'No grades found.'];
        }

        $result = $grades->map(function ($nota) {
            $seksion = $nota->provim->seksion;

            return [
                'course' => $seksion?->lenda?->LEND_EMER,
                'grade' => (float) $nota->NOTA_VLERA,
                'exam_type' => $nota->provim->TIP_EMER,
                'date' => $nota->NOTA_DAT->toDateString(),
                'semester' => $seksion?->semester?->SEM_NR,
            ];
        })->toArray();

        $average = round(collect($result)->avg('grade'), 2);

        return [
            'grades' => $result,
            'average' => $average,
            'total' => count($result),
        ];
    }
}
