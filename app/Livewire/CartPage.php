<?php

namespace App\Livewire;

use App\Support\Cart;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::site')]
#[Title('Korpa')]
class CartPage extends Component
{
    public function increment(string $key, Cart $cart): void
    {
        $line = $cart->paidLines()->firstWhere('key', $key);
        if ($line && $line['qty'] < $line['max_qty']) {
            $cart->setQty($key, $line['qty'] + 1);
            $this->dispatch('cart-updated');
        }
    }

    public function decrement(string $key, Cart $cart): void
    {
        $line = $cart->paidLines()->firstWhere('key', $key);
        if ($line && $line['qty'] > 1) {
            $cart->setQty($key, $line['qty'] - 1);
            $this->dispatch('cart-updated');
        }
    }

    public function remove(string $key, Cart $cart): void
    {
        if (str_starts_with($key, 'gift-')) {
            $cart->chooseGift((int) substr($key, 5), null);
        } else {
            $cart->remove($key);
        }
        $this->dispatch('cart-updated');
    }

    public function chooseGift(int $promotionId, int $sizeId, Cart $cart): void
    {
        $promo = $cart->promotions()->first(fn ($p) => $p['promotion']->id === $promotionId && $p['eligible']);
        if ($promo && $promo['gifts']->contains('id', $sizeId)) {
            $cart->chooseGift($promotionId, $sizeId);
            $this->dispatch('cart-updated');
        }
    }

    public function render(Cart $cart)
    {
        return view('livewire.cart-page', [
            'lines' => $cart->lines(),
            'summary' => $cart->summary(),
            'promotions' => $cart->promotions(),
        ]);
    }
}
