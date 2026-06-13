<?php

namespace App\Services\Chat\Tools;

use App\Models\Student;
use App\Models\User;

class GetUserProfileTool implements ChatTool
{
    public function name(): string
    {
        return 'get_user_profile';
    }

    public function description(): string
    {
        return 'Returns basic profile info about the currently authenticated user (name, email, role, and if student: matriculation number, enrolment status, program).';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => new \stdClass,
            'required' => [],
        ];
    }

    public function execute(array $input, User $user): array
    {
        $profile = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ];

        if ($user->role === 'student') {
            $student = Student::where('STU_EMAIL', $user->email)->first();
            if ($student) {
                $profile['matriculation_number'] = $student->STU_NR_MATRIKULL;
                $profile['status'] = $student->STU_STATUS;
            }
        }

        return $profile;
    }
}
