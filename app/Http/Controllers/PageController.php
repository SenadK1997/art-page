<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaintingStyle;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Tag;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        $base = fn () => Product::visible()->with('sizes.product', 'style');

        $featured = $base()
            ->orderByDesc('is_featured')->orderBy('sort')->latest()
            ->take(7)->get();

        $hero = $featured->first();

        // The artist's own homepage image (Admin → Postavke), else the first featured painting.
        $settings = app(\App\Support\Settings::class);
        $heroImage = $settings->get('hero_image');
        $heroLinkProduct = $settings->int('hero_product_id') ? Product::visible()->find($settings->int('hero_product_id')) : null;
        $heroView = $heroImage
            ? [
                'image' => \Illuminate\Support\Facades\Storage::disk('public')->url($heroImage),
                'alt' => $settings->get('hero_caption') ?: ($heroLinkProduct?->title ?? config('shop.name')),
                'caption' => $settings->get('hero_caption'),
                'url' => $heroLinkProduct ? route('gallery.show', $heroLinkProduct) : route('gallery'),
                'natural' => true,
            ]
            : ($hero && $hero->imageUrl() ? [
                'image' => $hero->imageUrl(),
                'alt' => $hero->title,
                'caption' => null, // only the caption typed in Admin → Postavke is shown
                'url' => route('gallery.show', $hero),
                'natural' => false,
            ] : null);

        return view('pages.home', [
            'hero' => $hero,
            'heroView' => $heroView,
            // One painting shown large; when the hero already uses the first featured painting, take the next.
            'spotlight' => $spotlight = ($heroImage ? $featured->first() : $featured->skip(1)->first()),
            'featured' => $featured->reject(fn ($p) => $p->is($spotlight) || (! $heroImage && $p->is($hero)))->take(6)->values(),
            'onSale' => $base()->onSale()->orderBy('sort')->take(6)->get(),
            'inStock' => $base()->inStock()->orderBy('sort')->take(6)->get(),
            'styles' => PaintingStyle::active()->get(),
            'promotions' => Promotion::running()->with('requiredProducts', 'gifts.product')->get()
                ->filter(fn (Promotion $p) => $p->availableGifts()->isNotEmpty()),
            'categories' => Tag::whereHas('products', fn ($q) => $q->visible())
                ->withCount(['products' => fn ($q) => $q->visible()])
                ->orderByDesc('products_count')->take(8)->get()
                ->each(function (Tag $t) use (&$usedCovers) {
                    // A different painting on each category tile where possible.
                    $usedCovers ??= [];
                    $cover = $t->products()->visible()->whereNotNull('image')->whereNotIn('products.id', $usedCovers)->orderBy('sort')->first()
                        ?? $t->products()->visible()->whereNotNull('image')->orderBy('sort')->first();
                    $usedCovers[] = $cover?->id;
                    $t->setRelation('cover', $cover);
                }),
        ]);
    }

    /** Owner's quick price calculator, installable as a phone app (PWA). */
    public function calculator(): View
    {
        return view('pages.calculator', [
            'styles' => PaintingStyle::active()->get()->map(fn (PaintingStyle $s) => [
                'id' => $s->id,
                'slug' => $s->slug,
                'image' => $s->imageUrl(),
                'name' => $s->name,
                'multiplier' => $s->multiplier,
                'minW' => $s->min_width,
                'maxW' => $s->max_width,
                'minH' => $s->min_height,
                'maxH' => $s->max_height,
            ])->values(),
        ]);
    }

    public function commission(): View
    {
        return view('pages.commission', ['styles' => PaintingStyle::active()->get()]);
    }

    public function about(): View
    {
        return view('pages.about', [
            'works' => Product::visible()->whereNotNull('image')->inRandomOrder()->take(3)->get(),
        ]);
    }

    public function product(Product $product): View
    {
        abort_unless($product->is_available, 404);

        $product->load('sizes.product', 'style', 'tags');

        $related = Product::visible()->with('sizes.product', 'style')
            ->whereKeyNot($product->id)
            ->when($product->painting_style_id, fn ($q) => $q->orderByRaw('painting_style_id = ? desc', [$product->painting_style_id]))
            ->inRandomOrder()->take(3)->get();

        return view('pages.product', [
            'product' => $product,
            'related' => $related,
            'schema' => $this->productSchema($product),
        ]);
    }

    /** schema.org data so search results can show price and availability. */
    private function productSchema(Product $product): array
    {
        $availability = fn ($size) => match (true) {
            $size->stock > 0 => 'https://schema.org/InStock',
            $product->made_to_order => 'https://schema.org/MadeToOrder',
            default => 'https://schema.org/SoldOut',
        };

        $offers = $product->sizes->map(fn ($size) => [
            '@type' => 'Offer',
            'name' => $size->label(),
            'price' => $size->finalPrice(),
            'priceCurrency' => 'BAM',
            'availability' => $availability($size),
            'url' => route('gallery.show', $product),
            'itemCondition' => 'https://schema.org/NewCondition',
        ])->values()->all();

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                array_filter([
                    '@type' => 'Product',
                    'name' => $product->title,
                    'description' => \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 300) ?: null,
                    'image' => $product->galleryUrls(),
                    'brand' => ['@type' => 'Brand', 'name' => config('shop.name')],
                    'category' => $product->style?->name,
                    'material' => $product->technique,
                    'offers' => count($offers) === 1 ? $offers[0] : [
                        '@type' => 'AggregateOffer',
                        'lowPrice' => $product->sizes->map->finalPrice()->min(),
                        'highPrice' => $product->sizes->map->finalPrice()->max(),
                        'priceCurrency' => 'BAM',
                        'offerCount' => count($offers),
                        'offers' => $offers,
                    ],
                ]),
                [
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Galerija', 'item' => route('gallery')],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => $product->title, 'item' => route('gallery.show', $product)],
                    ],
                ],
            ],
        ];
    }

    public function thanks(Order $order): View
    {
        return view('pages.thanks', ['order' => $order->load('items')]);
    }
}
