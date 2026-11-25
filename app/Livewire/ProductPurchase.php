<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductSize;
use App\Support\Cart;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ProductPurchase extends Component
{
    #[Locked]
    public int $productId;

    public ?int $sizeId = null;

    public string $frame = 'black';

    public function mount(Product $product): void
    {
        $this->productId = $product->id;
        $sizes = $product->sizes;
        // Prefer a size that is ready to ship.
        $this->sizeId = ($sizes->firstWhere(fn ($s) => $s->stock > 0) ?? $sizes->first(fn ($s) => $s->isPurchasable()))?->id;
    }

    public function add(Cart $cart)
    {
        $this->validate([
            'sizeId' => ['required', 'integer'],
            'frame' => ['required', 'in:'.implode(',', array_keys(config('shop.frames')))],
        ], ['sizeId.required' => 'Izaberite veličinu.']);

        $size = ProductSize::with('product')->where('product_id', $this->productId)->find($this->sizeId);
        if (! $size || ! $size->isPurchasable()) {
            $this->addError('sizeId', 'Ova veličina je rasprodana.');

            return;
        }

        $cart->addPainting($size->id, $this->frame);
        $this->dispatch('cart-updated');

        return $this->redirectRoute('cart', navigate: true);
    }

    public function render()
    {
        $product = Product::with('sizes.product')->findOrFail($this->productId);

        return view('livewire.product-purchase', [
            'product' => $product,
            'sizes' => $product->sizes,
            'selected' => $product->sizes->firstWhere('id', $this->sizeId),
            'frames' => config('shop.frames'),
        ]);
    }
}
