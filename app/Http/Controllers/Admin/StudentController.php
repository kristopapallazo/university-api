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

    public function show(int $id): JsonResponse
    {
        $student = Student::findOrFail($id);

        return $this->success(new StudentResource($student), 'Studenti u mor me sukses.');
    }

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

    public function destroy(int $id): JsonResponse
    {
        $student = Student::findOrFail($id);

        $student->update(['STU_STATUS' => "Ç'regjistruar"]);

        return $this->success(null, 'Studenti u çregjistrua me sukses.');
    }
}
