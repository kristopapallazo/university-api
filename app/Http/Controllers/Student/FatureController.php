<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\FatureResource;
use App\Http\Traits\ApiResponse;
use App\Models\Fature;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class FatureController extends Controller
{
    use ApiResponse;

    /**
     * List my invoices
     *
     * Returns all invoices for the authenticated student, sorted by issue date desc.
     *
     * @group Student Reports
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": [{"invoiceId": 3, "amount": 25000.0, "status": "E papaguar", "issuedDate": "2026-09-01", "description": "Tarif\u00eb vjetore 2025-2026"}],
     *   "message": "Faturat u mor\u00ebn me sukses.",
     *   "status": 200
     * }
     */
    public function index(): JsonResponse
    {
        $studentId = Auth::user()->student->STU_ID;

        $invoices = Fature::where('STU_ID', $studentId)
            ->orderByDesc('FAT_DAT_LESHIM')
            ->get();

        return $this->success(
            FatureResource::collection($invoices),
            'Faturat u morën me sukses.'
        );
    }
}
