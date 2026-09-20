<?php

namespace App\Http\Controllers;

use App\Models\HsCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HsCodeController extends Controller
{
    /**
     * Search HS codes by keyword, commodity name, code or category.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q');
        $category = $request->input('category');

        $builder = HsCode::query();

        if (!empty($category) && $category !== 'all') {
            $builder->byCategory($category);
        }

        if (!empty($query)) {
            $builder->search($query);
        } else {
            // Default to popular export items
            $builder->popular();
        }

        $results = $builder->orderBy('is_popular', 'desc')
            ->orderBy('commodity_name', 'asc')
            ->limit(30)
            ->get([
                'id',
                'code',
                'wco_code',
                'nepal_tariff_code',
                'commodity_name',
                'category',
                'standard_uom',
                'export_duty_rate',
                'customs_notes',
                'is_popular',
            ]);

        return response()->json([
            'status' => 'success',
            'count' => $results->count(),
            'query' => $query,
            'category' => $category,
            'results' => $results,
            'data' => $results,
        ]);
    }

    /**
     * Get distinct commodity categories.
     */
    public function categories(): JsonResponse
    {
        $categories = HsCode::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return response()->json([
            'status' => 'success',
            'categories' => $categories,
        ]);
    }
}
