<?php

namespace App\Filters\V1;

use Illuminate\Http\Request;

class ProductFilter
{
    public static function apply(Request $request)
    {
        $filters = [];

        // Add filtering parameters
        if ($request->has('name')) {
            $filters['name'] = $request->query('name');
        }

        if ($request->has('min_price')) {
            $filters['min_price'] = $request->query('min_price');
        }

        if ($request->has('max_price')) {
            $filters['max_price'] = $request->query('max_price');
        }

        // Add sorting parameters
        if ($request->has('sort_by')) {
            $filters['sort_by'] = $request->query('sort_by');
            $filters['sort_direction'] = $request->query('sort_direction', 'asc');
        }

        return $filters;
    }
}
