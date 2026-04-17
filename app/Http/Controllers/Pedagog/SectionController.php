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
