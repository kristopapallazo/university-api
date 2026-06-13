<?php

namespace App\Services\Chat\Tools;

use App\Models\Regjistrim;
use App\Models\Student;
use App\Models\User;
use RuntimeException;

class GetMyScheduleTool implements ChatTool
{
    public function name(): string
    {
        return 'get_my_schedule';
    }

    public function description(): string
    {
        return "Returns the authenticated student's class schedule: day of week, time, course name, lecturer, and room. Only works for students.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'semester_id' => [
                    'type' => 'integer',
                    'description' => 'Filter by a specific semester ID. Omit to return all active sections.',
                ],
            ],
            'required' => [],
        ];
    }

    public function execute(array $input, User $user): array
    {
        if ($user->role !== 'student') {
            throw new RuntimeException('get_my_schedule is only available to students.');
        }

        $student = Student::where('STU_EMAIL', $user->email)->first();
        if (! $student) {
            return ['error' => 'Student record not found for this account.'];
        }

        $query = Regjistrim::where('STU_ID', $student->STU_ID)
            ->with([
                'seksion.lenda',
                'seksion.pedagog',
                'seksion.semester',
                'seksion.sala.salle',
            ]);

        if (! empty($input['semester_id'])) {
            $query->whereHas('seksion', fn ($q) => $q->where('SEM_ID', (int) $input['semester_id']));
        }

        $registrations = $query->get();

        if ($registrations->isEmpty()) {
            return ['schedule' => [], 'message' => 'No schedule entries found.'];
        }

        $schedule = $registrations->map(function ($reg) {
            $sek = $reg->seksion;

            return [
                'day' => $sek->DITA,
                'start' => $sek->ORE_FILLIMI,
                'end' => $sek->ORE_MBARIMI,
                'course' => $sek->lenda?->LEND_EMER,
                'lecturer' => $sek->pedagog !== null ? trim($sek->pedagog->PED_EM . ' ' . $sek->pedagog->PED_MB) : '',
                'room' => $sek->sala?->salle?->SALLE_NR,
                'semester' => $sek->semester?->SEM_NR,
            ];
        })->sortBy(['day', 'start'])->values()->toArray();

        return ['schedule' => $schedule];
    }
}
