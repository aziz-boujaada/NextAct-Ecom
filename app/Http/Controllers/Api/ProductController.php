<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct(private readonly ProductImageService $productImageService)
    {
        $this->middleware('permissions:view_products')->only(['index', 'show']);

        $this->middleware('permissions:create_products')->only(['store']);

        $this->middleware('permissions:edit_products')->only(['update']);

        $this->middleware('permissions:delete_products')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'supplier_id' => ['sometimes', 'integer', 'exists:suppliers,id'],
        ]);

        return response()->json([
            'status' => 'success',
            'products' => Product::with(['category', 'supplier'])
                ->when(isset($filters['supplier_id']), fn($query) => $query->where('supplier_id', $filters['supplier_id']))
                ->latest()
                ->get(),
        ]);
    }

   public function store(StoreProductRequest $request)
{
    $data = $request->validated();

    $data['reference'] = 'Ref-' . Str::uuid();

    if ($request->hasFile('image')) {
        $data['image_path'] = $this->productImageService
            ->store($request->file('image'));
    }

    unset($data['image']);

    $stock = $data['stock'] ?? 0;
    $min_stock = $data['min_stock'] ?? 0;
    $security_stock = $data['security_stock'] ?? 0;

    if ($stock < $min_stock) {
        return response()->json([
            'status' => 'failed',
            'message' => 'Stock must be greater than min stock'
        ], 422);
    }
 
    $data['alert_stock'] = $min_stock + $security_stock;
    $product = Product::create($data)
    ->load(['category', 'supplier']);
    
    return response()->json([
        'status' => 'success',
        'message' => 'Product created successfully',
        'product' => $product,
        ], 201);
      
}

    public function show(Product $product)
    {
        return response()->json([
            'status' => 'success',
            'product' => $product->load(['category', 'supplier']),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->productImageService->replace(
                $request->file('image'),
                $product->image_path,
            );
        }

        unset($data['image']);

        $product->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Product updated successfully',
            'product' => $product->fresh()->load(['category', 'supplier']),
        ]);
    }

    public function destroy(Product $product)
    {
        $this->productImageService->delete($product->image_path);

        $product->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully',
        ]);
    }
}
