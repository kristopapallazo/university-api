<?php

namespace App\Services;

use App\Exceptions\EntityNotFoundException;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Lenda;
use App\Models\Pedagog;
use App\Models\ProgramStudim;
use App\Models\Student;

class ValidationService
{
    /**
     * Validate that a faculty exists by ID.
     *
     * @throws EntityNotFoundException if not found
     */
    public function validateFacultyExists(int $facultyId): bool
    {
        if (! Faculty::find($facultyId)) {
            throw new EntityNotFoundException('Fakultet', $facultyId);
        }

        return true;
    }

    /**
     * Validate that a department exists by ID.
     *
     * @throws EntityNotFoundException if not found
     */
    public function validateDepartmentExists(int $departmentId): bool
    {
        if (! Department::find($departmentId)) {
            throw new EntityNotFoundException('Departament', $departmentId);
        }

        return true;
    }

    /**
     * Validate that a program (programi studim) exists by ID.
     *
     * @throws EntityNotFoundException if not found
     */
    public function validateProgramExists(int $programId): bool
    {
        if (! ProgramStudim::find($programId)) {
            throw new EntityNotFoundException('Program Studim', $programId);
        }

        return true;
    }

    /**
     * Validate that a course (lende) exists by ID.
     *
     * @throws EntityNotFoundException if not found
     */
    public function validateCourseExists(int $courseId): bool
    {
        if (! Lenda::find($courseId)) {
            throw new EntityNotFoundException('Lende', $courseId);
        }

        return true;
    }

    /**
     * Validate that a student exists by ID.
     *
     * @throws EntityNotFoundException if not found
     */
    public function validateStudentExists(int $studentId): bool
    {
        if (! Student::find($studentId)) {
            throw new EntityNotFoundException('Student', $studentId);
        }

        return true;
    }

    /**
     * Validate that a pedagog (lecturer) exists by ID.
     *
     * @throws EntityNotFoundException if not found
     */
    public function validatePedagogExists(int $pedagogId): bool
    {
        if (! Pedagog::find($pedagogId)) {
            throw new EntityNotFoundException('Pedagog', $pedagogId);
        }

        return true;
    }
}
