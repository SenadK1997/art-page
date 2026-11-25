@props(['title' => null, 'description' => null, 'image' => null, 'type' => 'website', 'schema' => null])
@php
    $pageTitle = ($title ? $title.' — ' : '').config('shop.name').' · '.config('shop.artist');
    $pageDescription = \Illuminate\Support\Str::limit($description ?? 'Originalne slike i slike po narudžbi — '.config('shop.artist').', Sarajevo. Ulje i akril na platnu, cijena odmah, plaćanje pouzećem.', 160);
    // One URL per page for search engines: no filter query strings, except the category.
    $canonical = url()->current().(request()->routeIs('gallery') && request('tema') ? '?tema='.urlencode(request('tema')) : '');
    $shareImage = $image ?? asset('images/icon-512.png');
    $private = request()->routeIs('cart', 'checkout', 'order.thanks') || request()->hasAny(['q', 'poredaj', 'page', 'ponuda']);
    $organization = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => config('shop.name'),
        'url' => url('/'),
        'logo' => asset('images/icon-512.png'),
        'founder' => ['@type' => 'Person', 'name' => config('shop.artist'), 'jobTitle' => 'Slikar'],
        'sameAs' => array_values(config('shop.socials')),
    ] + ($settings->get('phone') ? ['telephone' => $settings->get('phone')] : []);
@endphp
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    @if ($private)
        <meta name="robots" content="noindex, follow">
    @else
        <link rel="canonical" href="{{ $canonical }}">
    @endif
    <meta name="theme-color" content="#0e0d0c">
    <link rel="icon" href="/favicon.ico" sizes="32x32">
    <link rel="icon" href="/images/icon-192.png" type="image/png" sizes="192x192">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">

    {{-- Link previews (WhatsApp, Viber, Facebook, Instagram DMs) --}}
    <meta property="og:site_name" content="{{ config('shop.name') }}">
    <meta property="og:locale" content="bs_BA">
    <meta property="og:type" content="{{ $type }}">
    <meta property="og:title" content="{{ $title ?? config('shop.name').' · '.config('shop.artist') }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $shareImage }}">
    <meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}">

    {{-- Who is behind the site, linked to the social profiles --}}
    <script type="application/ld+json">{!! json_encode($organization, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @if ($schema)
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="theme-light min-h-screen bg-night">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:bg-gold focus:px-4 focus:py-2 focus:text-ink">Preskoči na sadržaj</a>

    <header x-data="{ open: false, scrolled: false }"
            x-init="scrolled = window.scrollY > 20"
            @scroll.window="scrolled = window.scrollY > 20"
            :class="scrolled ? 'border-line' : 'border-transparent'"
            class="theme-dark fixed inset-x-0 top-0 z-40 border-b bg-[#0e0d0c] transition-colors duration-300">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-6 lg:px-10">
            <a href="{{ route('home') }}" class="group flex items-center gap-3.5 font-display text-[24px] tracking-[0.2em] text-bone" wire:navigate aria-label="{{ config('shop.name') }} — naslovna">
                <img src="/images/logo-160.png" alt="" width="48" height="48" class="h-12 w-12 transition-transform duration-700 group-hover:rotate-[20deg]">
                <span class="hidden sm:inline">FOCO <span class="text-gold">ART</span></span>
            </a>

            <nav class="hidden items-center gap-10 text-[12px] uppercase tracking-[0.2em] text-stone md:flex" aria-label="Glavni meni">
                <a href="{{ route('gallery') }}" wire:navigate class="transition hover:text-bone {{ request()->routeIs('gallery*') ? 'text-bone' : '' }}">Galerija</a>
                <a href="{{ route('commission') }}" class="transition hover:text-bone">Slika po mjeri</a>
                <a href="{{ route('about') }}" wire:navigate class="transition hover:text-bone {{ request()->routeIs('about') ? 'text-bone' : '' }}">O umjetniku</a>
                <livewire:cart-counter />
            </nav>

            <div class="-mr-2 flex items-center gap-1 md:hidden">
                <livewire:cart-counter />
                <button type="button" @click="open = !open" class="flex h-11 w-11 items-center justify-center text-bone" :aria-expanded="open" aria-label="Meni">
                    <svg x-show="!open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><path d="M3 7h18M3 12h18M3 17h18"/></svg>
                    <svg x-show="open" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>
        </div>

        {{-- Phone menu: full height, big targets, socials at the bottom --}}
        <div x-show="open" x-cloak x-transition.opacity @keydown.escape.window="open = false"
             x-effect="document.documentElement.style.overflow = open ? 'hidden' : ''"
             class="fixed inset-x-0 bottom-0 top-20 flex flex-col overflow-y-auto border-t border-line bg-[#0e0d0c] md:hidden">
            <nav class="flex flex-col px-6 pt-4 font-display text-[34px] leading-tight" aria-label="Mobilni meni">
                @foreach ([['home', 'Naslovna'], ['gallery', 'Galerija'], ['commission', 'Slika po mjeri'], ['about', 'O umjetniku'], ['cart', 'Korpa']] as [$route, $label])
                    <a href="{{ route($route) }}" @click="open = false" class="flex min-h-14 items-center justify-between border-b border-line py-3 {{ request()->routeIs($route) ? 'text-gold-light' : 'text-bone' }}">
                        {{ $label }} <span class="text-[18px] text-ash" aria-hidden="true">→</span>
                    </a>
                @endforeach
            </nav>
            <div class="mt-auto px-6 pb-10 pt-8">
                <a href="{{ route('commission') }}" @click="open = false" class="btn-gold w-full">Naruči sliku po mjeri</a>
                <x-social-links class="mt-8 justify-center" />
            </div>
        </div>
    </header>

    <main id="main">
        {{ $slot }}
    </main>

    <footer class="theme-dark mt-32 border-t border-line">
        <div class="mx-auto grid max-w-7xl gap-10 px-6 py-14 md:grid-cols-[1.4fr_1fr_1fr] md:gap-12 md:py-16 lg:px-10">
            <div>
                <div class="flex items-center gap-4">
                    <img src="/images/logo-160.png" alt="" width="64" height="64" loading="lazy" class="h-16 w-16">
                    <p class="font-display text-3xl tracking-[0.2em]">FOCO <span class="text-gold">ART</span></p>
                </div>
                <p class="mt-4 max-w-sm text-[14px] leading-relaxed text-stone">
                    Originalna djela i slike naslikane po vašoj mjeri — {{ config('shop.artist') }}. Dostava na kućnu adresu, plaćanje pouzećem.
                </p>
            </div>
            <div>
                <p class="eyebrow mb-3">Stranice</p>
                <ul class="text-[15px] text-stone">
                    @foreach ([['gallery', 'Galerija'], ['commission', 'Slika po mjeri'], ['about', 'O umjetniku'], ['cart', 'Korpa']] as [$route, $label])
                        <li><a href="{{ route($route) }}" class="inline-flex min-h-11 items-center hover:text-bone">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <p class="eyebrow mb-4">Pratite</p>
                <x-social-links />
                @if ($settings->get('phone'))
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $settings->get('phone')) }}" class="mt-5 inline-flex min-h-11 items-center gap-2 text-[15px] text-stone hover:text-bone">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/></svg>
                        {{ $settings->get('phone') }}
                    </a>
                @endif
            </div>
        </div>
        <div class="border-t border-line">
            <p class="safe-bottom mx-auto max-w-7xl px-6 pt-6 text-[12px] tracking-[0.08em] text-ash lg:px-10">© {{ date('Y') }} {{ config('shop.artist') }} · Sva prava zadržana</p>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
