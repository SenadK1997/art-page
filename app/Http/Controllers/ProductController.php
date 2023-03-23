<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Tags;
use Illuminate\View\View;

class ProductController extends Controller
{
    //
    public function show(string $id): View
    {
        return view('product', [
            'product' => Product::findOrFail($id)
        ]);
    }

    public function shop(): View
    {
        $items = Product::all();
        // $all_tags = Tags::all();
        foreach ($items as $item) {
            $tags = [];
            $tags_ids = json_decode($item->tags_ids);

            foreach ($tags_ids as $id) { // 1
                $tag = Tags::find($id); // 
                    array_push($tags, $tag);
            }

            $item->tags = $tags;
        }
        // $items->put('all_tags', $all_tags)
        return view('shop', [
            "items" => $items
        ]);
    }
}
