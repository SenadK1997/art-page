<div class="pt-28 sm:pt-36" x-data="{ filters: false }">
    <div class="mx-auto max-w-7xl px-6 lg:px-10">
        <p class="eyebrow">Galerija</p>
        <h1 class="display mt-4 text-[44px] sm:mt-5 sm:text-[68px]">Djela na prodaju</h1>
        <p class="mt-4 max-w-xl text-[15px] leading-relaxed text-stone sm:mt-5 sm:text-[16px]">Originali, svaki u nekoliko veličina. Ne vidite ono što tražite? <a href="{{ route('commission') }}" class="text-gold-light underline decoration-gold/40 underline-offset-4 hover:text-gold">Naručite sliku po mjeri.</a></p>

        {{-- Style: one swipeable row on phones --}}
        <div class="mt-8 border-t border-line pt-5 sm:mt-14 lg:flex lg:items-center lg:justify-between lg:gap-6 lg:py-6">
            <div class="scroll-row -mx-6 px-6 lg:mx-0 lg:flex-wrap lg:px-0" role="group" aria-label="Stil">
                <button type="button" wire:click="$set('style', '')" class="choice px-4 py-2 text-[12px] uppercase tracking-[0.14em] {{ $style === '' ? 'choice-on' : '' }}">Svi stilovi</button>
                @foreach ($styles as $s)
                    <button type="button" wire:click="$set('style', '{{ $s->slug }}')" class="choice px-4 py-2 text-[12px] uppercase tracking-[0.14em] {{ $style === $s->slug ? 'choice-on' : '' }}">{{ $s->name }}</button>
                @endforeach
            </div>

            {{-- Phones: search and sorting fold away --}}
            <button type="button" @click="filters = !filters" class="mt-3 flex min-h-11 w-full items-center justify-between border border-line px-4 text-[13px] uppercase tracking-[0.14em] text-bone lg:hidden" :aria-expanded="filters">
                <span>Filteri i sortiranje @if($search || $offer || $sort !== 'novo')<span class="ml-1 text-gold-light">•</span>@endif</span>
                <span class="transition" :class="filters && 'rotate-180'" aria-hidden="true">▾</span>
            </button>
            <div class="mt-3 grid gap-3 lg:mt-0 lg:!flex lg:flex-nowrap" :class="filters ? 'grid' : 'hidden lg:!flex'">
                <label class="sr-only" for="q">Pretraga</label>
                <input id="q" type="search" wire:model.live.debounce.400ms="search" placeholder="Pretraži po nazivu" class="field lg:w-56 lg:min-h-0 lg:py-2 lg:text-[14px]">
                <div class="grid grid-cols-2 gap-3 lg:flex">
                    <label class="sr-only" for="ponuda">Ponuda</label>
                    <select id="ponuda" wire:model.live="offer" class="field lg:w-auto lg:min-h-0 lg:py-2 lg:text-[14px]">
                        <option value="">Sve slike</option>
                        <option value="akcija">Na akciji</option>
                        <option value="na-stanju">Na stanju</option>
                    </select>
                    <label class="sr-only" for="poredaj">Poredaj</label>
                    <select id="poredaj" wire:model.live="sort" class="field lg:w-auto lg:min-h-0 lg:py-2 lg:text-[14px]">
                        <option value="novo">Izdvojeno</option>
                        <option value="cijena-gore">Cijena ↑</option>
                        <option value="cijena-dolje">Cijena ↓</option>
                    </select>
                </div>
            </div>
        </div>

        @if ($tags->isNotEmpty())
            <div class="scroll-row -mx-6 items-center border-b border-line px-6 py-4 lg:mx-0 lg:flex-wrap lg:px-0 lg:py-5" role="group" aria-label="Kategorija">
                <span class="mr-1 hidden text-[12px] uppercase tracking-[0.14em] text-ash lg:inline">Kategorija</span>
                <button type="button" wire:click="$set('tag', '')" class="min-h-10 rounded-full border px-4 text-[14px] transition {{ $tag === '' ? 'border-bone bg-bone text-night' : 'border-line text-stone hover:border-line-strong hover:text-bone' }}">Sve kategorije</button>
                @foreach ($tags as $t)
                    <button type="button" wire:click="$set('tag', '{{ $t->slug }}')" class="min-h-10 rounded-full border px-4 text-[14px] transition {{ $tag === $t->slug ? 'border-bone bg-bone text-night' : 'border-line text-stone hover:border-line-strong hover:text-bone' }}">{{ $t->name }}</button>
                @endforeach
            </div>
        @endif

        <div wire:loading.class="opacity-50" class="transition-opacity">
            @if ($products->isEmpty())
                <div class="py-32 text-center">
                    <p class="font-display text-[32px]">Nema slika za ovaj izbor</p>
                    <p class="mt-3 text-stone">Probajte drugi stil ili temu.</p>
                    <button type="button" wire:click="clearFilters" class="btn-ghost mt-8">Prikaži sve</button>
                </div>
            @else
                <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 sm:mt-14 sm:gap-x-8 sm:gap-y-16 lg:grid-cols-3">
                    @foreach ($products as $product)
                        <x-painting-card :product="$product" wire:key="p-{{ $product->id }}" />
                    @endforeach
                </div>
                <div class="mt-16">
                    {{ $products->links('partials.pagination') }}
                </div>
            @endif
        </div>
    </div>
</div>
