<div class="pt-28 sm:pt-36">
    <div class="mx-auto max-w-6xl px-6 lg:px-10">
        <p class="eyebrow">Korpa</p>
        <h1 class="display mt-4 text-[40px] sm:mt-5 sm:text-[64px]">Vaš izbor</h1>

        @if ($lines->isEmpty())
            <div class="mt-16 border-y border-line py-24 text-center">
                <p class="font-display text-[32px]">Korpa je prazna</p>
                <p class="mt-3 text-stone">Izaberite sliku iz galerije ili naručite sliku po mjeri.</p>
                <div class="mt-10 flex flex-wrap justify-center gap-4">
                    <a href="{{ route('gallery') }}" wire:navigate class="btn-gold">Galerija</a>
                    <a href="{{ route('commission') }}" class="btn-ghost">Slika po mjeri</a>
                </div>
            </div>
        @else
            <div class="mt-8 grid gap-10 sm:mt-14 sm:gap-14 lg:grid-cols-[1.6fr_1fr]">
                <ul class="divide-y divide-line border-y border-line">
                    @foreach ($lines as $line)
                        <li class="flex gap-4 py-6 sm:gap-6 sm:py-8" wire:key="line-{{ $line['key'] }}">
                            <a href="{{ $line['url'] }}" class="block h-28 w-24 shrink-0 bg-wall p-2 sm:h-32 sm:w-28">
                                @if ($line['image'])
                                    <img src="{{ $line['image'] }}" alt="" class="h-full w-full object-cover" @if($line['frame'] !== 'none' && config('shop.frames.'.$line['frame'].'.color')) style="border: 3px solid {{ config('shop.frames.'.$line['frame'].'.color') }}" @endif>
                                @endif
                            </a>
                            <div class="flex flex-1 flex-col justify-between gap-4 sm:flex-row">
                                <div>
                                    @if ($line['type'] === 'gift')
                                        <p class="mb-2 inline-block bg-gold px-2 py-0.5 text-[11px] font-medium uppercase tracking-[0.12em] text-ink">Poklon · {{ $line['promotion_title'] }}</p>
                                    @endif
                                    <p class="font-display text-[22px] leading-tight sm:text-[26px]">{{ $line['title'] }}</p>
                                    <p class="mt-2 text-[13px] leading-relaxed text-stone">
                                        @if ($line['style_name']){{ $line['style_name'] }} · @endif{{ $line['width'] }} × {{ $line['height'] }} cm
                                        @if ($line['type'] !== 'gift')<br>Ram: {{ $line['frame_label'] }}@endif
                                        @if ($line['photo'])<br><span class="text-ok">Vaša fotografija je priložena</span>@endif
                                        @if ($line['note'] ?? null)<br><span class="italic">„{{ \Illuminate\Support\Str::limit($line['note'], 90) }}“</span>@endif
                                        @if ($line['type'] === 'commission' && ! $line['photo'] && ! ($line['note'] ?? null))<br><span>Umjetnik vas kontaktira da dogovorite motiv.</span>@endif
                                    </p>
                                    <div class="mt-4 flex items-center gap-4">
                                        @if ($line['type'] !== 'gift')
                                        <div class="flex items-center border border-line">
                                            <button type="button" wire:click="decrement('{{ $line['key'] }}')" class="flex h-10 w-10 items-center justify-center text-stone hover:text-bone" aria-label="Smanji količinu">−</button>
                                            <span class="w-8 text-center text-[14px]">{{ $line['qty'] }}</span>
                                            <button type="button" wire:click="increment('{{ $line['key'] }}')" class="flex h-10 w-10 items-center justify-center text-stone hover:text-bone" aria-label="Povećaj količinu">+</button>
                                        </div>
                                        @endif
                                        <button type="button" wire:click="remove('{{ $line['key'] }}')" class="min-h-10 text-[12px] uppercase tracking-[0.14em] text-ash hover:text-bone">Ukloni</button>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="whitespace-nowrap font-display text-[24px]">{{ $line['type'] === 'gift' ? 'Gratis' : number_format($line['total'], 0, ',', '.').' KM' }}</p>
                                    @if ($line['original_unit_price'])
                                        <p class="text-[13px] text-ash line-through">{{ number_format($line['original_unit_price'] * $line['qty'], 0, ',', '.') }} KM</p>
                                    @endif
                                    @if (in_array($line['type'], ['painting', 'gift'], true))
                                        <p class="mt-1 text-[12px] {{ $line['in_stock'] >= $line['qty'] ? 'text-ok' : 'text-stone' }}">{{ $line['in_stock'] >= $line['qty'] ? 'Na stanju' : $settings->get('made_to_order_time') }}</p>
                                    @else
                                        <p class="mt-1 text-[12px] text-stone">{{ $settings->get('made_to_order_time') }}</p>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <aside class="h-fit space-y-6">
                    @foreach ($promotions as $p)
                        @php($promo = $p['promotion'])
                        <div class="border border-gold/50 bg-gold/5 p-6" wire:key="promo-{{ $promo->id }}">
                            <p class="eyebrow">🎁 {{ $promo->title }}</p>
                            @if ($p['eligible'])
                                <p class="mt-3 text-[14px] text-bone">{{ $p['chosen'] ? 'Vaš poklon je u korpi. Možete ga promijeniti:' : 'Ostvarili ste pravo na poklon — izaberite sliku:' }}</p>
                                <div class="mt-4 grid grid-cols-3 gap-2">
                                    @foreach ($p['gifts'] as $gift)
                                        <button type="button" wire:click="chooseGift({{ $promo->id }}, {{ $gift->id }})"
                                                class="group text-left {{ $p['chosen']?->id === $gift->id ? 'ring-2 ring-gold' : '' }}">
                                            <span class="block aspect-[4/5] overflow-hidden bg-wall"><img src="{{ $gift->product->imageUrl() }}" alt="" class="h-full w-full object-cover transition group-hover:scale-105"></span>
                                            <span class="mt-1.5 block text-[12px] leading-tight">{{ $gift->product->title }}</span>
                                            <span class="block text-[11px] text-stone">{{ $gift->label() }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-3 text-[14px] text-stone">{{ $promo->conditionText() }} i izaberite jednu sliku na poklon.</p>
                            @endif
                        </div>
                    @endforeach

                    <div class="border border-line bg-panel p-8">
                    <p class="field-label mb-6">Pregled</p>
                    <x-order-summary :summary="$summary" />
                    <a href="{{ route('checkout') }}" wire:navigate class="btn-gold mt-8 w-full">Nastavi na narudžbu</a>
                    <p class="mt-4 text-center text-[12px] text-stone">Ništa ne plaćate sada — plaćanje pouzećem.</p>
                    </div>
                </aside>
            </div>
        @endif
    </div>
</div>
