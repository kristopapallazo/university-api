<?php

namespace App\Http\Controllers\Pedagog;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Seksion;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SectionController extends Controller
{
    use ApiResponse;

    /**
     * List pedagog sections
     *
     * Returns the list of class sections (teaching slots) assigned to the authenticated pedagog,
     * ordered by day and start time. Each entry includes the course, classroom, and schedule info.
     *
     * @group Pedagogues
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": [{"id": 3, "courseId": 7, "course": "Bazat e Programimit", "day": "E Hene", "timeStart": "08:00:00", "timeEnd": "09:30:00", "roomId": 2}],
     *   "message": "Seksionet u mor\u00ebn me sukses.",
     *   "status": 200
     * }
     * @response 404 {"data": null, "message": "Nuk u gjet profili i pedagogut.", "status": 404}
     */
    public function index(): JsonResponse
    {
        $pedagog = Auth::user()->pedagog;

        if (! $pedagog) {
            return $this->error('Nuk u gjet profili i pedagogut.', 404);
        }

        $sections = Seksion::with(['lenda'])
            ->where('PED_ID', $pedagog->PED_ID)
            ->orderBy('DITA')
            ->orderBy('ORE_FILLIMI')
            ->get();

        $data = $sections->map(fn ($s) => [
            'id' => $s->SEK_ID,
            'courseId' => $s->LEND_ID,
            'course' => $s->lenda->LEND_EMER ?? null,
            'day' => $s->DITA,
            'timeStart' => $s->ORE_FILLIMI,
            'timeEnd' => $s->ORE_MBARIMI,
            'roomId' => $s->SALL_ID,
        ]);

        return $this->success($data, 'Seksionet u morën me sukses.');
    }
}
