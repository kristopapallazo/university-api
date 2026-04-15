<?php

namespace App\Http\Controllers;

use App\Http\Resources\DepartmentResource;
use App\Http\Traits\ApiResponse;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    use ApiResponse;

    /**
     * List departments
     *
     * Returns all departments. Pass `faculty_id` to filter by faculty.
     *
     * @group Reference Data
     *
     * @queryParam facultyId integer optional Filter by faculty. Example: 2
     *
     * @response 200 {
     *   "data": [{"id": 4, "name": "Departamenti i Informatik\u00ebs", "facultyId": 2}],
     *   "message": "Departamentet u mor\u00ebn me sukses.",
     *   "status": 200
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $query = Department::query()->orderBy('DEP_ID');

        if ($request->filled('facultyId')) {
            $query->where('FAK_ID', $request->integer('facultyId'));
        }

        return $this->success(
            DepartmentResource::collection($query->get()),
            'Departamentet u morën me sukses.'
        );
    }

    /**
     * Get a department
     *
     * Returns a single department by its ID.
     *
     * @group Reference Data
     *
     * @response 200 {"data": {"id": 4, "name": "Departamenti i Informatik\u00ebs", "facultyId": 2}, "message": "Departamenti u mor me sukses.", "status": 200}
     * @response 404 {"data": null, "message": "Not Found", "status": 404}
     */
    public function show(int $id): JsonResponse
    {
        $department = Department::findOrFail($id);

        return $this->success(
            new DepartmentResource($department),
            'Departamenti u mor me sukses.'
        );
    }

    /** @group Departments */
    public function store(Request $request): JsonResponse
    {
        return $this->success(null, 'Ende nuk është implementuar.', 501);
    }

    /** @group Departments */
    public function update(int $id): JsonResponse
    {
        return $this->success(null, 'Ende nuk është implementuar.', 501);
    }

    /** @group Departments */
    public function destroy(int $id): JsonResponse
    {
        return $this->success(null, 'Ende nuk është implementuar.', 501);
    }
}
