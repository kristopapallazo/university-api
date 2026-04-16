<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\GradeResource;
use App\Http\Traits\ApiResponse;
use App\Models\Nota;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    use ApiResponse;

    /**
     * List my grades
     *
     * Returns all grades for the authenticated student, sorted by date desc.
     *
     * @group Student Reports
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": [{"gradeId": 1, "value": 8.5, "date": "2026-01-15", "examType": "Final", "examDate": "2026-01-14", "course": "Bazat e Programimit"}],
     *   "message": "Notat u mor\u00ebn me sukses.",
     *   "status": 200
     * }
     */
    public function index(): JsonResponse
    {
        $studentId = Auth::user()->student->STU_ID;

        $grades = Nota::with('provim.seksion.lenda')
            ->where('STU_ID', $studentId)
            ->orderByDesc('NOTA_DAT')
            ->get();

        return $this->success(
            GradeResource::collection($grades),
            'Notat u morën me sukses.'
        );
    }
}
