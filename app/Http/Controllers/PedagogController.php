<?php

namespace App\Http\Controllers;

use App\Http\Resources\PedagogResource;
use App\Http\Traits\ApiResponse;
use App\Models\Pedagog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PedagogController extends Controller
{
    use ApiResponse;

    /**
     * List pedagogues
     *
     * Returns all pedagogues. Pass `department_id` to filter by department.
     *
     * @group Reference Data
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
        $query = Pedagog::query()->orderBy('PED_ID');

        if ($request->filled('departmentId')) {
            $query->where('DEP_ID', $request->integer('departmentId'));
        }

        return $this->success(
            PedagogResource::collection($query->get()),
            'Pedagogët u morën me sukses.'
        );
    }

    /**
     * Get a pedagogue
     *
     * Returns a single pedagogue by their ID.
     *
     * @group Reference Data
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
