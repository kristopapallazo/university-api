<?php

namespace App\Http\Controllers;

use App\Http\Resources\FacultyResource;
use App\Http\Resources\PaginatedCollection;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\Sortable;
use App\Models\Faculty;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    use ApiResponse, Sortable;

    /**
     * List faculties
     *
     * Returns all 6 UAMD faculties ordered by ID.
     *
     * @group Faculties
     *
     * @response 200 {
     *   "data": [{"id": 1, "name": "Fakulteti i Shkencave t\u00eb Biznesit"}, {"id": 2, "name": "Fakulteti i Shkencave Teknike"}],
     *   "message": "Fakultetet u mor\u00ebn me sukses.",
     *   "status": 200
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('perPage', 15), 100);

        $faculties = $this->applySorting(Faculty::query(), $request, ['FAK_EM', 'FAK_ID'])
            ->paginate($perPage);

        return (new PaginatedCollection($faculties->through(fn ($f) => new FacultyResource($f))))->response();
    }

    /**
     * Get a faculty
     *
     * Returns a single faculty by its ID.
     *
     * @group Faculties
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

    /**
     * Create a faculty
     *
     * @group Faculties
     *
     * @response 501 {"data": null, "message": "Ende nuk është implementuar.", "status": 501}
     */
    public function store(Request $request): JsonResponse
    {
        return $this->success(null, 'Ende nuk është implementuar.', 501);
    }

    /**
     * Update a faculty
     *
     * @group Faculties
     *
     * @response 501 {"data": null, "message": "Ende nuk është implementuar.", "status": 501}
     */
    public function update(int $id): JsonResponse
    {
        return $this->success(null, 'Ende nuk është implementuar.', 501);
    }

    /**
     * Delete a faculty
     *
     * @group Faculties
     *
     * @response 501 {"data": null, "message": "Ende nuk është implementuar.", "status": 501}
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->success(null, 'Ende nuk është implementuar.', 501);
    }
}
