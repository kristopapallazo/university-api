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
}
