<?php

namespace Tests\Feature;

use App\Models\PaintingStyle;
use App\Models\Product;
use Database\Seeders\PaintingStyleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PaintingStyleSeeder::class);
        $this->product = Product::create([
            'title' => 'Sebilj u sumrak', 'image' => 'paintings/sebilj.jpg',
            'painting_style_id' => PaintingStyle::where('slug', 'panorama')->value('id'),
        ]);
        $this->product->sizes()->createMany([
            ['width' => 50, 'height' => 70, 'price' => 525, 'stock' => 1],
            ['width' => 70, 'height' => 100, 'price' => 1050],
        ]);
        $this->product->tags()->create(['name' => 'Sarajevo', 'slug' => 'sarajevo']);
    }

    public function test_sitemap_lists_pages_paintings_images_and_categories(): void
    {
        Product::create(['title' => 'Skrivena', 'is_available' => false])->sizes()->create(['width' => 1, 'height' => 1, 'price' => 1]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('gallery.show', $this->product), false)
            ->assertSee('/storage/paintings/sebilj.jpg', false)
            ->assertSee(route('commission'), false)
            ->assertSee('tema=sarajevo', false)
            ->assertDontSee('skrivena');
    }

    public function test_robots_only_opens_up_in_production(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /');

        $this->app['env'] = 'production';
        $this->get('/robots.txt')
            ->assertSee('Disallow: /admin')
            ->assertSee('Sitemap: '.route('sitemap'));
    }

    public function test_product_page_has_share_image_canonical_and_price_data(): void
    {
        $this->get(route('gallery.show', $this->product))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('gallery.show', $this->product).'">', false)
            ->assertSee('<meta property="og:type" content="product">', false)
            ->assertSee('<meta property="og:image" content="'.url('/storage/paintings/sebilj.jpg').'">', false)
            ->assertSee('"@type":"AggregateOffer"', false)
            ->assertSee('"lowPrice":525', false)
            ->assertSee('"priceCurrency":"BAM"', false)
            ->assertSee('https://schema.org/InStock', false)
            ->assertSee('https://www.instagram.com/foco.art/', false);
    }

    public function test_cart_and_filtered_pages_are_kept_out_of_search(): void
    {
        $this->get('/korpa')->assertSee('<meta name="robots" content="noindex, follow">', false);
        $this->get('/galerija?poredaj=cijena-gore')->assertSee('noindex', false);
        $this->get('/galerija?tema=sarajevo')
            ->assertDontSee('noindex', false)
            ->assertSee('<link rel="canonical" href="'.route('gallery').'?tema=sarajevo">', false)
            ->assertSee('<title>Sarajevo — slike', false);
    }

    public function test_social_links_are_on_every_page(): void
    {
        foreach (['/', '/galerija', '/o-umjetniku', '/slika-po-mjeri'] as $url) {
            $this->get($url)
                ->assertSee('https://www.instagram.com/foco.art/', false)
                ->assertSee('https://www.facebook.com/focooART/', false)
                ->assertSee('https://www.tiktok.com/@foco_art', false);
        }
    }
}
