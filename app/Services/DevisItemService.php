<?php

namespace App\Services;

use App\Models\Devis;
use App\Models\DevisItem;
use App\Models\Product;

class DevisItemService
{
    public function create(Devis $devis, array $data): DevisItem
    {
        $product = Product::findOrFail($data['product_id']);
        $price = (float) ($data['price'] ?? $product->price);
        $quantity = (int) $data['quantity'];

        return DevisItem::create([
            'devis_id' => $devis->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $price,
            'total' => $price * $quantity,
        ]);
    }

    public function delete(DevisItem $devisItem): void
    {
        $devisItem->delete();
    }
}
