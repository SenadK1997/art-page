<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Product extends Model
{
    use HasFactory;

    public function tags ():BelongsToMany
    {
        return $this->belongsToMany(Tags::class)
        ->withPivot('tags_id')
        ->withTimestamps();
    }
    public function images():BelongsToMany
    {
        return $this->belongsToMany(Images::class, 'product_images')
        ->withPivot('images_id')
        ->withTimestamps();
    }
}
