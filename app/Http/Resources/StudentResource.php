<?php

namespace App\Http\Resources;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Student */
class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->STU_ID,
            'firstName' => $this->STU_EM,
            'lastName' => $this->STU_MB,
            'fathersName' => $this->STU_ATESI,
            'gender' => $this->STU_GJINI,
            'birthDate' => $this->STU_DTL?->toDateString(),
            'matriculation' => $this->STU_NR_MATRIKULL,
            'email' => $this->STU_EMAIL,
            'phone' => $this->STU_TEL,
            'enrolledAt' => $this->STU_DAT_REGJISTRIM?->toDateString(),
            'status' => $this->STU_STATUS,
            'dormRoomId' => $this->DHOM_ID,
        ];
    }
}
