<?php

namespace App\Http\Controllers;

use App\Http\Resources\FacultyResource;
use App\Http\Traits\ApiResponse;
use App\Models\Faculty;
use Illuminate\Http\JsonResponse;

class FacultyController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $faculties = Faculty::orderBy('FAK_ID')->get();

        return $this->success(
            FacultyResource::collection($faculties),
            'Fakultetet u morën me sukses.'
        );
    }

    public function show(int $id): JsonResponse
    {
        $faculty = Faculty::findOrFail($id);

        return $this->success(
            new FacultyResource($faculty),
            'Fakulteti u mor me sukses.'
        );
    }
}
