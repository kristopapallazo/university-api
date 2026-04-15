<?php

namespace App\Http\Resources;

use App\Models\ProgramStudim;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProgramStudim */
class ProgramStudimResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->PROG_ID,
            'name' => $this->PROG_EM,
            'level' => $this->PROG_NIV,
            'credits' => $this->PROG_KRD,
            'departmentId' => $this->DEP_ID,
        ];
    }
}
