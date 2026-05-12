<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;

class SupplierController extends Controller
{

public function __construct()
{
    $this->middleware('permissions:view_suppliers')->only(['index', 'show']);

    $this->middleware('permissions:create_suppliers')->only(['store']);

    $this->middleware('permissions:edit_suppliers')->only(['update']);

    $this->middleware('permissions:delete_suppliers')->only(['destroy']);

}
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'suppliers' => Supplier::latest()->get(),
        ]);
    }

    public function store(StoreSupplierRequest $request)
    {
        $supplier = Supplier::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Supplier created successfully',
            'supplier' => $supplier,
        ], 201);
    }

    public function show(Supplier $supplier)
    {
        return response()->json([
            'status' => 'success',
            'supplier' => $supplier,
        ]);
    }

    public function update(UpdateSupplierRequest $request, string $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Supplier updated successfully',
            'supplier' => $supplier->fresh(),
        ]);
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Supplier deleted successfully',
        ]);
    }
}
