<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Support\Cart;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PhotoController extends Controller
{
    /** The customer's own uploaded photo, shown only to that same visitor's cart. */
    public function cart(string $key, Cart $cart): StreamedResponse
    {
        $path = $cart->photoFor($key);
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, ['Cache-Control' => 'private, max-age=3600']);
    }

    public function admin(OrderItem $item): StreamedResponse
    {
        abort_unless($item->customer_photo && Storage::disk('local')->exists($item->customer_photo), 404);

        return Storage::disk('local')->response(
            $item->customer_photo,
            $item->order->number.'-'.basename($item->customer_photo),
        );
    }
}
