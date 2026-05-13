<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'reference',
        'name',
        'description',
        'image_path',
        'price',
        'stock',
        'min_stock',
        'security_stock',
        'alert_stock',
        'category_id',
        'supplier_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function refundItems()
    {
        return $this->hasMany(RefundItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }



    protected static function booted()
    {
        static::saving(function($product) {
            $product->alert_stock = ($product->min_stock ?? 0) + ($product->security_stock ?? 0);
        });
    }
}
