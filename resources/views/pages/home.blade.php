<x-layouts::site :image="$hero?->imageUrl()" description="Originalne slike Amera Foče i slike po mjeri — Sarajevo, pejzaži, panorame i apstrakcija. Izaberite stil i veličinu, cijenu vidite odmah, plaćanje pouzećem.">
    {{-- Hero: one painting, lit like a museum wall --}}
    <section class="theme-dark spotlight relative overflow-hidden pt-24 pb-16 sm:pt-32 sm:pb-24 lg:pt-40">
        <div class="mx-auto grid max-w-7xl items-center gap-8 px-6 sm:gap-14 lg:grid-cols-[1fr_1.1fr] lg:px-10">
            <div class="reveal order-2 lg:order-1">
                <p class="eyebrow">Ulje i akril na platnu · Sarajevo</p>
                <h1 class="display mt-5 text-[42px] sm:mt-6 sm:text-[68px] lg:text-[84px]">
                    Umjetnost za vaš zid,<br><em class="text-gold-light">naslikana za vas.</em>
                </h1>
                <p class="mt-5 max-w-md text-[15px] leading-relaxed text-stone sm:mt-8 sm:text-[16px]">
                    Originalna djela {{ config('shop.artist_genitive') }} i slike po mjeri — izaberite stil i veličinu, cijenu vidite odmah. Plaćate tek kada slika stigne.
                </p>
                <div class="mt-8 grid gap-3 sm:mt-10 sm:flex sm:flex-wrap sm:gap-4">
                    <a href="#narudzba" class="btn-gold">Naruči sliku po mjeri</a>
                    <a href="{{ route('gallery') }}" wire:navigate class="btn-ghost">Pogledaj galeriju</a>
                </div>
            </div>

            <div class="reveal order-1 lg:order-2" style="animation-delay: .15s">
                @if ($hero && $hero->imageUrl())
                    <a href="{{ route('gallery.show', $hero) }}" wire:navigate class="group block">
                        <div class="frame-shadow mx-auto w-[72%] max-w-xl border-[8px] border-ink outline outline-1 outline-line-strong sm:w-full sm:border-[10px]">
                            <img src="{{ $hero->imageUrl() }}" alt="{{ $hero->title }}" class="block aspect-[4/5] w-full object-cover transition duration-700 group-hover:scale-[1.015]" fetchpriority="high">
                        </div>
                        <p class="mt-6 text-center font-display text-[17px] italic text-stone">
                            {{ $hero->title }}@if($hero->technique) — {{ $hero->technique }}@endif
                            @if($hero->sizes->first()), {{ $hero->sizes->first()->label() }}@endif
                        </p>
                    </a>
                @else
                    <div class="frame-shadow mx-auto aspect-[4/5] w-full max-w-xl border-[10px] border-ink bg-wall outline outline-1 outline-line-strong"></div>
                @endif
            </div>
        </div>
    </section>

    {{-- Promise strip --}}
    <section class="theme-dark border-b border-line">
        <div class="mx-auto grid max-w-7xl divide-line px-6 sm:grid-cols-3 sm:divide-x lg:px-10">
            @foreach ([
                ['Original, ne print', 'Svaka slika je naslikana rukom, potpisana i jedinstvena.'],
                ['Cijena odmah', 'Kalkulator računa cijenu prema stilu i veličini.'],
                ['Plaćanje pouzećem', 'Ništa ne plaćate unaprijed — tek kada slika stigne.'],
            ] as [$t, $d])
                <div class="py-8 sm:px-8 first:sm:pl-0">
                    <p class="font-display text-[22px]">{{ $t }}</p>
                    <p class="mt-2 text-[14px] leading-relaxed text-stone">{{ $d }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Gift offers --}}
    @foreach ($promotions as $promo)
        <section class="pt-10 sm:pt-16">
            <div class="mx-auto max-w-7xl px-6 lg:px-10">
                <div class="grid items-center gap-8 border border-gold/50 bg-panel p-8 sm:p-10 md:grid-cols-[1.3fr_1fr]">
                    <div>
                        <p class="eyebrow">Poklon akcija</p>
                        <h2 class="display mt-4 text-[38px] sm:text-[46px]">{{ $promo->title }}</h2>
                        <p class="mt-4 text-[16px] leading-relaxed text-stone">
                            {{ $promo->description ?: $promo->conditionText().' i izaberite jednu od ovih slika na poklon.' }}
                        </p>
                        @if ($promo->ends_at)<p class="mt-3 text-[13px] text-gold-light">Traje do {{ $promo->ends_at->format('d.m.Y.') }}</p>@endif
                        <a href="{{ $promo->requiredProducts->count() === 1 ? route('gallery.show', $promo->requiredProducts->first()) : route('gallery') }}" wire:navigate class="btn-gold mt-8">Pogledaj slike</a>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach ($promo->availableGifts()->take(3) as $gift)
                            <div>
                                <div class="aspect-[4/5] overflow-hidden bg-wall"><img src="{{ $gift->product->imageUrl() }}" alt="{{ $gift->product->title }}" loading="lazy" class="h-full w-full object-cover"></div>
                                <p class="mt-2 text-[12px] leading-tight">{{ $gift->product->title }}</p>
                                <p class="text-[11px] text-stone">{{ $gift->label() }} · <span class="line-through">{{ $gift->price }} KM</span> <span class="text-gold-light">gratis</span></p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endforeach

    {{-- Categories --}}
    @if ($categories->isNotEmpty())
        <section class="pt-20 sm:pt-28">
            <div class="mx-auto max-w-7xl px-6 lg:px-10">
                <p class="eyebrow">Kategorije</p>
                <h2 class="display mt-4 text-[36px] sm:mt-5 sm:text-[56px]">Pronađite svoj motiv</h2>
                <div class="mt-12 grid grid-cols-2 gap-4 md:grid-cols-4">
                    @foreach ($categories as $cat)
                        <a href="{{ route('gallery', ['tema' => $cat->slug]) }}" wire:navigate class="group relative block aspect-[4/3] overflow-hidden bg-wall">
                            @if ($cat->cover?->imageUrl())
                                <img src="{{ $cat->cover->imageUrl() }}" alt="" loading="lazy" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
                            @endif
                            <span class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/20 to-transparent"></span>
                            <span class="absolute inset-x-0 bottom-0 p-5">
                                <span class="block font-display text-[26px] leading-none text-white">{{ $cat->name }}</span>
                                <span class="mt-1 block text-[12px] text-white/70">{{ $cat->products_count }} slika</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- On sale --}}
    @if ($onSale->isNotEmpty())
        <x-painting-row eyebrow="Akcija" title="Na akciji" :products="$onSale"
            text="Odabrane slike po sniženoj cijeni — dok traje akcija."
            :link="route('gallery', ['ponuda' => 'akcija'])" link-label="Sve slike na akciji" />
    @endif

    {{-- The calculator --}}
    <section id="narudzba" class="scroll-mt-24 pt-28">
        <div class="mx-auto max-w-7xl px-6 lg:px-10">
            <div class="max-w-2xl">
                <p class="eyebrow">Kalkulator</p>
                <h2 class="display mt-4 text-[36px] sm:mt-5 sm:text-[56px]">Naruči sliku po mjeri</h2>
                <p class="mt-5 text-[16px] leading-relaxed text-stone">
                    Izaberite stil i dimenzije platna. Na zidu desno vidite kolika će slika biti u odnosu na kauč, a cijena se mijenja dok pomjerate.
                </p>
            </div>
            <div class="mt-8 sm:mt-14">
                <livewire:commission-calculator />
            </div>
        </div>
    </section>

    {{-- Selected works --}}
    @if ($featured->isNotEmpty())
        <section class="pt-20 sm:pt-32">
            <div class="mx-auto max-w-7xl px-6 lg:px-10">
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <div>
                        <p class="eyebrow">Galerija</p>
                        <h2 class="display mt-4 text-[36px] sm:mt-5 sm:text-[56px]">Odabrana djela</h2>
                    </div>
                    <a href="{{ route('gallery') }}" wire:navigate class="link-underline">Sva djela <span aria-hidden="true">→</span></a>
                </div>
                <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 sm:mt-14 sm:gap-x-8 sm:gap-y-16 lg:grid-cols-3">
                    @foreach ($featured as $product)
                        <x-painting-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Ready to ship --}}
    @if ($inStock->isNotEmpty())
        <x-painting-row eyebrow="Na stanju" title="Spremno za slanje" :products="$inStock"
            text="Gotove slike koje šaljemo odmah — bez čekanja na izradu."
            :link="route('gallery', ['ponuda' => 'na-stanju'])" link-label="Sve slike na stanju" />
    @endif

    {{-- Styles --}}
    @if ($styles->isNotEmpty())
        <section class="theme-dark mt-20 py-20 sm:mt-32 sm:py-28">
            <div class="mx-auto max-w-7xl px-6 lg:px-10">
                <p class="eyebrow">Stilovi</p>
                <h2 class="display mt-4 text-[36px] sm:mt-5 sm:text-[56px]">Tri načina gledanja</h2>
                <div class="mt-10 grid gap-px bg-line sm:mt-14 md:grid-cols-3">
                    @foreach ($styles as $style)
                        <a href="{{ route('gallery', ['stil' => $style->slug]) }}" wire:navigate class="group relative block overflow-hidden bg-night">
                            <div class="aspect-[4/5] overflow-hidden bg-wall">
                                @if ($style->imageUrl())
                                    <img src="{{ $style->imageUrl() }}" alt="" loading="lazy" class="h-full w-full object-cover opacity-80 transition duration-700 group-hover:scale-105 group-hover:opacity-100">
                                @endif
                            </div>
                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink via-ink/70 to-transparent p-8 pt-24">
                                <p class="font-display text-[34px]">{{ $style->name }}</p>
                                @if ($style->description)
                                    <p class="mt-2 text-[14px] leading-relaxed text-stone">{{ $style->description }}</p>
                                @endif
                                <p class="mt-4 text-[12px] uppercase tracking-[0.16em] text-gold">{{ $style->multiplier }} KM / 100 cm²</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- How ordering works --}}
    <section class="pt-20 sm:pt-32">
        <div class="mx-auto max-w-7xl px-6 lg:px-10">
            <div class="grid gap-16 lg:grid-cols-[1fr_1.4fr]">
                <div>
                    <p class="eyebrow">Kako naručiti</p>
                    <h2 class="display mt-4 text-[36px] sm:mt-5 sm:text-[56px]">Od ideje do vašeg zida</h2>
                </div>
                <ol class="grid gap-12 sm:grid-cols-3">
                    @foreach ([
                        ['Izaberite', 'Gotovu sliku iz galerije ili stil i veličinu za sliku po mjeri.'],
                        ['Potvrdimo', 'Javljamo vam se telefonom da potvrdimo detalje i rok izrade.'],
                        ['Platite pri dostavi', 'Slika stiže zapakovana na vašu adresu. Plaćate kuriru.'],
                    ] as $i => [$t, $d])
                        <li>
                            <p class="font-display text-[56px] leading-none text-gold/70">0{{ $i + 1 }}</p>
                            <p class="mt-5 font-display text-[24px]">{{ $t }}</p>
                            <p class="mt-3 text-[14px] leading-relaxed text-stone">{{ $d }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>

    {{-- Artist --}}
    <section class="pt-20 sm:pt-32">
        <div class="mx-auto max-w-4xl px-6 text-center lg:px-10">
            <img src="/images/logo.png" alt="Foco Art" width="120" height="120" loading="lazy" class="mx-auto mb-10 h-28 w-28 opacity-90">
            <p class="eyebrow">Umjetnik</p>
            <p class="display mt-8 text-[32px] italic sm:text-[42px]">
                Za njega, crtanje nije samo vještina, već prvenstveno dar od Boga — svaki potez kista nosi priču.
            </p>
            <p class="mt-8 text-[13px] uppercase tracking-[0.2em] text-stone">{{ config('shop.artist') }}</p>
            <a href="{{ route('about') }}" wire:navigate class="link-underline mt-10">Upoznajte umjetnika <span aria-hidden="true">→</span></a>
        </div>
    </section>
</x-layouts::site>
