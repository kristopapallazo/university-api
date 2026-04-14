<?php

namespace App\Http\Resources;

use App\Models\Pedagog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Pedagog */
class PedagogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->PED_ID,
            'firstName' => $this->PED_EM,
            'lastName' => $this->PED_MB,
            'title' => $this->PED_TITULLI,
            'email' => $this->PED_EMAIL,
            'gender' => $this->PED_GJINI,
            'departmentId' => $this->DEP_ID,
        ];
    }
}
