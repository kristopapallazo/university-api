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

    /**
     * Create a pedagogue
     *
     * @group Pedagogues
     *
     * @response 201 {"data": {"id": 10, "firstName": "Arben", "lastName": "Hoxha", "title": "Prof. Dr.", "email": "ahoxha@uamd.edu.al", "gender": "M", "departmentId": 4}, "message": "Pedagogu u krijua me sukses.", "status": 201}
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'firstName' => 'required|string|max:100',
            'lastName' => 'required|string|max:100',
            'gender' => 'required|in:M,F',
            'title' => 'required|string|max:100',
            'email' => 'required|email|unique:PEDAGOG,PED_EMAIL',
            'phone' => 'nullable|string|max:20',
            'birthDate' => 'nullable|date',
            'hireDate' => 'nullable|date',
            'departmentId' => 'required|integer|exists:DEPARTAMENT,DEP_ID',
        ]);

        $pedagog = Pedagog::create([
            'PED_EM' => $data['firstName'],
            'PED_MB' => $data['lastName'],
            'PED_GJINI' => $data['gender'],
            'PED_TITULLI' => $data['title'],
            'PED_EMAIL' => $data['email'],
            'PED_TEL' => $data['phone'] ?? null,
            'PED_DTL' => $data['birthDate'] ?? null,
            'PED_DT_PUNESIM' => $data['hireDate'] ?? null,
            'DEP_ID' => $data['departmentId'],
        ]);

        return $this->success(new PedagogResource($pedagog), 'Pedagogu u krijua me sukses.', 201);
    }

    /**
     * Update a pedagogue
     *
     * @group Pedagogues
     *
     * @response 200 {"data": {"id": 1, "firstName": "Arben", "lastName": "Hoxha", "title": "Prof. Dr.", "email": "ahoxha@uamd.edu.al", "gender": "M", "departmentId": 4}, "message": "Pedagogu u përditësua me sukses.", "status": 200}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $pedagog = Pedagog::findOrFail($id);

        $data = $request->validate([
            'firstName' => 'sometimes|required|string|max:100',
            'lastName' => 'sometimes|required|string|max:100',
            'gender' => 'sometimes|required|in:M,F',
            'title' => 'sometimes|required|string|max:100',
            'email' => 'sometimes|required|email|unique:PEDAGOG,PED_EMAIL,' . $id . ',PED_ID',
            'phone' => 'nullable|string|max:20',
            'birthDate' => 'nullable|date',
            'hireDate' => 'nullable|date',
            'departmentId' => 'sometimes|required|integer|exists:DEPARTAMENT,DEP_ID',
        ]);

        $pedagog->update([
            'PED_EM' => $data['firstName'] ?? $pedagog->PED_EM,
            'PED_MB' => $data['lastName'] ?? $pedagog->PED_MB,
            'PED_GJINI' => $data['gender'] ?? $pedagog->PED_GJINI,
            'PED_TITULLI' => $data['title'] ?? $pedagog->PED_TITULLI,
            'PED_EMAIL' => $data['email'] ?? $pedagog->PED_EMAIL,
            'PED_TEL' => array_key_exists('phone', $data) ? $data['phone'] : $pedagog->PED_TEL,
            'PED_DTL' => array_key_exists('birthDate', $data) ? $data['birthDate'] : $pedagog->PED_DTL,
            'PED_DT_PUNESIM' => array_key_exists('hireDate', $data) ? $data['hireDate'] : $pedagog->PED_DT_PUNESIM,
            'DEP_ID' => $data['departmentId'] ?? $pedagog->DEP_ID,
        ]);

        return $this->success(new PedagogResource($pedagog->fresh()), 'Pedagogu u përditësua me sukses.');
    }

    /**
     * Delete a pedagogue
     *
     * @group Pedagogues
     *
     * @response 200 {"data": null, "message": "Pedagogu u fshi me sukses.", "status": 200}
     */
    public function destroy(int $id): JsonResponse
    {
        $pedagog = Pedagog::findOrFail($id);
        $pedagog->delete();

        return $this->success(null, 'Pedagogu u fshi me sukses.');
    }
}
