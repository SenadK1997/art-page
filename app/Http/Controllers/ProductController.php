<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Tags;
use App\Models\Order;
use Illuminate\View\View;
// use Symfony\Component\HttpFoundation\Request;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Gloudemans\Shoppingcart\Facades\Cart;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\IpUtils;
use Illuminate\Support\Facades\Http;

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
        // dd(Cart::content());
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
        $product_width = $request->input('product_width');
        $product_height = $request->input('product_height');
        $product_quantity = $request->input('quantity');
        $product_frame = $request->input('selected_option');
        Cart::add([
            'id' => $product_id,
            'name' => $product_title,
            'qty' => $product_quantity,
            'price' => $product_price,
            'options' => [
                'price' => $product_price,
                'url' => $product_url,
                'description' => $product_description,
                'width' => $product_width,
                'height' => $product_height,
                'frame' => $product_frame,
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
    public function createOrder(Request $request)
    {
        $allCartItems = Cart::content();

        if ($allCartItems->isEmpty()) {
            return redirect()->route('shop.cart')->with('error', 'Your cart is empty.');
        }

        $recaptcha_response = $request->input('g-recaptcha-response');
        if (is_null($recaptcha_response)) {
            return redirect()->back()->with('error', 'Please Complete the Recaptcha to proceed');
        }
        $url = "https://www.google.com/recaptcha/api/siteverify";
        $body = [
            'secret' => env('RECAPTCHA_SITE_SECRET'),
            'response' => $recaptcha_response,
            'remoteip' => IpUtils::anonymize($request->ip())
        ];
        $response = Http::asForm()->post($url, $body);

        $result = json_decode($response);

        if (!$response->successful() || $result->success != true) {
            return redirect()->back()->with('error', 'Please complete the reCAPTCHA again to proceed');
        }

        $fullName = $request->input('full_name');
        $address = $request->input('address');
        $country = $request->input('country');
        $email = $request->input('email');
        $phone = $request->input('phone');
        $req = $request->input('request') ;
        $zipcode = $request->input('zipcode');
        $totalPrice = str_replace(',', '', Cart::subTotal());

        $newOrder = new Order();
        $newOrder->fullname = $fullName;
        $newOrder->address = $address;
        $newOrder->country = $country;
        $newOrder->email = $email;
        $newOrder->phone = $phone;
        $newOrder->request = $req;
        $newOrder->zipcode = $zipcode;
        $newOrder->totalPrice = $totalPrice;
        $newOrder->save();

        $orders = [];

        foreach ($allCartItems as $product) {
            $orders[] = [
                'product_id' => $product->id,
                'itemId' => $product->id,
                'itemName' => $product->name,
                'width' => $product->options->width,
                'height' => $product->options->height,
                'price' => $product->price,
                'qty' => $product->qty,
                'frame' => $product->options->frame,
            ];
        }

        $newOrder->products()->attach($orders);
        Cart::destroy();
        $orderId = $newOrder->id;

        return redirect()->route('completed.order', ['id' => $orderId])->with('success', 'Hvala na ukazanom povjerenju');
    }

    public function completedOrder($id)
    {

        $orders = Order::findOrFail($id);
        // dd($orders->totalPrice);
        // $order->totalPrice = $order->totalPrice;
        return view('order', compact('orders'));
    }
}
