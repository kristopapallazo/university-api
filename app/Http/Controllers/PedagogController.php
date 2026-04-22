<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaginatedCollection;
use App\Http\Resources\PedagogResource;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\Sortable;
use App\Models\Pedagog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PedagogController extends Controller
{
    use ApiResponse, Sortable;

    /**
     * List pedagogues
     *
     * Returns all pedagogues. Pass `department_id` to filter by department.
     *
     * @group Pedagogues
     *
     * @queryParam departmentId integer optional Filter by department. Example: 4
     *
     * @response 200 {
     *   "data": [{"id": 1, "firstName": "Arben", "lastName": "Hoxha", "title": "Prof. Dr.", "email": "ahoxha@uamd.edu.al", "gender": "M", "departmentId": 4}],
     *   "message": "Pedagog\u00ebt u mor\u00ebn me sukses.",
     *   "status": 200
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('perPage', 15), 100);

        $query = Pedagog::query();

        if ($request->filled('departmentId')) {
            $query->where('DEP_ID', $request->integer('departmentId'));
        }

        $pedagogues = $this->applySorting($query, $request, ['PED_EMER', 'PED_MBIEMER', 'PED_ID'])
            ->paginate($perPage);

        return (new PaginatedCollection($pedagogues->through(fn ($p) => new PedagogResource($p))))->response();
    }

    /**
     * Get a pedagogue
     *
     * Returns a single pedagogue by their ID.
     *
     * @group Pedagogues
     *
     * @response 200 {"data": {"id": 1, "firstName": "Arben", "lastName": "Hoxha", "title": "Prof. Dr.", "email": "ahoxha@uamd.edu.al", "gender": "M", "departmentId": 4}, "message": "Pedagogu u mor me sukses.", "status": 200}
     * @response 404 {"data": null, "message": "Rekordi nuk u gjet.", "status": 404}
     */
    public function show(int $id): JsonResponse
    {
        $pedagog = Pedagog::findOrFail($id);

        return $this->success(
            new PedagogResource($pedagog),
            'Pedagogu u mor me sukses.'
        );
    }
}
