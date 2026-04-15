<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProgramStudimResource;
use App\Http\Traits\ApiResponse;
use App\Models\ProgramStudim;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgramStudimController extends Controller
{
    use ApiResponse;

    /**
     * List study programs
     *
     * Returns all study programs. Pass `department_id` to filter by department.
     *
     * @group Reference Data
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
        $query = ProgramStudim::query()->orderBy('PROG_ID');

        if ($request->filled('departmentId')) {
            $query->where('DEP_ID', $request->integer('departmentId'));
        }

        return $this->success(
            ProgramStudimResource::collection($query->get()),
            'Programet e studimit u morën me sukses.'
        );
    }

    /**
     * Get a study program
     *
     * Returns a single study program by its ID.
     *
     * @group Reference Data
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
}
