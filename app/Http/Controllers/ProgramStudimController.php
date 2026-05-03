<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaginatedCollection;
use App\Http\Resources\ProgramStudimResource;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\Sortable;
use App\Models\ProgramStudim;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgramStudimController extends Controller
{
    use ApiResponse, Sortable;

    /**
     * List study programs
     *
     * Returns all study programs. Pass `department_id` to filter by department.
     *
     * @group Programs
     *
     * @queryParam departmentId integer optional Filter by department. Example: 4
     *
     * @response 200 {
     *   "data": [{"id": 1, "name": "Informatik\u00eb", "level": "Bachelor", "credits": 180, "departmentId": 4}],
     *   "message": "Programet e studimit u mor\u00ebn me sukses.",
     *   "status": 200
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('perPage', 15), 100);

        $query = ProgramStudim::query();

        if ($request->filled('departmentId')) {
            $query->where('DEP_ID', $request->integer('departmentId'));
        }

        $programs = $this->applySorting($query, $request, ['PROG_EM', 'PROG_LLOJI', 'PROG_ID'])
            ->paginate($perPage);

        return (new PaginatedCollection($programs->through(fn ($p) => new ProgramStudimResource($p))))->response();
    }

    /**
     * Get a study program
     *
     * Returns a single study program by its ID.
     *
     * @group Programs
     *
     * @response 200 {"data": {"id": 1, "name": "Informatik\u00eb", "level": "Bachelor", "credits": 180, "departmentId": 4}, "message": "Programi i studimit u mor me sukses.", "status": 200}
     * @response 404 {"data": null, "message": "Rekordi nuk u gjet.", "status": 404}
     */
    public function show(int $id): JsonResponse
    {
        $program = ProgramStudim::findOrFail($id);

        return $this->success(
            new ProgramStudimResource($program),
            'Programi i studimit u mor me sukses.'
        );
    }

    /**
     * Create a study program
     *
     * @group Programs
     *
     * @response 201 {"data": {"id": 10, "name": "Programi i Ri", "level": "Bachelor", "credits": 180, "departmentId": 4}, "message": "Programi i studimit u krijua me sukses.", "status": 201}
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|in:Bachelor,Master,Doktorature',
            'credits' => 'required|integer|min:1',
            'departmentId' => 'required|integer|exists:DEPARTAMENT,DEP_ID',
        ]);

        $program = ProgramStudim::create([
            'PROG_EM' => $data['name'],
            'PROG_NIV' => $data['level'],
            'PROG_KRD' => $data['credits'],
            'DEP_ID' => $data['departmentId'],
        ]);

        return $this->success(new ProgramStudimResource($program), 'Programi i studimit u krijua me sukses.', 201);
    }

    /**
     * Update a study program
     *
     * @group Programs
     *
     * @response 200 {"data": {"id": 1, "name": "Emër i ri", "level": "Master", "credits": 120, "departmentId": 4}, "message": "Programi i studimit u përditësua me sukses.", "status": 200}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $program = ProgramStudim::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'level' => 'sometimes|required|in:Bachelor,Master,Doktorature',
            'credits' => 'sometimes|required|integer|min:1',
            'departmentId' => 'sometimes|required|integer|exists:DEPARTAMENT,DEP_ID',
        ]);

        $program->update([
            'PROG_EM' => $data['name'] ?? $program->PROG_EM,
            'PROG_NIV' => $data['level'] ?? $program->PROG_NIV,
            'PROG_KRD' => $data['credits'] ?? $program->PROG_KRD,
            'DEP_ID' => $data['departmentId'] ?? $program->DEP_ID,
        ]);

        return $this->success(new ProgramStudimResource($program->fresh()), 'Programi i studimit u përditësua me sukses.');
    }

    /**
     * Delete a study program
     *
     * @group Programs
     *
     * @response 200 {"data": null, "message": "Programi i studimit u fshi me sukses.", "status": 200}
     */
    public function destroy(int $id): JsonResponse
    {
        $program = ProgramStudim::findOrFail($id);
        $program->delete();

        return $this->success(null, 'Programi i studimit u fshi me sukses.');
    }
}
