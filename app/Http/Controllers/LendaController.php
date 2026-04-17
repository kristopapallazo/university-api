<?php

namespace App\Http\Controllers;

use App\Http\Resources\LendaResource;
use App\Http\Traits\ApiResponse;
use App\Models\Lenda;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LendaController extends Controller
{
    use ApiResponse;

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
        $query = Lenda::query()->orderBy('LEND_ID');

        if ($request->filled('departmentId')) {
            $query->where('DEP_ID', $request->integer('departmentId'));
        }

        return $this->success(
            LendaResource::collection($query->get()),
            'Lëndët u morën me sukses.'
        );
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
}
