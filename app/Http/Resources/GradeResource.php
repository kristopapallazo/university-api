<?php

namespace App\Http\Resources;

use App\Models\Nota;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Nota */
class GradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'gradeId' => $this->NOTA_ID,
            'value' => (float) $this->NOTA_VLERA,
            'date' => $this->NOTA_DAT,
            'examType' => $this->provim->TIP_EMER,
            'examDate' => $this->provim->DAT_PROVIM,
            'course' => $this->provim->seksion->lenda->LEND_EMER,
        ];
    }
}
