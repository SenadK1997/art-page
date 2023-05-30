<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    use HasFactory;

    public function products():BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_product')
        ->withPivot('order_id', 'product_id', 'itemId', 'itemName', 'width', 'height', 'price', 'qty')
        ->withTimestamps();
    }
}
