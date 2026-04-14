<?php

namespace App\Http\Resources;

use App\Models\Lenda;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Lenda */
class LendaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->LEND_ID,
            'name' => $this->LEND_EMER,
            'code' => $this->LEND_KOD,
            'departmentId' => $this->DEP_ID,
        ];
    }
}
