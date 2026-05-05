<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductImageService;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct(private readonly ProductImageService $productImageService) {}

    public function index()
    {
        return response()->json([
            'status' => 'success',
            'products' => Product::with(['category', 'supplier'])->latest()->get(),
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $data['reference'] = 'Ref-' . Str::uuid(); 
        if ($request->hasFile('image')) {
            $data['image_path'] = $this->productImageService->store($request->file('image'));
        }

        unset($data['image']);

        
        if($data['stock'] < $data['min_stock']){
            return response()->json([
                'status' => 'failed',
                'message' => 'stock must be greater than min stock '
            ],422);
        }
        $product = Product::create($data )->load(['category', 'supplier']);

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
