<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaginatedCollection;
use App\Http\Resources\StudentResource;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\Sortable;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    use ApiResponse, Sortable;

    /**
     * List students
     *
     * Returns paginated students with optional status filter and sorting.
     *
     * @group Students
     *
     * @queryParam perPage integer optional Items per page (max 100, default 15). Example: 20
     * @queryParam status string optional Filter by status: Aktiv, Pezulluar, I diplomuar, Ç'regjistruar. Example: Aktiv
     * @queryParam sort string optional Field to sort by (STU_MB, STU_EM, STU_ID). Example: STU_MB
     *
     * @response 200 {
     *   "data": [{"id": 1, "firstName": "Arta", "lastName": "Hoxha", "email": "ahoxha@students.uamd.edu.al", "status": "Aktiv"}],
     *   "meta": {"currentPage": 1, "perPage": 15, "total": 1, "lastPage": 1},
     *   "message": "OK",
     *   "status": 200
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('perPage', 15), 100);

        $query = Student::query();

        if ($request->filled('status')) {
            $query->where('STU_STATUS', $request->query('status'));
        }

        $students = $this->applySorting($query, $request, ['STU_MB', 'STU_EM', 'STU_ID'])
            ->paginate($perPage);

        return (new PaginatedCollection($students->through(fn ($s) => new StudentResource($s))))->response();
    }

    /**
     * Get a student
     *
     * Returns a single student by ID.
     *
     * @group Students
     *
     * @response 200 {"data": {"id": 1, "firstName": "Arta", "lastName": "Hoxha", "email": "ahoxha@students.uamd.edu.al", "status": "Aktiv"}, "message": "Studenti u mor me sukses.", "status": 200}
     * @response 404 {"data": null, "message": "Not Found", "status": 404}
     */
    public function show(int $id): JsonResponse
    {
        $student = Student::findOrFail($id);

        return $this->success(new StudentResource($student), 'Studenti u mor me sukses.');
    }

    /**
     * Create a student
     *
     * Creates a STUDENT row and a users (auth) row in a single transaction.
     * Students authenticate via Google OAuth — no password is set.
     *
     * @group Students
     *
     * @bodyParam firstName string required Example: Arta
     * @bodyParam lastName string required Example: Hoxha
     * @bodyParam fathersName string optional Example: Ilir
     * @bodyParam gender string required One of: M, F. Example: F
     * @bodyParam birthDate date required Example: 2003-05-12
     * @bodyParam matriculation string required Unique. Example: 23.1.1.001
     * @bodyParam email string required Unique. Example: ahoxha@students.uamd.edu.al
     * @bodyParam phone string optional Example: +355691234567
     * @bodyParam enrolledAt date optional Defaults to today. Example: 2024-10-01
     * @bodyParam dormRoomId integer optional Example: 12
     *
     * @response 201 {"data": {"id": 1, "firstName": "Arta", "lastName": "Hoxha", "email": "ahoxha@students.uamd.edu.al", "status": "Aktiv"}, "message": "Studenti u regjistrua me sukses.", "status": 201}
     * @response 422 {"message": "The given data was invalid.", "errors": {}}
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'firstName' => 'required|string|max:100',
            'lastName' => 'required|string|max:100',
            'fathersName' => 'nullable|string|max:100',
            'gender' => 'required|in:M,F',
            'birthDate' => 'required|date',
            'matriculation' => 'required|string|max:20|unique:STUDENT,STU_NR_MATRIKULL',
            'email' => 'required|email|unique:STUDENT,STU_EMAIL|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'enrolledAt' => 'nullable|date',
            'dormRoomId' => 'nullable|integer|exists:DHOME,DHOM_ID',
        ]);

        $student = DB::transaction(function () use ($data) {
            $student = Student::create([
                'STU_EM' => $data['firstName'],
                'STU_MB' => $data['lastName'],
                'STU_ATESI' => $data['fathersName'] ?? null,
                'STU_GJINI' => $data['gender'],
                'STU_DTL' => $data['birthDate'],
                'STU_NR_MATRIKULL' => $data['matriculation'],
                'STU_EMAIL' => $data['email'],
                'STU_TEL' => $data['phone'] ?? null,
                'STU_DAT_REGJISTRIM' => $data['enrolledAt'] ?? now()->toDateString(),
                'STU_STATUS' => 'Aktiv',
                'DHOM_ID' => $data['dormRoomId'] ?? null,
            ]);

            User::create([
                'name' => $data['firstName'] . ' ' . $data['lastName'],
                'email' => $data['email'],
                'password' => null,
                'role' => 'student',
                'provider' => 'google',
            ]);

            return $student;
        });

        return $this->success(new StudentResource($student), 'Studenti u regjistrua me sukses.', 201);
    }

    /**
     * Update a student
     *
     * Updates profile fields only. Email is intentionally excluded —
     * it is the link to the users (auth) row and changing it would break login.
     *
     * @group Students
     *
     * @response 200 {"data": {"id": 1, "firstName": "Arta", "lastName": "Hoxha", "status": "Aktiv"}, "message": "Studenti u përditësua me sukses.", "status": 200}
     * @response 404 {"data": null, "message": "Not Found", "status": 404}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $student = Student::findOrFail($id);

        $data = $request->validate([
            'firstName' => 'sometimes|required|string|max:100',
            'lastName' => 'sometimes|required|string|max:100',
            'fathersName' => 'nullable|string|max:100',
            'gender' => 'sometimes|required|in:M,F',
            'birthDate' => 'sometimes|required|date',
            'phone' => 'nullable|string|max:20',
            'status' => 'sometimes|required|in:Aktiv,Pezulluar,I diplomuar,Ç\'regjistruar',
            'dormRoomId' => 'nullable|integer|exists:DHOME,DHOM_ID',
        ]);

        $student->update([
            'STU_EM' => $data['firstName'] ?? $student->STU_EM,
            'STU_MB' => $data['lastName'] ?? $student->STU_MB,
            'STU_ATESI' => array_key_exists('fathersName', $data) ? $data['fathersName'] : $student->STU_ATESI,
            'STU_GJINI' => $data['gender'] ?? $student->STU_GJINI,
            'STU_DTL' => $data['birthDate'] ?? $student->STU_DTL,
            'STU_TEL' => array_key_exists('phone', $data) ? $data['phone'] : $student->STU_TEL,
            'STU_STATUS' => $data['status'] ?? $student->STU_STATUS,
            'DHOM_ID' => array_key_exists('dormRoomId', $data) ? $data['dormRoomId'] : $student->DHOM_ID,
        ]);

        return $this->success(new StudentResource($student->fresh()), 'Studenti u përditësua me sukses.');
    }

    /**
     * Deactivate a student
     *
     * Sets STU_STATUS to "Ç'regjistruar". Does not hard-delete because the
     * student may have linked grades, invoices, or registrations (FK constraints).
     *
     * @group Students
     *
     * @response 200 {"data": null, "message": "Studenti u çregjistrua me sukses.", "status": 200}
     * @response 404 {"data": null, "message": "Not Found", "status": 404}
     */
    public function destroy(int $id): JsonResponse
    {
        $student = Student::findOrFail($id);

        $student->update(['STU_STATUS' => "Ç'regjistruar"]);

        return $this->success(null, 'Studenti u çregjistrua me sukses.');
    }
}
