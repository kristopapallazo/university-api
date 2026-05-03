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
     * @response 201 {"data": {"id": 7, "name": "Fakulteti i Ri"}, "message": "Fakulteti u krijua me sukses.", "status": 201}
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'deanId' => 'nullable|integer|exists:PEDAGOG,PED_ID',
        ]);

        $faculty = Faculty::create([
            'FAK_EM' => $data['name'],
            'PED_ID' => $data['deanId'] ?? null,
        ]);

        return $this->success(new FacultyResource($faculty), 'Fakulteti u krijua me sukses.', 201);
    }

    /**
     * Update a faculty
     *
     * @group Faculties
     *
     * @response 200 {"data": {"id": 1, "name": "Emër i ri"}, "message": "Fakulteti u përditësua me sukses.", "status": 200}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $faculty = Faculty::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'deanId' => 'nullable|integer|exists:PEDAGOG,PED_ID',
        ]);

        $faculty->update([
            'FAK_EM' => $data['name'] ?? $faculty->FAK_EM,
            'PED_ID' => array_key_exists('deanId', $data) ? $data['deanId'] : $faculty->PED_ID,
        ]);

        return $this->success(new FacultyResource($faculty->fresh()), 'Fakulteti u përditësua me sukses.');
    }

    /**
     * Delete a faculty
     *
     * @group Faculties
     *
     * @response 200 {"data": null, "message": "Fakulteti u fshi me sukses.", "status": 200}
     */
    public function destroy(int $id): JsonResponse
    {
        $faculty = Faculty::findOrFail($id);
        $faculty->delete();

        return $this->success(null, 'Fakulteti u fshi me sukses.');
    }
}
