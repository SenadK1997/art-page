@props(['product'])
@php
    $discount = $product->activeDiscount();
    $soldOut = $product->isSoldOut();
    $inStock = $product->isInStock();
    $many = $product->sizes->count() > 1;
@endphp
<a href="{{ route('gallery.show', $product) }}" wire:navigate class="group block">
    <div class="relative overflow-hidden bg-wall p-3 transition-colors duration-500 group-hover:bg-panel sm:p-8">
        <div class="frame-shadow mx-auto overflow-hidden border-4 border-frame outline outline-1 outline-line sm:border-[6px] {{ $soldOut ? 'opacity-60 grayscale-[40%]' : '' }}">
            @if ($product->imageUrl())
                <img src="{{ $product->imageUrl() }}" alt="{{ $product->title }}" loading="lazy" decoding="async"
                     class="block aspect-[4/5] w-full object-cover transition duration-700 group-hover:scale-[1.03]">
            @else
                <div class="aspect-[4/5] w-full bg-line"></div>
            @endif
        </div>
        <div class="absolute left-1.5 top-1.5 flex flex-col items-start gap-1 sm:left-3 sm:top-3 sm:gap-1.5">
            @if ($soldOut)
                <span class="bg-[#0e0d0c] px-2 py-0.5 text-[10px] font-medium uppercase tracking-[0.1em] text-[#ede9e3] sm:px-2.5 sm:py-1 sm:text-[11px]">Prodano</span>
            @else
                @if ($discount)
                    <span class="bg-gold px-2 py-0.5 text-[10px] font-medium uppercase tracking-[0.1em] text-ink sm:px-2.5 sm:py-1 sm:text-[11px]">−{{ $discount }}%<span class="hidden sm:inline"> akcija</span></span>
                @endif
                @if ($inStock)
                    <span class="border border-ok/40 bg-night/90 px-2 py-0.5 text-[10px] uppercase tracking-[0.1em] text-ok sm:px-2.5 sm:py-1 sm:text-[11px]">Na stanju</span>
                @endif
            @endif
        </div>
    </div>
    <div class="mt-3 flex flex-col gap-1 sm:mt-5 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
        <div class="min-w-0">
            <p class="font-display text-[18px] leading-tight transition group-hover:text-gold-light sm:text-[24px]">{{ $product->title }}</p>
            <p class="mt-0.5 truncate text-[12px] text-stone sm:mt-1 sm:text-[13px]">
                {{ $product->style?->name ?? $product->technique ?? 'Ulje na platnu' }}
                @if ($many)<span class="hidden sm:inline"> · {{ $product->sizes->count() }} veličine</span>@endif
            </p>
        </div>
        @if (! $soldOut && ($price = $product->startingFinalPrice()))
            <div class="flex items-baseline gap-2 sm:block sm:text-right">
                <p class="whitespace-nowrap text-[14px] {{ $discount ? 'text-gold-light' : 'text-bone' }}">
                    @if ($many)<span class="text-stone">od</span>@endif
                    {{ number_format($price, 0, ',', '.') }} KM
                </p>
                @if ($discount)
                    <p class="text-[12px] text-ash line-through">{{ number_format($product->startingPrice(), 0, ',', '.') }} KM</p>
                @endif
            </div>
        @endif
    </div>
</a>
