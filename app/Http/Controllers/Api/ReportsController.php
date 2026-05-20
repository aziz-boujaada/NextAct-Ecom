<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    private readonly \App\Services\ReportsService $reportsService;

    public function __construct(\App\Services\ReportsService $reportsService)
    {
        $this->reportsService = $reportsService;

        $this->middleware('permissions:view_financial_reports')->only(['financial']);
        $this->middleware('permissions:view_inventory_reports')->only(['inventory']);
        $this->middleware('permissions:view_sales_reports')->only(['sales']);
        $this->middleware('permissions:view_sales_reports')->only(['devis']);
        $this->middleware('permissions:view_purchasing_reports')->only(['purchasing']);
    }

    public function financial(Request $request)
    {
        $filters = $request->validate([
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date', 'after_or_equal:from'],
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $this->reportsService->financial($filters),
        ]);
    }

    public function inventory(Request $request)
    {
        $filters = $request->validate([
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date', 'after_or_equal:from'],
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $this->reportsService->inventory($filters),
        ]);
    }

    public function sales(Request $request)
    {
        $filters = $request->validate([
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date', 'after_or_equal:from'],
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $this->reportsService->sales($filters),
        ]);
    }

    public function devis(Request $request)
    {
        $filters = $request->validate([
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date', 'after_or_equal:from'],
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $this->reportsService->devis($filters),
        ]);
    }

    public function purchasing(Request $request)
    {
        $filters = $request->validate([
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date', 'after_or_equal:from'],
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $this->reportsService->purchasing($filters),
        ]);
    }
}