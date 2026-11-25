<?php

namespace Tests\Feature;

use App\Filament\Pages\SalesReport;
use App\Filament\Pages\ShopSettings;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Livewire\CartPage;
use App\Livewire\CommissionCalculator;
use App\Models\Order;
use App\Models\PaintingStyle;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use App\Support\Cart;
use App\Support\PlaceOrder;
use App\Support\Settings;
use App\Support\Telegram;
use Database\Seeders\PaintingStyleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class NewFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PaintingStyleSeeder::class);
        Http::preventStrayRequests();
    }

    private function style(string $slug): PaintingStyle
    {
        return PaintingStyle::where('slug', $slug)->firstOrFail();
    }

    private function painting(string $title, int $price, int $stock = 0): Product
    {
        $p = Product::create(['title' => $title, 'image' => 'paintings/x.jpg', 'painting_style_id' => $this->style('realizam')->id]);
        $p->sizes()->create(['width' => 50, 'height' => 70, 'price' => $price, 'stock' => $stock]);

        return $p->fresh('sizes');
    }

    private function customer(): array
    {
        return ['full_name' => 'Test Kupac', 'phone' => '061 000 111', 'address' => 'Testna 1', 'city' => 'Sarajevo', 'country' => 'BiH'];
    }

    public function test_customer_photo_is_kept_privately_with_the_order(): void
    {
        Storage::fake('local');

        Livewire::test(CommissionCalculator::class)
            ->set('photo', UploadedFile::fake()->image('baka.jpg', 1200, 900))
            ->call('add', $this->style('realizam')->id, 60, 80, 'gold')
            ->assertHasNoErrors();

        $line = app(Cart::class)->lines()->sole();
        $this->assertSame('Slika po vašoj fotografiji', $line['title']);
        $this->assertSame(480, $line['unit_price']);

        // Visible in this visitor's own cart only.
        $this->get(route('cart.photo', $line['key']))->assertOk();

        $order = app(PlaceOrder::class)->handle($this->customer());
        $item = $order->items->sole();
        $this->assertStringStartsWith('order-photos/'.$order->number.'/', $item->customer_photo);
        Storage::disk('local')->assertExists($item->customer_photo);

        // Guests can't open it, the admin can.
        $this->get(route('admin.photo', $item))->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create())->get(route('admin.photo', $item))->assertOk();
    }

    public function test_each_style_has_its_own_size_limits(): void
    {
        // Panorama starts at 60 × 30 cm.
        Livewire::test(CommissionCalculator::class)
            ->call('add', $this->style('panorama')->id, 40, 40, 'none')
            ->assertHasErrors('width');

        $this->style('realizam')->update(['max_width' => 100]);
        Livewire::test(CommissionCalculator::class)
            ->call('add', $this->style('realizam')->id, 120, 80, 'none')
            ->assertHasErrors('width');

        Livewire::test(CommissionCalculator::class)
            ->call('add', $this->style('panorama')->id, 120, 60, 'none')
            ->assertHasNoErrors();
        $this->assertSame(1080, app(Cart::class)->lines()->sole()['unit_price']);
    }

    public function test_gift_promotion_adds_a_free_painting(): void
    {
        $a = $this->painting('Sebilj', 350);
        $b = $this->painting('Miljacka', 300);
        $gift = $this->painting('Mala slika', 120, stock: 1);

        $promo = Promotion::create(['title' => 'Dvije + poklon', 'min_paintings' => 2]);
        $promo->requiredProducts()->attach($a);
        $promo->gifts()->attach($gift->sizes[0]);

        $cart = app(Cart::class);
        $cart->addPainting($a->sizes[0]->id, 'none');

        // One painting: not yet.
        $this->assertFalse($cart->promotions()->sole()['eligible']);

        $cart->addPainting($b->sizes[0]->id, 'none');
        $this->assertTrue($cart->promotions()->sole()['eligible']);

        Livewire::test(CartPage::class)->call('chooseGift', $promo->id, $gift->sizes[0]->id);

        $gifts = $cart->lines()->where('type', 'gift');
        $this->assertCount(1, $gifts);
        $this->assertSame(0, $gifts->first()['unit_price']);
        $this->assertSame(650, $cart->summary()['total']);

        $order = app(PlaceOrder::class)->handle($this->customer());
        $item = $order->items->firstWhere('kind', 'gift');
        $this->assertSame('Dvije + poklon', $item->promotion_title);
        $this->assertSame(0, $gift->sizes[0]->fresh()->stock);
        $this->assertSame(650, $order->total);
    }

    public function test_gift_disappears_when_the_cart_no_longer_qualifies(): void
    {
        $a = $this->painting('Sebilj', 350);
        $gift = $this->painting('Mala', 100, stock: 2);
        $promo = Promotion::create(['title' => 'Iznad 300', 'min_total' => 300]);
        $promo->gifts()->attach($gift->sizes[0]);

        $cart = app(Cart::class);
        $cart->addPainting($a->sizes[0]->id, 'none');
        $cart->chooseGift($promo->id, $gift->sizes[0]->id);
        $this->assertCount(2, $cart->lines());

        $a->sizes[0]->update(['price' => 200]);
        $this->assertCount(1, $cart->lines());
    }

    public function test_delivery_cost_and_free_delivery_threshold(): void
    {
        $settings = app(Settings::class);
        $this->assertNull($settings->shippingFor(100)); // not set: "by courier price list"

        $settings->set(['shipping_fee' => 10, 'free_shipping_over' => 500]);
        $this->assertSame(10, $settings->shippingFor(499));
        $this->assertSame(0, $settings->shippingFor(500));

        $p = $this->painting('Sebilj', 350);
        app(Cart::class)->addPainting($p->sizes[0]->id, 'none');
        $order = app(PlaceOrder::class)->handle($this->customer());

        $this->assertSame(10, $order->shipping);
        $this->assertSame(360, $order->total);
        $this->assertSame(360, $order->original_total);
    }

    public function test_telegram_gets_new_orders(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
        app(Settings::class)->set(['telegram_enabled' => true, 'telegram_bot_token' => '123456:ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'telegram_chat_id' => '42']);

        // The token is stored encrypted.
        $raw = DB::table('settings')->where('key', 'telegram_bot_token')->value('value');
        $this->assertNotSame('123456:ABCDEFGHIJKLMNOPQRSTUVWXYZ', $raw);

        $p = $this->painting('Sebilj', 350);
        app(Cart::class)->addPainting($p->sizes[0]->id, 'none');
        $order = app(PlaceOrder::class)->handle($this->customer());
        app(Telegram::class)->notifyNewOrder($order);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'bot123456:ABCDEFGHIJKLMNOPQRSTUVWXYZ/sendMessage')
            && $request['chat_id'] === '42'
            && str_contains($request['text'], $order->number)
            && str_contains($request['text'], 'Sebilj'));
    }

    public function test_telegram_is_silent_when_not_configured(): void
    {
        Http::fake();
        $order = Order::create(['full_name' => 'A', 'phone' => '1', 'address' => 'x', 'city' => 'y', 'total' => 1]);
        app(Telegram::class)->notifyNewOrder($order);
        Http::assertNothingSent();
    }

    public function test_admin_can_change_prices_and_give_a_discount(): void
    {
        $this->actingAs(User::factory()->create());
        $order = Order::create(['full_name' => 'A B', 'phone' => '061', 'address' => 'X', 'city' => 'Y', 'subtotal' => 700, 'total' => 700, 'original_total' => 700]);
        $item = $order->items()->create(['kind' => 'painting', 'title' => 'Sebilj', 'width' => 50, 'height' => 70, 'unit_price' => 350, 'qty' => 2]);

        $page = Livewire::test(EditOrder::class, ['record' => $order->getRouteKey()]);
        $key = array_key_first($page->get('data.items'));
        $page->set("data.items.{$key}.unit_price", 300)
            ->set('data.discount', 50)
            ->set('data.shipping', 10)
            ->call('save')
            ->assertHasNoFormErrors();

        $order->refresh();
        $this->assertSame(300, $item->fresh()->unit_price);
        $this->assertSame(600, $order->subtotal);
        $this->assertSame(560, $order->total);
        $this->assertSame(700, $order->original_total);
        $this->assertTrue($order->wasAdjusted());
    }

    public function test_settings_page_saves_and_report_page_loads(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ShopSettings::class)
            ->fillForm(['shipping_fee' => 12, 'phone' => '061 111 222', 'made_to_order_time' => 'izrada 10 dana'])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame(12, app(Settings::class)->int('shipping_fee'));

        $this->get('/admin/postavke')->assertOk()->assertSee('Telegram');
        $this->get('/admin/izvjestaj')->assertOk()->assertSee('Izvještaj prodaje');
        Livewire::test(SalesReport::class)->assertOk();
    }

    public function test_new_storefront_pages_render(): void
    {
        $this->painting('Sebilj', 350)->tags()->create(['name' => 'Priroda', 'slug' => 'priroda']);

        $this->get('/slika-po-mjeri')->assertOk()->assertSee('Vaša fotografija')->assertSee('Često pitate');
        $this->get('/')->assertOk()->assertSee('Pronađite svoj motiv')->assertSee('Priroda');
        $this->get('/galerija?tema=priroda')->assertOk()->assertSee('Sebilj');
    }
    public function test_commission_without_photo_or_motif_is_agreed_by_phone(): void
    {
        Livewire::test(CommissionCalculator::class)
            ->call('add', $this->style('realizam')->id, 50, 70, 'black');

        $line = app(Cart::class)->lines()->sole();
        $this->assertSame('Slika po mjeri — motiv po dogovoru', $line['title']);
        $this->assertNull($line['note']);
        $this->get('/korpa')->assertSee('Umjetnik vas kontaktira da dogovorite motiv.');
    }

    public function test_commission_motif_reaches_the_order_and_telegram(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
        app(Settings::class)->set(['telegram_enabled' => true, 'telegram_bot_token' => '123456:ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'telegram_chat_id' => '42']);

        Livewire::test(CommissionCalculator::class)
            ->set('motif', 'Zalazak sunca nad morem')
            ->call('add', $this->style('realizam')->id, 50, 70, 'black')
            ->assertHasNoErrors();

        $this->assertSame('Slika po mjeri', app(Cart::class)->lines()->sole()['title']);
        $order = app(PlaceOrder::class)->handle($this->customer());
        $this->assertSame('Zalazak sunca nad morem', $order->items->sole()->customer_note);

        app(Telegram::class)->notifyNewOrder($order);
        Http::assertSent(fn ($r) => str_contains($r['text'], 'Zalazak sunca nad morem'));
    }
}
