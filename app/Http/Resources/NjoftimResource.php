<?php

namespace App\Http\Resources;

use App\Models\Njoftim;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Njoftim */
class NjoftimResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->NJOF_ID,
            'title' => $this->NJOF_TITULL,
            'body' => $this->NJOF_TEKST,
            'type' => $this->NJOF_TIPI,
            'isRead' => (bool) $this->NJOF_IS_READ,
            'readAt' => $this->NJOF_READ_AT,
            'createdAt' => $this->CREATED_AT,
        ];
    }
}
