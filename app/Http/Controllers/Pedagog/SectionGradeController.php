<?php

namespace App\Http\Controllers\Pedagog;

use App\Http\Controllers\Controller;
use App\Http\Resources\SectionGradeResource;
use App\Http\Traits\ApiResponse;
use App\Models\Nota;
use App\Models\Seksion;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SectionGradeController extends Controller
{
    use ApiResponse;

    /**
     * List grades for a section
     *
     * Returns grades for all students in the given section. The authenticated
     * pedagog must teach that section — otherwise 403 is returned.
     *
     * @group Reports
     *
     * @authenticated
     *
     * @urlParam sectionId integer required The section ID. Example: 5
     *
     * @response 200 {
     *   "data": [{"gradeId": 1, "value": 9.0, "date": "2026-01-15", "examType": "Final", "student": {"id": 12, "firstName": "Arta", "lastName": "Hoxha", "matriculationNumber": "2021001234"}}],
     *   "message": "Notat u mor\u00ebn me sukses.",
     *   "status": 200
     * }
     * @response 403 {"data": null, "message": "Nuk keni leje p\u00ebr k\u00ebt\u00eb seksion.", "status": 403}
     * @response 404 {"data": null, "message": "Seksioni nuk u gjet.", "status": 404}
     */
    public function index(int $sectionId): JsonResponse
    {
        $pedagog = Auth::user()->pedagog;

        if (! $pedagog) {
            return $this->error('Nuk keni leje për këtë seksion.', 403);
        }

        $section = Seksion::findOrFail($sectionId);

        if ($section->PED_ID !== $pedagog->PED_ID) {
            return $this->error('Nuk keni leje për këtë seksion.', 403);
        }

        $grades = Nota::with(['provim', 'student'])
            ->whereHas('provim', function ($query) use ($sectionId) {
                $query->where('SEK_ID', $sectionId);
            })
            ->orderByDesc('NOTA_DAT')
            ->get();

        return $this->success(
            SectionGradeResource::collection($grades),
            'Notat u morën me sukses.'
        );
    }
}
