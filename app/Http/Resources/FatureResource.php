<?php

namespace App\Http\Resources;

use App\Models\Fature;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Fature */
class FatureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'invoiceId' => $this->FAT_ID,
            'amount' => (float) $this->FAT_SHUMA,
            'status' => $this->FAT_STATUSI,
            'issuedDate' => $this->FAT_DAT_LESHIM,
            'description' => $this->FAT_PERSHKRIM,
        ];
    }
}
