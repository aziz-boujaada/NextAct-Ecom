<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRefundRequest;
use App\Models\Refund;
use App\Services\RefundService;

class RefundController extends Controller
{
    public function __construct(private readonly RefundService $refundService) {}

    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => Refund::with(['sale.client', 'items.product'])->latest()->get(),
        ]);
    }

    public function store(StoreRefundRequest $request)
    {
        $refund = $this->refundService->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Refund created successfully',
            'data' => $refund,
        ], 201);
    }

    public function show(Refund $refund)
    {
        return response()->json([
            'status' => 'success',
            'data' => $refund->load(['sale.client', 'items.product']),
        ]);
    }

    public function destroy(Refund $refund)
    {
        $this->refundService->delete($refund);

        return response()->json([
            'status' => 'success',
            'message' => 'Refund deleted successfully',
        ]);
    }
}
