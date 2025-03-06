<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Images extends Model
{
    use HasFactory;

    public function product():BelongsToMany
    {
        return $this->belongsToMany(Product::class)
        ->withPivot('product_id', 'images_id')
        ->withTimestamps();
    }
    protected $fillable = ['filename', 'product_id', 'width', 'height', 'price'];

}
