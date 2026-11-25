<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    /** Every public page, painting (with its images) and category, for search engines. */
    public function sitemap(): Response
    {
        $xml = (function () {
            $latest = Product::visible()->max('updated_at');
            $pages = [
                ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'weekly', 'lastmod' => $latest],
                ['loc' => route('gallery'), 'priority' => '0.9', 'changefreq' => 'daily', 'lastmod' => $latest],
                ['loc' => route('commission'), 'priority' => '0.9', 'changefreq' => 'monthly'],
                ['loc' => route('about'), 'priority' => '0.6', 'changefreq' => 'yearly'],
            ];

            foreach (Tag::whereHas('products', fn ($q) => $q->visible())->get() as $tag) {
                $pages[] = ['loc' => route('gallery', ['tema' => $tag->slug]), 'priority' => '0.6', 'changefreq' => 'weekly'];
            }

            foreach (Product::visible()->orderBy('sort')->get() as $product) {
                $pages[] = [
                    'loc' => route('gallery.show', $product),
                    'priority' => '0.8',
                    'changefreq' => 'weekly',
                    'lastmod' => $product->updated_at,
                    'images' => collect($product->galleryUrls())->map(fn ($url) => ['loc' => $url, 'title' => $product->title])->all(),
                ];
            }

            return view('seo.sitemap', ['pages' => $pages])->render();
        })();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = app()->isProduction()
            ? [
                'User-agent: *',
                'Disallow: /admin',
                'Disallow: /korpa',
                'Disallow: /narudzba',
                'Disallow: /livewire',
                'Allow: /',
                '',
                'Sitemap: '.route('sitemap'),
            ]
            // Staging and local copies must never end up in Google.
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
