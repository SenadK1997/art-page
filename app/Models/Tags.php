<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tags extends Model
{
    use HasFactory;

    public function products():BelongsToMany
    {
        return $this->belongsToMany(Product::class)
        ->withPivot('product_id', 'tags_id')
        ->withTimestamps();
    }
}
