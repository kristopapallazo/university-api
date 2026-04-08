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

    public function index(Request $request): JsonResponse
    {
        $query = Department::query()->orderBy('DEP_ID');

        if ($request->filled('faculty_id')) {
            $query->where('FAK_ID', $request->integer('faculty_id'));
        }

        return $this->success(
            DepartmentResource::collection($query->get()),
            'Departamentet u morën me sukses.'
        );
    }

    public function show(int $id): JsonResponse
    {
        $department = Department::findOrFail($id);

        return $this->success(
            new DepartmentResource($department),
            'Departamenti u mor me sukses.'
        );
    }
}
