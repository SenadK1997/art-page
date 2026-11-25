<?php

namespace App\Support;

use App\Mail\NewOrderMail;
use App\Models\Order;
use App\Models\ProductSize;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PlaceOrder
{
    public function __construct(private Cart $cart, private Settings $settings, private Telegram $telegram) {}

    /**
     * @param  array{full_name: string, phone: string, email?: ?string, address: string, city: string, zip?: ?string, country: string, note?: ?string}  $customer
     */
    public function handle(array $customer): Order
    {
        $lines = $this->cart->lines();
        if ($this->cart->isEmpty()) {
            throw new RuntimeException('Cart is empty.');
        }
        $summary = $this->cart->summary();

        $order = DB::transaction(function () use ($customer, $lines, $summary) {
            $order = Order::create($customer + [
                'status' => 'new',
                'payment_method' => 'cod',
                'subtotal' => $summary['subtotal'],
                'shipping' => $summary['shipping'] ?? 0,
                'total' => $summary['total'],
                'original_total' => $summary['total'],
            ]);

            foreach ($lines as $line) {
                // Take finished pieces from stock first; the rest is painted to order.
                $fromStock = 0;
                if ($line['size_id']) {
                    $size = ProductSize::whereKey($line['size_id'])->lockForUpdate()->first();
                    $fromStock = min($line['qty'], $size?->stock ?? 0);
                    if ($fromStock > 0) {
                        $size->decrement('stock', $fromStock);
                    }
                }

                $order->items()->create([
                    'kind' => $line['type'],
                    'customer_photo' => $this->keepPhoto($line['photo'], $order),
                    'customer_note' => $line['note'] ?? null,
                    'promotion_title' => $line['promotion_title'],
                    'product_id' => $line['product_id'],
                    'title' => $line['title'],
                    'style_name' => $line['style_name'],
                    'width' => $line['width'],
                    'height' => $line['height'],
                    'frame' => $line['frame'] === 'none' ? null : $line['frame'],
                    'unit_price' => $line['unit_price'],
                    'original_unit_price' => $line['original_unit_price'],
                    'qty' => $line['qty'],
                    'from_stock' => $fromStock,
                ]);
            }

            return $order;
        });

        $this->cart->clear();
        $order->load('items');

        if ($to = $this->settings->get('owner_email') ?? config('shop.owner_email')) {
            try {
                Mail::to($to)->send(new NewOrderMail($order));
            } catch (\Throwable $e) {
                Log::warning('New order email failed', ['order' => $order->number, 'error' => $e->getMessage()]);
            }
        }

        // After the response, so a slow Telegram never delays the customer.
        dispatch(fn () => app(Telegram::class)->notifyNewOrder($order))->afterResponse();

        return $order;
    }

    /** Move an uploaded photo from the cart area into the order's own folder. */
    private function keepPhoto(?string $path, Order $order): ?string
    {
        if (! $path || ! Storage::disk('local')->exists($path)) {
            return null;
        }
        $target = 'order-photos/'.$order->number.'/'.basename($path);
        Storage::disk('local')->move($path, $target);

        return $target;
    }
}
