<x-layouts::site :image="$heroView['image'] ?? null" description="Originalne slike iz ateljea Amera Foče u Sarajevu — reljefne teksture, zlato i svjetlo, naslikano rukom. Gotova djela i slike po mjeri, plaćanje pouzećem.">

    {{-- ============ HERO: the main image edge to edge, no frame ============ --}}
    <section class="theme-dark relative isolate min-h-[100svh] overflow-hidden bg-[#0e0d0c]">
        @if ($heroView)
            <a href="{{ $heroView['url'] }}" wire:navigate class="absolute inset-x-0 top-0 block h-[62svh] overflow-hidden lg:inset-y-0 lg:left-auto lg:right-0 lg:h-auto lg:w-[54%]" aria-label="{{ $heroView['alt'] }}">
                <img src="{{ $heroView['image'] }}" alt="{{ $heroView['alt'] }}" fetchpriority="high"
                     class="kenburns h-full w-full object-cover">
                {{-- Phones: dark from the bottom so the headline reads over the photo --}}
                <span class="absolute inset-0 bg-gradient-to-t from-[#0e0d0c] via-[#0e0d0c]/10 to-transparent lg:hidden"></span>
                {{-- Desktop: soft blend into the black text side --}}
                <span class="absolute inset-y-0 left-0 hidden w-1/3 bg-gradient-to-r from-[#0e0d0c] to-transparent lg:block"></span>
            </a>
            @if ($heroView['caption'])
                <p class="absolute bottom-8 right-8 z-10 hidden items-center gap-3 font-display text-[16px] italic text-white/80 lg:flex">
                    <span class="h-px w-10 bg-gold"></span>{{ $heroView['caption'] }}
                </p>
            @endif
        @endif

        <div class="relative z-10 mx-auto flex min-h-[100svh] max-w-7xl items-end px-6 pb-12 {{ $heroView ? 'pt-[52svh]' : 'pt-28' }} lg:items-center lg:px-10 lg:pb-20 lg:pt-28">
            <div class="max-w-xl lg:w-[44%]">
                <p class="hero-in eyebrow flex items-center gap-3" style="--d: .1s">
                    <span class="h-px w-8 bg-gold"></span> Atelje · Sarajevo
                </p>
                <h1 class="display mt-6 text-[48px] leading-[0.98] sm:text-[72px] lg:text-[92px]">
                    <span class="hero-line"><span style="--d: .2s">Umjetnost</span></span>
                    <span class="hero-line"><span style="--d: .35s">koja oživi</span></span>
                    <span class="hero-line"><em class="text-gold-light" style="--d: .5s">vaš prostor.</em></span>
                </h1>
                <p class="hero-in mt-7 max-w-md text-[16px] leading-relaxed text-[#cfc8bd] sm:text-[17px]" style="--d: .7s">
                    Originalne slike iz ateljea {{ config('shop.artist_genitive') }} — teksture, zlato i svjetlo, naslikano rukom.
                    Izaberite gotovo djelo ili naručite sliku po mjeri svog zida.
                </p>
                <div class="hero-in mt-9 grid gap-3 sm:flex sm:flex-wrap sm:gap-4" style="--d: .85s">
                    <a href="{{ route('gallery') }}" wire:navigate class="btn-gold">Pogledaj galeriju</a>
                    <a href="{{ route('commission') }}" class="btn-ghost border-white/30 text-white hover:border-gold">Slika po mjeri</a>
                </div>
                <dl class="hero-in mt-10 flex flex-wrap gap-x-6 gap-y-2 text-[12px] uppercase tracking-[0.16em] text-[#9a938a]" style="--d: 1s">
                    <div>Ručni rad</div><div aria-hidden="true" class="text-gold">✦</div>
                    <div>Potpisan original</div><div aria-hidden="true" class="text-gold">✦</div>
                    <div>Plaćanje pouzećem</div>
                </dl>
            </div>
        </div>

        <a href="#dalje" class="absolute bottom-8 left-1/2 z-10 hidden -translate-x-1/2 flex-col items-center gap-3 text-[11px] uppercase tracking-[0.3em] text-[#9a938a] lg:left-10 lg:flex lg:translate-x-0">
            Skrolaj <span class="scroll-cue h-12 w-px bg-gold/70"></span>
        </a>
    </section>

    {{-- ============ Gold ribbon ============ --}}
    <div id="dalje" class="overflow-hidden bg-gold py-4 text-ink" aria-hidden="true">
        <div class="marquee flex w-max gap-10 whitespace-nowrap text-[13px] font-medium uppercase tracking-[0.28em]">
            @for ($i = 0; $i < 2; $i++)
                @foreach (['Original', 'Ručno naslikano', 'Sarajevo', 'Slike po mjeri', 'Reljef i tekstura', 'Plaćanje pouzećem', 'Potpisano'] as $word)
                    <span>{{ $word }}</span><span>✦</span>
                @endforeach
            @endfor
        </div>
    </div>

    {{-- ============ Gift offers ============ --}}
    @foreach ($promotions as $promo)
        <section class="pt-14 sm:pt-20">
            <div class="mx-auto max-w-7xl px-6 lg:px-10" data-reveal>
                <div class="relative grid items-center gap-8 overflow-hidden border border-gold/50 bg-panel p-8 sm:p-12 md:grid-cols-[1.3fr_1fr]">
                    <span class="pointer-events-none absolute -right-10 -top-16 font-display text-[220px] leading-none text-gold/10" aria-hidden="true">✦</span>
                    <div class="relative">
                        <p class="eyebrow">Poklon akcija</p>
                        <h2 class="display mt-4 text-[38px] sm:text-[52px]">{{ $promo->title }}</h2>
                        <p class="mt-4 text-[16px] leading-relaxed text-stone">
                            {{ $promo->description ?: $promo->conditionText().' i izaberite jednu od ovih slika na poklon.' }}
                        </p>
                        @if ($promo->ends_at)<p class="mt-3 text-[13px] text-gold-light">Traje do {{ $promo->ends_at->format('d.m.Y.') }}</p>@endif
                        <a href="{{ $promo->requiredProducts->count() === 1 ? route('gallery.show', $promo->requiredProducts->first()) : route('gallery') }}" wire:navigate class="btn-gold mt-8">Pogledaj slike</a>
                    </div>
                    <div class="relative grid grid-cols-3 gap-3">
                        @foreach ($promo->availableGifts()->take(3) as $i => $gift)
                            <div class="transition duration-500 hover:-translate-y-1" style="transform: rotate({{ [-2, 1, 2.5][$i] ?? 0 }}deg)">
                                <div class="aspect-[4/5] overflow-hidden border-4 border-frame bg-wall shadow-lg"><img src="{{ $gift->product->imageUrl() }}" alt="{{ $gift->product->title }}" loading="lazy" class="h-full w-full object-cover"></div>
                                <p class="mt-2 text-[12px] leading-tight">{{ $gift->product->title }}</p>
                                <p class="text-[12px] text-stone"><span class="line-through">{{ $gift->price }} KM</span> <span class="text-gold-light">gratis</span></p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endforeach

    {{-- ============ A painting in the spotlight ============ --}}
    @if ($spotlight)
        <section class="pt-20 sm:pt-32">
            <div class="mx-auto grid max-w-7xl items-center gap-10 px-6 lg:grid-cols-[1.1fr_1fr] lg:gap-20 lg:px-10">
                <a href="{{ route('gallery.show', $spotlight) }}" wire:navigate class="group relative block bg-wall {{ $spotlight->coverIsRoomPhoto() ? 'p-3 sm:p-4' : 'p-8 sm:p-14' }}" data-reveal>
                    @if ($spotlight->coverIsRoomPhoto())
                        {{-- Room photo: no extra frame --}}
                        <div class="overflow-hidden">
                            <img src="{{ $spotlight->imageUrl() }}" alt="{{ $spotlight->title }}" loading="lazy" class="block aspect-[4/5] w-full object-cover transition duration-[1.2s] group-hover:scale-[1.04]">
                        </div>
                    @else
                        <div class="frame-shadow mx-auto max-w-md overflow-hidden border-[8px] border-frame transition duration-700 group-hover:-translate-y-1.5">
                            <img src="{{ $spotlight->imageUrl() }}" alt="{{ $spotlight->title }}" loading="lazy" class="block aspect-[4/5] w-full object-cover transition duration-[1.2s] group-hover:scale-[1.04]">
                        </div>
                    @endif
                    <span class="absolute left-0 top-0 h-16 w-16 border-l border-t border-gold/70"></span>
                    <span class="absolute bottom-0 right-0 h-16 w-16 border-b border-r border-gold/70"></span>
                </a>
                <div data-reveal style="--d: .15s">
                    <p class="eyebrow flex items-center gap-3"><span class="h-px w-8 bg-gold"></span> Djelo u fokusu</p>
                    <h2 class="display mt-5 text-[44px] sm:text-[68px]">{{ $spotlight->title }}</h2>
                    <p class="mt-4 text-[15px] uppercase tracking-[0.14em] text-stone">
                        {{ collect([$spotlight->style?->name, $spotlight->technique, $spotlight->year])->filter()->join(' · ') }}
                    </p>
                    @if ($spotlight->description)
                        <p class="mt-6 max-w-lg text-[16px] leading-relaxed text-stone">{{ \Illuminate\Support\Str::limit($spotlight->description, 240) }}</p>
                    @endif
                    <div class="mt-8 flex flex-wrap items-baseline gap-x-6 gap-y-3">
                        @if ($price = $spotlight->startingFinalPrice())
                            <p class="font-display text-[40px] text-gold-light">{{ $spotlight->sizes->count() > 1 ? 'od ' : '' }}{{ number_format($price, 0, ',', '.') }} KM</p>
                        @endif
                        <p class="text-[14px] text-stone">{{ $spotlight->sizes->map->label()->join(' · ') }}</p>
                    </div>
                    <a href="{{ route('gallery.show', $spotlight) }}" wire:navigate class="btn-gold mt-8">Pogledaj sliku</a>
                </div>
            </div>
        </section>
    @endif

    {{-- ============ Categories ============ --}}
    @if ($categories->isNotEmpty())
        <section class="pt-20 sm:pt-32">
            <div class="mx-auto max-w-7xl px-6 lg:px-10">
                <div data-reveal>
                    <p class="eyebrow">Kategorije</p>
                    <h2 class="display mt-4 text-[36px] sm:mt-5 sm:text-[56px]">Pronađite svoj motiv</h2>
                </div>
                <div class="mt-10 grid grid-cols-2 gap-3 sm:mt-12 sm:gap-4 md:grid-cols-4">
                    @foreach ($categories as $i => $cat)
                        <a href="{{ route('gallery', ['tema' => $cat->slug]) }}" wire:navigate data-reveal style="--d: {{ $i * 0.08 }}s"
                           class="group relative block overflow-hidden bg-wall {{ $i === 0 ? 'col-span-2 row-span-2 aspect-square md:aspect-auto' : 'aspect-[4/3]' }}">
                            @if ($cat->cover?->imageUrl())
                                <img src="{{ $cat->cover->imageUrl() }}" alt="" loading="lazy" class="absolute inset-0 h-full w-full object-cover transition duration-[1.2s] group-hover:scale-110">
                            @endif
                            <span class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent transition group-hover:from-black/70"></span>
                            <span class="absolute inset-x-0 bottom-0 p-5 sm:p-6">
                                <span class="block font-display leading-none text-white {{ $i === 0 ? 'text-[38px] sm:text-[48px]' : 'text-[24px] sm:text-[28px]' }}">{{ $cat->name }}</span>
                                <span class="mt-2 flex items-center gap-2 text-[12px] uppercase tracking-[0.14em] text-white/70">
                                    {{ $cat->products_count }} slika <span class="transition-transform group-hover:translate-x-1" aria-hidden="true">→</span>
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============ On sale ============ --}}
    @if ($onSale->isNotEmpty())
        <x-painting-row eyebrow="Akcija" title="Na akciji" :products="$onSale"
            text="Odabrane slike po sniženoj cijeni — dok traje akcija."
            :link="route('gallery', ['ponuda' => 'akcija'])" link-label="Sve slike na akciji" />
    @endif

    {{-- ============ Selected works ============ --}}
    @if ($featured->isNotEmpty())
        <x-painting-row eyebrow="Galerija" title="Odabrana djela" :products="$featured"
            :link="route('gallery')" link-label="Sva djela" />
    @endif

    {{-- ============ The calculator, on a dark stage ============ --}}
    <section id="narudzba" class="theme-dark relative mt-20 scroll-mt-20 overflow-hidden py-20 sm:mt-32 sm:py-28">
        <div class="spotlight pointer-events-none absolute inset-0"></div>
        <div class="relative mx-auto max-w-7xl px-6 lg:px-10">
            <div class="grid gap-6 lg:grid-cols-[1fr_1fr] lg:items-end" data-reveal>
                <div>
                    <p class="eyebrow flex items-center gap-3"><span class="h-px w-8 bg-gold"></span> Slika po mjeri</p>
                    <h2 class="display mt-5 text-[40px] sm:text-[64px]">Vaš zid, <em class="text-gold-light">vaša mjera.</em></h2>
                </div>
                <p class="max-w-md text-[16px] leading-relaxed text-stone lg:justify-self-end">
                    Izaberite stil i dimenzije — slika se pojavi na zidu u pravoj veličini, a cijenu vidite odmah. Možete poslati i svoju fotografiju.
                </p>
            </div>
            <div class="theme-light mt-10 sm:mt-14" data-reveal style="--d: .1s">
                <livewire:commission-calculator />
            </div>
        </div>
    </section>

    {{-- ============ Ready to ship ============ --}}
    @if ($inStock->isNotEmpty())
        <x-painting-row eyebrow="Na stanju" title="Spremno za slanje" :products="$inStock"
            text="Gotove slike koje šaljemo odmah — bez čekanja na izradu."
            :link="route('gallery', ['ponuda' => 'na-stanju'])" link-label="Sve slike na stanju" />
    @endif

    {{-- ============ Styles ============ --}}
    @if ($styles->isNotEmpty())
        <section class="pt-20 sm:pt-32">
            <div class="mx-auto max-w-7xl px-6 lg:px-10">
                <div data-reveal>
                    <p class="eyebrow">Stilovi</p>
                    <h2 class="display mt-4 text-[36px] sm:mt-5 sm:text-[56px]">Tri načina gledanja</h2>
                </div>
                <div class="mt-10 grid gap-4 sm:mt-14 md:grid-cols-3">
                    @foreach ($styles as $i => $style)
                        <a href="{{ route('gallery', ['stil' => $style->slug]) }}" wire:navigate data-reveal style="--d: {{ $i * 0.1 }}s"
                           class="group relative block overflow-hidden bg-[#0e0d0c] {{ $i === 1 ? 'md:translate-y-10' : '' }}">
                            <div class="aspect-[4/5] overflow-hidden">
                                @if ($style->imageUrl())
                                    <img src="{{ $style->imageUrl() }}" alt="" loading="lazy" class="h-full w-full object-cover opacity-85 transition duration-[1.2s] group-hover:scale-110 group-hover:opacity-100">
                                @endif
                            </div>
                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 via-black/60 to-transparent p-7 pt-28 text-white">
                                <p class="font-display text-[13px] uppercase tracking-[0.3em] text-[#e2be7e]">0{{ $i + 1 }}</p>
                                <p class="mt-2 font-display text-[38px] leading-none">{{ $style->name }}</p>
                                @if ($style->description)
                                    <p class="mt-3 max-h-0 overflow-hidden text-[14px] leading-relaxed text-white/75 transition-all duration-500 group-hover:max-h-24 max-md:max-h-24">{{ $style->description }}</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============ How ordering works ============ --}}
    <section class="pt-24 sm:pt-40">
        <div class="mx-auto max-w-7xl px-6 lg:px-10">
            <div class="grid gap-14 lg:grid-cols-[1fr_1.5fr]">
                <div data-reveal>
                    <p class="eyebrow">Kako naručiti</p>
                    <h2 class="display mt-4 text-[36px] sm:mt-5 sm:text-[56px]">Od ideje do vašeg zida</h2>
                </div>
                <ol class="grid gap-10 sm:grid-cols-3 sm:gap-8">
                    @foreach ([
                        ['Izaberite', 'Gotovu sliku iz galerije ili stil i veličinu za sliku po mjeri.'],
                        ['Potvrdimo', 'Javljamo vam se telefonom da potvrdimo detalje i rok izrade.'],
                        ['Platite pri dostavi', 'Slika stiže zapakovana na vašu adresu. Plaćate kuriru.'],
                    ] as $i => [$t, $d])
                        <li class="relative border-t border-line pt-6" data-reveal style="--d: {{ $i * 0.12 }}s">
                            <span class="absolute -top-px left-0 h-px w-12 bg-gold"></span>
                            <p class="font-display text-[64px] leading-none text-gold/60">0{{ $i + 1 }}</p>
                            <p class="mt-4 font-display text-[26px]">{{ $t }}</p>
                            <p class="mt-3 text-[14px] leading-relaxed text-stone">{{ $d }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>

    {{-- ============ Artist ============ --}}
    <section class="pt-24 sm:pt-40">
        <div class="relative mx-auto max-w-5xl px-6 text-center lg:px-10" data-reveal>
            <img src="/images/logo.png" alt="" aria-hidden="true" class="pointer-events-none absolute left-1/2 top-1/2 h-[420px] w-[420px] -translate-x-1/2 -translate-y-1/2 opacity-[0.07]">
            <p class="relative eyebrow">Umjetnik</p>
            <p class="relative display mt-8 text-[32px] italic leading-snug sm:text-[48px]">
                <span class="text-gold" aria-hidden="true">“</span>Za njega, crtanje nije samo vještina, već prvenstveno dar od Boga — svaki potez kista nosi priču.<span class="text-gold" aria-hidden="true">”</span>
            </p>
            <p class="relative mt-8 text-[13px] uppercase tracking-[0.24em] text-stone">{{ config('shop.artist') }}</p>
            <a href="{{ route('about') }}" wire:navigate class="relative link-underline mt-8">Upoznajte umjetnika <span aria-hidden="true">→</span></a>
        </div>
    </section>

    {{-- ============ Closing invitation ============ --}}
    <section class="theme-dark relative isolate mt-24 overflow-hidden sm:mt-40">
        @if ($heroView)
            <img src="{{ $heroView['image'] }}" alt="" aria-hidden="true" loading="lazy" class="absolute inset-0 -z-10 h-full w-full object-cover opacity-30">
        @endif
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-[#0e0d0c] via-[#0e0d0c]/85 to-[#0e0d0c]/50"></div>
        <div class="mx-auto max-w-7xl px-6 py-24 sm:py-32 lg:px-10" data-reveal>
            <p class="eyebrow">Atelje Foco Art</p>
            <h2 class="display mt-5 max-w-2xl text-[42px] sm:text-[72px]">Imate zid koji <em class="text-gold-light">čeka sliku?</em></h2>
            <p class="mt-6 max-w-lg text-[16px] leading-relaxed text-stone">Pošaljite mjere ili fotografiju — umjetnik vam se javi s prijedlogom. Plaćate tek kada slika stigne.</p>
            <div class="mt-10 grid gap-3 sm:flex sm:gap-4">
                <a href="{{ route('commission') }}" class="btn-gold">Naruči sliku po mjeri</a>
                <a href="{{ route('gallery') }}" wire:navigate class="btn-ghost">Galerija</a>
            </div>
        </div>
    </section>
</x-layouts::site>
