<?php

namespace Tests\Feature\Admin;

use App\Models\Fature;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FatureControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsRole(string $role, ?User $user = null): static
    {
        $user ??= User::factory()->create(['role' => $role]);

        return $this->withToken($user->createToken('test')->plainTextToken);
    }

    private function validPayload(int $studentId): array
    {
        return [
            'studentId' => $studentId,
            'amount' => 150.50,
            'status' => 'E papaguar',
            'description' => 'Tarifë semestri 1',
            'issueDate' => '2026-05-14',
        ];
    }

    public function test_admin_creates_invoice_with_valid_data(): void
    {
        $student = Student::factory()->create();

        $response = $this->actingAsRole('admin')
            ->postJson('/api/v1/admin/invoices', $this->validPayload($student->STU_ID));

        $response->assertStatus(201);
        $this->assertDatabaseHas('FATURE', [
            'STU_ID' => $student->STU_ID,
            'FAT_STATUSI' => 'E papaguar',
            'FAT_PERSHKRIM' => 'Tarifë semestri 1',
        ]);
    }

    public function test_invalid_student_returns_404(): void
    {
        $this->actingAsRole('admin')
            ->postJson('/api/v1/admin/invoices', $this->validPayload(99999))
            ->assertStatus(404)
            ->assertJson(['entity' => 'Student', 'status' => 404]);

        $this->assertDatabaseCount('FATURE', 0);
    }

    public function test_invalid_amount_returns_422(): void
    {
        $student = Student::factory()->create();

        $payload = $this->validPayload($student->STU_ID);
        $payload['amount'] = -50.00;

        $this->actingAsRole('admin')
            ->postJson('/api/v1/admin/invoices', $payload)
            ->assertStatus(422)
            ->assertJsonStructure(['data' => ['amount']]);
    }

    public function test_invalid_status_returns_422(): void
    {
        $student = Student::factory()->create();

        $payload = $this->validPayload($student->STU_ID);
        $payload['status'] = 'Invalid Status';

        $this->actingAsRole('admin')
            ->postJson('/api/v1/admin/invoices', $payload)
            ->assertStatus(422)
            ->assertJsonStructure(['data' => ['status']]);
    }

    public function test_invalid_date_returns_422(): void
    {
        $student = Student::factory()->create();

        $payload = $this->validPayload($student->STU_ID);
        $payload['issueDate'] = 'not-a-date';

        $this->actingAsRole('admin')
            ->postJson('/api/v1/admin/invoices', $payload)
            ->assertStatus(422)
            ->assertJsonStructure(['data' => ['issueDate']]);
    }

    public function test_index_lists_invoices(): void
    {
        $student = Student::factory()->create();
        Fature::factory()->count(3)->create(['STU_ID' => $student->STU_ID]);

        $response = $this->actingAsRole('admin')
            ->getJson('/api/v1/admin/invoices');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_show_returns_single_invoice(): void
    {
        $student = Student::factory()->create();
        $invoice = Fature::factory()->create(['STU_ID' => $student->STU_ID]);

        $this->actingAsRole('admin')
            ->getJson("/api/v1/admin/invoices/{$invoice->FAT_ID}")
            ->assertStatus(200)
            ->assertJsonPath('data.FAT_ID', $invoice->FAT_ID);
    }

    public function test_update_only_changes_provided_fields(): void
    {
        $student = Student::factory()->create();
        $invoice = Fature::factory()->create([
            'STU_ID' => $student->STU_ID,
            'FAT_STATUSI' => 'E papaguar',
            'FAT_SHUMA' => 100.00,
        ]);

        $this->actingAsRole('admin')
            ->putJson("/api/v1/admin/invoices/{$invoice->FAT_ID}", ['status' => 'E paguar'])
            ->assertStatus(200);

        $this->assertDatabaseHas('FATURE', [
            'FAT_ID' => $invoice->FAT_ID,
            'FAT_STATUSI' => 'E paguar',
            'FAT_SHUMA' => 100.00,
        ]);
    }

    public function test_update_with_invalid_student_returns_404(): void
    {
        $student = Student::factory()->create();
        $invoice = Fature::factory()->create(['STU_ID' => $student->STU_ID]);

        $this->actingAsRole('admin')
            ->putJson("/api/v1/admin/invoices/{$invoice->FAT_ID}", ['studentId' => 99999])
            ->assertStatus(404)
            ->assertJson(['entity' => 'Student']);
    }

    public function test_destroy_deletes_invoice(): void
    {
        $student = Student::factory()->create();
        $invoice = Fature::factory()->create(['STU_ID' => $student->STU_ID]);

        $this->actingAsRole('admin')
            ->deleteJson("/api/v1/admin/invoices/{$invoice->FAT_ID}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('FATURE', ['FAT_ID' => $invoice->FAT_ID]);
    }

    public function test_non_admin_gets_403(): void
    {
        $student = Student::factory()->create();

        $this->actingAsRole('student')
            ->getJson('/api/v1/admin/invoices')
            ->assertStatus(403);

        $this->actingAsRole('pedagog')
            ->postJson('/api/v1/admin/invoices', $this->validPayload($student->STU_ID))
            ->assertStatus(403);
    }

    public function test_unauthenticated_gets_401(): void
    {
        $this->getJson('/api/v1/admin/invoices')
            ->assertStatus(401);
    }
}
