<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Tags;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends Controller
{
    //
    public function show(string $id): View
    {
        return view('product', [
            'product' => Product::findOrFail($id)
        ]);
    }

    public function shop(Request $request): View
    {   
        $query = $request->get('query');

        if (strlen($query) > 0) {
            $products = Product::where('title', 'LIKE', '%' .$query. '%')->get();

            return view('/shop', 
            ['items' => $products], compact('products'));
        }
        $items = Product::all();
        
        // foreach ($items as $item) {
        //     dd($item->tags->toArray());
        // }
        return view('shop', [
           "items" => $items,
        ]);
        /* foreach ($items as $item) {
            $tags = [];
            $tags_ids = json_decode($item->tags_ids);

            foreach ($tags_ids as $id) { // 1
                $tag = Tags::find($id); // 
                    array_push($tags, $tag);
            }
            $tags = $item->tags;
        } */

         //return view('shop', [
           // "items" => $items,
            /* "tags" => $tags */
       // ]);
    }
}
