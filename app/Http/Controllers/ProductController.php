<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Tags;
use Illuminate\View\View;
// use Symfony\Component\HttpFoundation\Request;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Gloudemans\Shoppingcart\Facades\Cart;


class ProductController extends Controller
{
    //
    public function show(string $id): View
    {
        return view('product', [
            'product' => Product::findOrFail($id),
        ]);
    }

    public function shop(Request $request): View
    {   
        $query = $request->get('query');
        $items = Product::paginate(12);
        
        $qtags = $request->get('qtags');

        $cleared = $request->get('clear_tags');
        $tags = Tags::all();
        
       if (isset($qtags)) {
            $tags_id =[];
            foreach ($qtags as $q) {
                $tagValue = $q;
                $tags_id[] = $tagValue;
            }
            $tags_id = is_array($tags_id) ? $tags_id : Arr::wrap($tags_id);
            $items = Product::whereHas('tags', function ($query) use ($tags_id) {
                $query->whereIn('tags_id', $tags_id);
            })->paginate(12);    
        }
        else if ($request->has($cleared)) {
            dd('Request has cleared');
        }
            
        $tags = Tags::all();
        if (strlen($query) > 0) {
            $items = Product::where('title', 'LIKE', '%' .$query. '%')->paginate(12);

            return view('/shop', 
            [
                'items' => $items,
                'tags' => $tags,
                'qtags' => $qtags
            ], 
            compact('items'));
        }

        $tags = Tags::all();
        
        
        return view('shop', [
           "items" => $items,
           "tags" => $tags,
           "qtags" => $qtags
        ]);
    }
    public function cart()
    {
        // foreach (Cart::content() as $item) {
        //     dd($item);
        // }
        return view('shop.cart', [
            'cartItems' => Cart::content(),
        ]);
    }
    public function addToCart(Request $request)
    {
        $product_id = $request->input('product_id');
        
        $image = $request->input('product_url');
        $product_url = $image;
        
        $product_title = $request->input('product_title');
        $product_description = $request->input('product_description');
        $product_price = $request->input('product_price');
        $product_quantity = $request->input('quantity');

    Cart::add([
        'id' => $product_id,
        'name' => $product_title,
        'qty' => $product_quantity,
        'price' => $product_price,
        'options' => [
            'url' => $product_url,
            'description' => $product_description
        ]
    ]);

    return redirect()->route('shop.cart');
    }


    public function updateCart(Request $request, $rowId)
    {
        $quantity = $request->input('product_qty');

        Cart::update($rowId, $quantity);

        return redirect()->route('shop.cart');
    }

    public function removeFromCart(Request $request, $rowId)
    {
        // $rowId = $request->input('product_id');

        Cart::remove($rowId);

        return redirect()->route('shop.cart');
    }

    // public function checkout()
    // {

    // }
}
