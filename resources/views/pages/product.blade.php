<x-layouts::site :title="$product->title" type="product" :image="$product->imageUrl()" :schema="$schema" :description="\Illuminate\Support\Str::limit(strip_tags((string) $product->description), 155) ?: $product->title.' — originalna slika, '.($product->style?->name ?? 'ulje na platnu').'. Plaćanje pouzećem.'">
    <section class="spotlight pt-24 sm:pt-32">
        <div class="mx-auto max-w-7xl px-6 lg:px-10">
            <nav class="text-[12px] uppercase tracking-[0.16em] text-ash" aria-label="Putanja">
                <a href="{{ route('gallery') }}" wire:navigate class="hover:text-bone">Galerija</a>
                @if ($product->style)
                    <span class="mx-2">/</span>
                    <a href="{{ route('gallery', ['stil' => $product->style->slug]) }}" wire:navigate class="hover:text-bone">{{ $product->style->name }}</a>
                @endif
            </nav>

            <div class="mt-5 grid gap-8 sm:mt-10 sm:gap-14 lg:grid-cols-[1.25fr_1fr]">
                @php($photos = $product->photos())
                @php($images = array_column($photos, 'url'))
                <div x-data="{ active: 0, zoom: false, photos: @js($photos), get current() { return this.photos[this.active] ?? {} } }">
                    {{-- The plain painting photo is framed; room and detail photos are shown as they are --}}
                    <div class="-mx-6 bg-wall sm:mx-0" :class="current.framed ? 'p-6 sm:p-14' : 'p-0'">
                        @if (count($photos))
                            <button type="button" @click="zoom = true" aria-label="Uvećaj sliku"
                                    class="mx-auto block w-full cursor-zoom-in"
                                    :class="current.framed ? 'frame-shadow border-[10px] border-frame outline outline-1 outline-line-strong' : ''">
                                <img :src="current.url" src="{{ $photos[0]['url'] }}" alt="{{ $product->title }}" class="block max-h-[75vh] w-full object-contain">
                            </button>
                        @else
                            <div class="aspect-[4/5] bg-line"></div>
                        @endif
                    </div>
                    @if (count($images) > 1)
                        <div class="mt-4 flex gap-3 overflow-x-auto">
                            @foreach ($images as $i => $url)
                                <button type="button" @click="active = {{ $i }}" :class="active === {{ $i }} ? 'outline-gold' : 'outline-line'" class="h-20 w-20 shrink-0 overflow-hidden outline outline-1 transition" aria-label="Slika {{ $i + 1 }}">
                                    <img src="{{ $url }}" alt="" loading="lazy" class="h-full w-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <template x-teleport="body">
                        <div x-show="zoom" x-cloak x-transition.opacity @keydown.escape.window="zoom = false" @click="zoom = false"
                             class="fixed inset-0 z-50 flex cursor-zoom-out items-center justify-center bg-ink/95 p-6">
                            <img :src="current.url" alt="{{ $product->title }}" class="max-h-full max-w-full object-contain">
                            <button type="button" class="absolute right-6 top-6 text-bone" aria-label="Zatvori">✕</button>
                        </div>
                    </template>
                </div>

                <div class="lg:pt-6">
                    @if ($product->style)<p class="eyebrow">{{ $product->style->name }}</p>@endif
                    <h1 class="display mt-3 text-[38px] sm:mt-4 sm:text-[60px]">{{ $product->title }}</h1>
                    <p class="mt-3 text-[14px] text-stone">
                        {{ $product->technique ?? 'Original na platnu' }}@if($product->year) · {{ $product->year }}@endif · potpisano
                    </p>

                    <div class="mt-10">
                        <livewire:product-purchase :product="$product" />
                    </div>

                    @if ($product->description)
                        <div class="mt-14 border-t border-line pt-10">
                            <p class="field-label">O djelu</p>
                            <div class="mt-4 space-y-4 text-[15px] leading-relaxed text-stone">
                                {!! nl2br(e($product->description)) !!}
                            </div>
                        </div>
                    @endif

                    <dl class="mt-10 grid grid-cols-2 gap-6 border-t border-line pt-10 text-[13px]">
                        <div><dt class="text-ash">Dostava</dt><dd class="mt-1">Kurirskom službom, zapakovano</dd></div>
                        <div><dt class="text-ash">Plaćanje</dt><dd class="mt-1">Pouzećem, pri preuzimanju</dd></div>
                        @if ($product->tags->isNotEmpty())
                            <div class="col-span-2"><dt class="text-ash">Kategorije</dt>
                                <dd class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($product->tags as $tag)
                                        <a href="{{ route('gallery', ['tema' => $tag->slug]) }}" wire:navigate class="border border-line px-3 py-1 text-[12px] text-stone hover:border-gold hover:text-gold">{{ $tag->name }}</a>
                                    @endforeach
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="pt-20 sm:pt-32">
            <div class="mx-auto max-w-7xl px-6 lg:px-10">
                <h2 class="display text-[32px] sm:text-[40px]">Možda vam se svidi</h2>
                <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 sm:mt-12 sm:gap-x-8 sm:gap-y-16 lg:grid-cols-3">
                    @foreach ($related as $item)
                        <x-painting-card :product="$item" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts::site>
