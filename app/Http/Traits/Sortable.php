<?php

namespace App\Http\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait Sortable
{
    protected function applySorting(Builder $query, Request $request, array $allowedFields): Builder
    {
        $sortBy = $request->query('sortBy');
        $sortOrder = in_array(strtolower($request->query('sortOrder', 'asc')), ['asc', 'desc'])
            ? strtolower($request->query('sortOrder', 'asc'))
            : 'asc';

        if ($sortBy && in_array($sortBy, $allowedFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        return $query;
    }
}
