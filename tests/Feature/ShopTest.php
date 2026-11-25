<?php

namespace Tests\Feature;

use App\Livewire\Checkout;
use App\Livewire\CommissionCalculator;
use App\Livewire\ProductPurchase;
use App\Models\Order;
use App\Models\PaintingStyle;
use App\Models\Product;
use App\Support\Cart;
use Database\Seeders\PaintingStyleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PaintingStyleSeeder::class);
    }

    private function style(string $slug): PaintingStyle
    {
        return PaintingStyle::where('slug', $slug)->firstOrFail();
    }

    private function painting(array $attrs = [], array $sizes = [[50, 70, 350, 0]]): Product
    {
        $product = Product::create($attrs + [
            'title' => 'Sebilj',
            'image' => 'paintings/sebilj.jpg',
            'painting_style_id' => $this->style('realizam')->id,
        ]);
        foreach ($sizes as $i => [$w, $h, $price, $stock]) {
            $product->sizes()->create(['width' => $w, 'height' => $h, 'price' => $price, 'stock' => $stock, 'sort' => $i]);
        }

        return $product->fresh('sizes');
    }

    private function customer(): array
    {
        return [
            'full_name' => 'Test Kupac', 'phone' => '061 000 111', 'address' => 'Testna 1',
            'city' => 'Sarajevo', 'zip' => '71000',
        ];
    }

    private function checkout(): \Livewire\Features\SupportTesting\Testable
    {
        $test = Livewire::test(Checkout::class);
        foreach ($this->customer() as $field => $value) {
            $test->set($field, $value);
        }

        return $test->call('submit');
    }

    public function test_public_pages_render(): void
    {
        $product = $this->painting();

        $this->get('/')->assertOk()->assertSee('Naruči sliku po mjeri');
        $this->get('/galerija')->assertOk()->assertSee('Sebilj');
        $this->get(route('gallery.show', $product))->assertOk()->assertSee('350')->assertSee('Na zidu');
        $this->get('/o-umjetniku')->assertOk()->assertSee('Amer Fočo');
        $this->get('/korpa')->assertOk();
        $this->get('/shop')->assertRedirect('/galerija');
    }

    public function test_commission_is_priced_on_the_server_with_the_formula(): void
    {
        Livewire::test(CommissionCalculator::class)
            ->call('add', $this->style('panorama')->id, 100, 150, 'gold')
            ->assertRedirect(route('cart'));

        $line = app(Cart::class)->lines()->sole();
        $this->assertSame(2250, $line['unit_price']);
        $this->assertSame('Panorama', $line['style_name']);
    }

    public function test_commission_rejects_sizes_outside_the_limits(): void
    {
        Livewire::test(CommissionCalculator::class)
            ->call('add', $this->style('realizam')->id, 5, 70, 'gold')
            ->assertHasErrors('width');

        $this->assertTrue(app(Cart::class)->isEmpty());
    }

    public function test_checkout_creates_a_cash_on_delivery_order(): void
    {
        $product = $this->painting();
        app(Cart::class)->addPainting($product->sizes[0]->id, 'black', 2);
        app(Cart::class)->addCommission($this->style('realizam')->id, 70, 100, 'gold');

        $this->checkout()->assertHasNoErrors()->assertRedirect();

        $order = Order::with('items')->sole();
        $this->assertSame('cod', $order->payment_method);
        $this->assertSame('new', $order->status);
        $this->assertSame(350 * 2 + 700, $order->total);
        $this->assertCount(2, $order->items);
        $this->assertTrue(app(Cart::class)->isEmpty());
    }

    public function test_checkout_requires_contact_details(): void
    {
        app(Cart::class)->addCommission($this->style('realizam')->id, 50, 70, 'none');

        Livewire::test(Checkout::class)
            ->call('submit')
            ->assertHasErrors(['full_name', 'phone', 'address', 'city']);

        $this->assertSame(0, Order::count());
    }

    public function test_discount_lowers_the_price_and_shows_on_the_homepage(): void
    {
        $product = $this->painting(['discount_percent' => 20]);
        app(Cart::class)->addPainting($product->sizes[0]->id, 'none');

        $line = app(Cart::class)->lines()->sole();
        $this->assertSame(280, $line['unit_price']);
        $this->assertSame(350, $line['original_unit_price']);

        $this->get('/')->assertSee('Na akciji')->assertSee('−20%', false);

        $this->checkout();
        $item = Order::sole()->items->sole();
        $this->assertSame(280, $item->unit_price);
        $this->assertSame(350, $item->original_unit_price);
    }

    public function test_expired_discount_is_ignored(): void
    {
        $product = $this->painting(['discount_percent' => 20, 'discount_ends_at' => now()->subDay()]);

        $this->assertSame(0, $product->activeDiscount());
        $this->assertSame(350, $product->sizes[0]->finalPrice());
        $this->assertSame(0, Product::onSale()->count());
    }

    public function test_ordering_takes_pieces_from_stock(): void
    {
        $product = $this->painting([], [[50, 70, 350, 1]]);
        app(Cart::class)->addPainting($product->sizes[0]->id, 'none', 3);

        $this->get('/')->assertSee('Spremno za slanje');
        $this->checkout();

        $this->assertSame(0, $product->sizes[0]->fresh()->stock);
        $item = Order::sole()->items->sole();
        $this->assertSame(3, $item->qty);
        $this->assertSame(1, $item->from_stock);
    }

    public function test_one_off_original_sells_out(): void
    {
        $product = $this->painting(['made_to_order' => false], [[50, 70, 350, 1]]);
        app(Cart::class)->addPainting($product->sizes[0]->id, 'none', 5);

        // Only the one piece in stock can be bought.
        $this->assertSame(1, app(Cart::class)->lines()->sole()['qty']);
        $this->checkout();

        $product->refresh()->load('sizes');
        $this->assertTrue($product->isSoldOut());

        Livewire::test(ProductPurchase::class, ['product' => $product])
            ->assertSee('Prodano')
            ->set('sizeId', $product->sizes[0]->id)
            ->call('add')
            ->assertHasErrors('sizeId');
    }

    public function test_cart_ignores_tampered_prices_and_hidden_paintings(): void
    {
        $product = $this->painting();
        app(Cart::class)->addPainting($product->sizes[0]->id, 'not-a-frame');
        $product->sizes[0]->update(['price' => 400]);

        $line = app(Cart::class)->lines()->sole();
        $this->assertSame(400, $line['unit_price']);
        $this->assertSame('none', $line['frame']);

        $product->update(['is_available' => false]);
        $this->assertTrue(app(Cart::class)->isEmpty());
    }
}
