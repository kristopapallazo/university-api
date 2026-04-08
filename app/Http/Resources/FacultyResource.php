<?php

namespace App\Http\Resources;

use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Faculty */
class FacultyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->FAK_ID,
            'name' => $this->FAK_EM,
        ];
    }
}
