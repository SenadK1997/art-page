<x-layouts::site title="Hvala na narudžbi">
    <section class="pt-28 sm:pt-40">
        <div class="mx-auto max-w-3xl px-6 text-center lg:px-10">
            <p class="eyebrow">Narudžba {{ $order->number }}</p>
            <h1 class="display mt-5 text-[42px] sm:mt-6 sm:text-[68px]">Hvala, {{ \Illuminate\Support\Str::before($order->full_name, ' ') }}.</h1>
            <p class="mx-auto mt-6 max-w-lg text-[16px] leading-relaxed text-stone">
                Narudžba je primljena. Javit ćemo vam se na <span class="text-bone">{{ $order->phone }}</span> da potvrdimo detalje i rok dostave.
            </p>

            <div class="mt-10 border border-line bg-panel p-6 text-left sm:mt-14 sm:p-8">
                <ul class="space-y-4">
                    @foreach ($order->items as $item)
                        <li class="flex justify-between gap-4 text-[14px]">
                            <span>
                                {{ $item->title }} @if($item->qty > 1)<span class="text-stone">× {{ $item->qty }}</span>@endif
                                <span class="block text-[12px] text-stone">@if($item->style_name){{ $item->style_name }} · @endif{{ $item->width }} × {{ $item->height }} cm · {{ $item->frameLabel() ?? 'Bez rama' }}</span>
                            </span>
                            <span class="whitespace-nowrap">{{ $item->kind === 'gift' ? 'Poklon' : number_format($item->lineTotal(), 0, ',', '.').' KM' }}</span>
                        </li>
                    @endforeach
                </ul>
                @if ($order->shipping)
                    <p class="mt-6 flex justify-between border-t border-line pt-4 text-[14px]"><span class="text-stone">Dostava</span><span>{{ $order->shipping }} KM</span></p>
                @endif
                <div class="mt-6 flex items-baseline justify-between border-t border-line pt-6">
                    <span class="text-[13px] uppercase tracking-[0.14em] text-stone">Plaćate pri dostavi</span>
                    <span class="font-display text-[36px] text-gold-light">{{ number_format($order->total, 0, ',', '.') }} KM</span>
                </div>
                <p class="mt-4 text-[13px] text-stone">Dostava: {{ $order->address }}, {{ $order->zip }} {{ $order->city }}</p>
            </div>

            <a href="{{ route('gallery') }}" wire:navigate class="btn-ghost mt-12">Nazad u galeriju</a>
        </div>
    </section>
</x-layouts::site>
