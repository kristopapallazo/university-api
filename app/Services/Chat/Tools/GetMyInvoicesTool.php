<?php

namespace App\Services\Chat\Tools;

use App\Models\Fature;
use App\Models\Student;
use App\Models\User;
use RuntimeException;

class GetMyInvoicesTool implements ChatTool
{
    public function name(): string
    {
        return 'get_my_invoices';
    }

    public function description(): string
    {
        return "Returns the authenticated student's invoices/fees, with amount, status (paid/unpaid), description, and issue date. Only works for students.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'status' => [
                    'type' => 'string',
                    'enum' => ['paguar', 'papaguar'],
                    'description' => "Filter by status: 'paguar' (paid) or 'papaguar' (unpaid). Omit to return all.",
                ],
            ],
            'required' => [],
        ];
    }

    public function execute(array $input, User $user): array
    {
        if ($user->role !== 'student') {
            throw new RuntimeException('get_my_invoices is only available to students.');
        }

        $student = Student::where('STU_EMAIL', $user->email)->first();
        if (! $student) {
            return ['error' => 'Student record not found for this account.'];
        }

        $query = Fature::where('STU_ID', $student->STU_ID);

        if (! empty($input['status'])) {
            $query->where('FAT_STATUSI', $input['status']);
        }

        $invoices = $query->orderByDesc('FAT_DAT_LESHIM')->get();

        if ($invoices->isEmpty()) {
            return ['invoices' => [], 'total_unpaid' => 0, 'message' => 'No invoices found.'];
        }

        $result = $invoices->map(fn ($f) => [
            'id' => $f->FAT_ID,
            'amount' => (float) $f->FAT_SHUMA,
            'status' => $f->FAT_STATUSI,
            'description' => $f->FAT_PERSHKRIM,
            'issued_at' => $f->FAT_DAT_LESHIM,
        ])->toArray();

        $totalUnpaid = $invoices
            ->where('FAT_STATUSI', 'papaguar')
            ->sum('FAT_SHUMA');

        return [
            'invoices' => $result,
            'total_unpaid' => (float) $totalUnpaid,
            'count' => count($result),
        ];
    }
}
