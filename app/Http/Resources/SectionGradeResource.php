<?php

namespace App\Http\Resources;

use App\Models\Nota;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Nota */
class SectionGradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'gradeId' => $this->NOTA_ID,
            'value' => (float) $this->NOTA_VLERA,
            'date' => $this->NOTA_DAT,
            'examType' => $this->provim->TIP_EMER,
            'student' => [
                'id' => $this->student->STU_ID,
                'firstName' => $this->student->STU_EM,
                'lastName' => $this->student->STU_MB,
                'matriculationNumber' => $this->student->STU_NR_MATRIKULL,
            ],
        ];
    }
}
