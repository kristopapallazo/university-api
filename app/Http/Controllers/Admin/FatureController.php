<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Fature;
use App\Services\ValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FatureController extends Controller
{
    use ApiResponse;

    // Valid invoice statuses
    private const VALID_STATUSES = ['E papaguar', 'E paguar', 'E vonuar'];

    public function __construct(private readonly ValidationService $validation) {}

    /**
     * List all invoices (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('perPage', 20), 100);

        $invoices = Fature::paginate($perPage);

        return $this->success($invoices, 'OK');
    }

    /**
     * Get a single invoice by ID.
     */
    public function show(int $id): JsonResponse
    {
        $invoice = Fature::findOrFail($id);

        return $this->success($invoice, 'OK');
    }

    /**
     * Create a new invoice.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'studentId' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'status' => 'required|string|in:' . implode(',', self::VALID_STATUSES),
            'description' => 'nullable|string|max:200',
            'issueDate' => 'required|date',
        ]);

        // Validate that the student exists
        $this->validation->validateStudentExists($data['studentId']);

        // Map request field names to model field names
        $invoice = Fature::create([
            'STU_ID' => $data['studentId'],
            'FAT_SHUMA' => $data['amount'],
            'FAT_STATUSI' => $data['status'],
            'FAT_PERSHKRIM' => $data['description'] ?? null,
            'FAT_DAT_LESHIM' => $data['issueDate'],
        ]);

        return $this->success($invoice, 'Fatura u krijua me sukses.', 201);
    }

    /**
     * Update an existing invoice.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $invoice = Fature::findOrFail($id);

        $data = $request->validate([
            'studentId' => 'sometimes|integer',
            'amount' => 'sometimes|numeric|min:0.01',
            'status' => 'sometimes|string|in:' . implode(',', self::VALID_STATUSES),
            'description' => 'nullable|string|max:200',
            'issueDate' => 'sometimes|date',
        ]);

        // Validate that the student exists (only if studentId is being updated)
        if (isset($data['studentId'])) {
            $this->validation->validateStudentExists($data['studentId']);
        }

        // Map and update only the provided fields
        $updateData = [];
        if (isset($data['studentId'])) {
            $updateData['STU_ID'] = $data['studentId'];
        }
        if (isset($data['amount'])) {
            $updateData['FAT_SHUMA'] = $data['amount'];
        }
        if (isset($data['status'])) {
            $updateData['FAT_STATUSI'] = $data['status'];
        }
        if (isset($data['description'])) {
            $updateData['FAT_PERSHKRIM'] = $data['description'];
        }
        if (isset($data['issueDate'])) {
            $updateData['FAT_DAT_LESHIM'] = $data['issueDate'];
        }

        $invoice->update($updateData);

        return $this->success($invoice, 'Fatura u përditësua me sukses.');
    }

    /**
     * Delete an invoice.
     */
    public function destroy(int $id): JsonResponse
    {
        $invoice = Fature::findOrFail($id);
        $invoice->delete();

        return $this->success(null, 'Fatura u fshi me sukses.');
    }
}
