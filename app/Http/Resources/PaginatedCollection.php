<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PaginatedCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        $paginator = $this->resource;

        return [
            'data' => $this->collection,
            'pagination' => [
                'current' => $paginator->currentPage(),
                'pageSize' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'message' => 'OK',
            'status' => 200,
        ];
    }
}
