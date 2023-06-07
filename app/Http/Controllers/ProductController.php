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
    public function requestPayment(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();

        $amount = $request->price;
        $amount = str_replace(',', '', $amount);
        $amount = round($amount / 1.95583, 2);
        $amount = str_replace(',', '', $amount);
        // $amount = str_replace(',', '', round($amount / 1.95, 2));
        // dd($amount);
        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('payment.success'),
                "cancel_url" => route('payment.cancel'),
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => "EUR",
                        "value" => $amount
                    ]
                ]
            ]
        ]);
        if (isset($response['id'])) {
            foreach ($response['links'] as $links) {
                if ($links['rel'] == 'approve') {
                    return redirect()->away($links['href']);
                }
            }
            return redirect()
            ->route('shop.cart')
            ->with('error', 'Something went wrong');
        } else {
            return redirect()
            ->route('shop.cart')
            ->with('error', $response['message'] ?? 'Something went wrong');
        }
    }
    public function paymentSuccess(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        
        $response = $provider->capturePaymentOrder($request['token']);
        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            // Definisanje informacija o produktu
            $allCartItems = Cart::content();
            $productIds = collect($allCartItems)->pluck('id')->toArray();
            // Definisanje informacija o klijentu
            $payerFullName = $response['payer']['name']['given_name'] . ' ' . $response['payer']['name']['surname'];
            $payerAdress = $response['purchase_units'][0]['shipping']['address']['address_line_1'];
            $payerCountry = $response['purchase_units'][0]['shipping']['address']['country_code'];
            $payerEmail = $response['payer']['email_address'];
            $payerZipcode = $response['purchase_units'][0]['shipping']['address']['postal_code'];
            $totalPrice = $response['purchase_units'][0]['payments']['captures'][0]['amount']['value'];
            
            $newOrder = new Order();
            $newOrder->fullname = $payerFullName;
            $newOrder->address = $payerAdress;
            $newOrder->country = $payerCountry;
            $newOrder->email = $payerEmail;
            $newOrder->zipcode = $payerZipcode;
            $newOrder->totalPrice = $totalPrice;
            $newOrder->save();
            $cartProducts = Cart::content();
            // Attach the products to the order and specify the pivot data
            $orders = [];

            foreach ($cartProducts as $product) {
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
            $orderId = $newOrder->id;
            
            $newOrder->products()->attach($orders);
            // return redirect()
            // ->route('shop.cart')
            // ->with('success', 'Transaction completed');
            Cart::destroy();
            return redirect()->route('completed.order', ['id' => $orderId])->with('success', 'Hvala na ukazanom povjerenju');
        } else {
            return redirect()
            ->route('shop.cart')
            ->with('error', 'Something went wrongs');
        }
    }
    public function completedOrder($id)
    {

        $orders = Order::findOrFail($id);
        return view('order', compact('orders'));
    }

    public function paymentCancel()
    {
        return redirect()
        ->route('shop.cart')
        ->with('error', $response['message'] ?? 'You have canceled the transaction');
    }
    // public function checkout()
    // {

    // }
}
