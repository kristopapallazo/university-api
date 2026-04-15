<?php

namespace App\Http\Controllers;

use App\Http\Resources\FacultyResource;
use App\Http\Traits\ApiResponse;
use App\Models\Faculty;
use Illuminate\Http\JsonResponse;

class FacultyController extends Controller
{
    use ApiResponse;

    /**
     * List faculties
     *
     * Returns all 6 UAMD faculties ordered by ID.
     *
     * @group Reference Data
     *
     * @response 200 {
     *   "data": [{"id": 1, "name": "Fakulteti i Shkencave t\u00eb Biznesit"}, {"id": 2, "name": "Fakulteti i Shkencave Teknike"}],
     *   "message": "Fakultetet u mor\u00ebn me sukses.",
     *   "status": 200
     * }
     */
    public function index(): JsonResponse
    {
        $faculties = Faculty::orderBy('FAK_ID')->get();

        return $this->success(
            FacultyResource::collection($faculties),
            'Fakultetet u morën me sukses.'
        );
    }

    /**
     * Get a faculty
     *
     * Returns a single faculty by its ID.
     *
     * @group Reference Data
     *
     * @response 200 {"data": {"id": 1, "name": "Fakulteti i Shkencave t\u00eb Biznesit"}, "message": "Fakulteti u mor me sukses.", "status": 200}
     * @response 404 {"data": null, "message": "Not Found", "status": 404}
     */
    public function show(int $id): JsonResponse
    {
        $faculty = Faculty::findOrFail($id);

        return $this->success(
            new FacultyResource($faculty),
            'Fakulteti u mor me sukses.'
        );
    }

    /** @group Reference Data */
    public function store(): JsonResponse
    {
        return $this->success(null, 'Ende nuk është implementuar.', 501);
    }

    /** @group Reference Data */
    public function update(int $id): JsonResponse
    {
        return $this->success(null, 'Ende nuk është implementuar.', 501);
    }

    /** @group Reference Data */
    public function destroy(int $id): JsonResponse
    {
        return $this->success(null, 'Ende nuk është implementuar.', 501);
    }
}
