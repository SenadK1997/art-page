@props(['eyebrow', 'title', 'text' => null, 'products', 'link' => null, 'linkLabel' => 'Pogledaj sve'])
<section class="pt-20 sm:pt-32">
    <div class="mx-auto max-w-7xl px-6 lg:px-10">
        <div class="flex flex-wrap items-end justify-between gap-6" data-reveal>
            <div class="max-w-2xl">
                <p class="eyebrow flex items-center gap-3"><span class="h-px w-8 bg-gold"></span> {{ $eyebrow }}</p>
                <h2 class="display mt-4 text-[36px] sm:mt-5 sm:text-[56px]">{{ $title }}</h2>
                @if ($text)<p class="mt-4 text-[16px] leading-relaxed text-stone">{{ $text }}</p>@endif
            </div>
            @if ($link)
                <a href="{{ $link }}" wire:navigate class="link-underline">{{ $linkLabel }} <span aria-hidden="true">→</span></a>
            @endif
        </div>
        <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 sm:mt-14 sm:gap-x-8 sm:gap-y-16 lg:grid-cols-3">
            @foreach ($products as $i => $product)
                <div data-reveal style="--d: {{ ($i % 3) * 0.1 }}s">
                    <x-painting-card :product="$product" />
                </div>
            @endforeach
        </div>
    </div>
</section>
