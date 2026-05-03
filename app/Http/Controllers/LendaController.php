<?php

namespace App\Http\Controllers;

use App\Http\Resources\LendaResource;
use App\Http\Resources\PaginatedCollection;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\Sortable;
use App\Models\Lenda;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LendaController extends Controller
{
    use ApiResponse, Sortable;

    /**
     * List courses
     *
     * Returns all courses. Pass `department_id` to filter by department.
     *
     * @group Courses
     *
     * @queryParam departmentId integer optional Filter by department. Example: 4
     *
     * @response 200 {
     *   "data": [{"id": 1, "name": "Algoritmika", "code": "INF101", "departmentId": 4}],
     *   "message": "L\u00ebnd\u00ebt u mor\u00ebn me sukses.",
     *   "status": 200
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('perPage', 15), 100);

        $query = Lenda::query();

        if ($request->filled('departmentId')) {
            $query->where('DEP_ID', $request->integer('departmentId'));
        }

        $courses = $this->applySorting($query, $request, ['LEND_EM', 'LEND_KOD', 'LEND_ID'])
            ->paginate($perPage);

        return (new PaginatedCollection($courses->through(fn ($l) => new LendaResource($l))))->response();
    }

    /**
     * Get a course
     *
     * Returns a single course by its ID.
     *
     * @group Courses
     *
     * @response 200 {"data": {"id": 1, "name": "Algoritmika", "code": "INF101", "departmentId": 4}, "message": "L\u00ebnda u mor me sukses.", "status": 200}
     * @response 404 {"data": null, "message": "Rekordi nuk u gjet.", "status": 404}
     */
    public function show(int $id): JsonResponse
    {
        $lenda = Lenda::findOrFail($id);

        return $this->success(
            new LendaResource($lenda),
            'Lënda u mor me sukses.'
        );
    }

    /**
     * Create a course
     *
     * @group Courses
     *
     * @response 201 {"data": {"id": 10, "name": "Algoritmika", "code": "INF101", "departmentId": 4}, "message": "Lënda u krijua me sukses.", "status": 201}
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:20|unique:LENDA,LEND_KOD',
            'departmentId' => 'required|integer|exists:DEPARTAMENT,DEP_ID',
        ]);

        $lenda = Lenda::create([
            'LEND_EMER' => $data['name'],
            'LEND_KOD' => strtoupper($data['code']),
            'DEP_ID' => $data['departmentId'],
        ]);

        return $this->success(new LendaResource($lenda), 'Lënda u krijua me sukses.', 201);
    }

    /**
     * Update a course
     *
     * @group Courses
     *
     * @response 200 {"data": {"id": 1, "name": "Algoritmika e Avancuar", "code": "INF101", "departmentId": 4}, "message": "Lënda u përditësua me sukses.", "status": 200}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $lenda = Lenda::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'code' => 'sometimes|required|string|max:20|unique:LENDA,LEND_KOD,' . $id . ',LEND_ID',
            'departmentId' => 'sometimes|required|integer|exists:DEPARTAMENT,DEP_ID',
        ]);

        $lenda->update([
            'LEND_EMER' => $data['name'] ?? $lenda->LEND_EMER,
            'LEND_KOD' => isset($data['code']) ? strtoupper($data['code']) : $lenda->LEND_KOD,
            'DEP_ID' => $data['departmentId'] ?? $lenda->DEP_ID,
        ]);

        return $this->success(new LendaResource($lenda->fresh()), 'Lënda u përditësua me sukses.');
    }

    /**
     * Delete a course
     *
     * @group Courses
     *
     * @response 200 {"data": null, "message": "Lënda u fshi me sukses.", "status": 200}
     */
    public function destroy(int $id): JsonResponse
    {
        $lenda = Lenda::findOrFail($id);
        $lenda->delete();

        return $this->success(null, 'Lënda u fshi me sukses.');
    }
}
