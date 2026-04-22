<?php

namespace App\Http\Controllers;

use App\Http\Resources\DepartmentResource;
use App\Http\Resources\PaginatedCollection;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\Sortable;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    use ApiResponse, Sortable;

    /**
     * List departments
     *
     * Returns all departments. Pass `faculty_id` to filter by faculty.
     *
     * @group Departments
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
        $perPage = min((int) $request->query('perPage', 15), 100);

        $query = Department::query();

        if ($request->filled('facultyId')) {
            $query->where('FAK_ID', $request->integer('facultyId'));
        }

        $departments = $this->applySorting($query, $request, ['DEP_EM', 'DEP_ID'])
            ->paginate($perPage);

        return (new PaginatedCollection($departments->through(fn ($d) => new DepartmentResource($d))))->response();
    }

    /**
     * Get a department
     *
     * Returns a single department by its ID.
     *
     * @group Departments
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

    /**
     * Create a department
     *
     * @group Departments
     *
     * @response 501 {"data": null, "message": "Ende nuk është implementuar.", "status": 501}
     */
    public function store(Request $request): JsonResponse
    {
        return $this->success(null, 'Ende nuk është implementuar.', 501);
    }

    /**
     * Update a department
     *
     * @group Departments
     *
     * @response 501 {"data": null, "message": "Ende nuk është implementuar.", "status": 501}
     */
    public function update(int $id): JsonResponse
    {
        return $this->success(null, 'Ende nuk është implementuar.', 501);
    }

    /**
     * Delete a department
     *
     * @group Departments
     *
     * @response 501 {"data": null, "message": "Ende nuk është implementuar.", "status": 501}
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->success(null, 'Ende nuk është implementuar.', 501);
    }
}
