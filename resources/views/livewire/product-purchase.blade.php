<div x-data="buyBar()">
    @php($discount = $product->activeDiscount())
    @if ($product->isSoldOut())
        <p class="font-display text-[40px] leading-none text-stone">Prodano</p>
        <p class="mt-4 max-w-sm text-[14px] leading-relaxed text-stone">Ovaj original je pronašao svoj dom. Sličnu sliku možete naručiti po mjeri.</p>
        <a href="{{ route('commission') }}" class="btn-gold mt-8 w-full">Naruči sličnu po mjeri</a>
    @else
        <div class="flex flex-wrap items-baseline gap-x-4 gap-y-2">
            <p class="font-display text-[44px] leading-none text-gold-light">
                {{ $selected ? number_format($selected->finalPrice(), 0, ',', '.') : '—' }} <span class="text-[24px]">KM</span>
            </p>
            @if ($discount && $selected)
                <p class="text-[18px] text-ash line-through">{{ number_format($selected->price, 0, ',', '.') }} KM</p>
                <span class="bg-gold px-2 py-0.5 text-[12px] font-medium text-ink">−{{ $discount }}%</span>
            @endif
        </div>
        @if ($discount && $product->discount_ends_at)
            <p class="mt-3 text-[13px] text-gold">Akcija traje do {{ $product->discount_ends_at->format('d.m.Y.') }}</p>
        @endif

        @if ($selected)
            <p class="mt-4 flex items-center gap-2 text-[13px] {{ $selected->stock > 0 ? 'text-ok' : 'text-stone' }}">
                <span class="h-1.5 w-1.5 rounded-full {{ $selected->stock > 0 ? 'bg-ok' : 'bg-stone' }}"></span>
                @if ($selected->stock > 0)
                    Na stanju — šaljemo odmah{{ $selected->stock === 1 ? ' (posljednji komad)' : '' }}
                @else
                    Slika se slika po narudžbi · {{ $settings->get('made_to_order_time') }}
                @endif
            </p>
        @endif

        @if ($sizes->isNotEmpty())
            <fieldset class="mt-10">
                <legend class="field-label">Veličina</legend>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach ($sizes as $size)
                        @php($ok = $size->isPurchasable())
                        <button type="button" @if($ok) wire:click="$set('sizeId', {{ $size->id }})" @else disabled @endif
                                class="choice {{ $sizeId === $size->id ? 'choice-on' : '' }} {{ $ok ? '' : 'cursor-not-allowed opacity-40' }}"
                                aria-pressed="{{ $sizeId === $size->id ? 'true' : 'false' }}">
                            <span class="block text-[14px]">{{ $size->label() }}</span>
                            <span class="mt-1 block text-[12px] text-stone">
                                @if (! $ok) Rasprodano
                                @else
                                    {{ number_format($size->finalPrice(), 0, ',', '.') }} KM
                                    @if ($size->stock > 0)<span class="text-ok"> · na stanju</span>@endif
                                @endif
                            </span>
                        </button>
                    @endforeach
                </div>
            </fieldset>
        @endif

        <fieldset class="mt-8">
            <legend class="field-label">Ram</legend>
            <div class="flex flex-wrap gap-2">
                @foreach ($frames as $key => $f)
                    <button type="button" wire:click="$set('frame', '{{ $key }}')" class="choice flex items-center gap-2 px-3 py-2 text-[13px] {{ $frame === $key ? 'choice-on' : '' }}">
                        <span class="h-3 w-3 rounded-full border border-line-strong" @if($f['color']) style="background: {{ $f['color'] }}" @endif></span>
                        {{ $f['label'] }}
                    </button>
                @endforeach
            </div>
            <p class="mt-3 text-[12px] text-ash">Ram ne mijenja cijenu slike.</p>
        </fieldset>

        @if ($selected && $product->imageUrl())
            <x-wall-preview class="mt-10" :image="$product->imageUrl()" :width="$selected->width" :height="$selected->height"
                :frame="$frame" wire:key="wall-{{ $selected->id }}-{{ $frame }}" />
        @endif

        @error('sizeId') <p class="mt-4 text-[13px] text-red-600">{{ $message }}</p> @enderror

        <button type="button" x-ref="buy" wire:click="add" wire:loading.attr="disabled" class="btn-gold mt-10 w-full">
            <span wire:loading.remove wire:target="add">Dodaj u korpu</span>
            <span wire:loading wire:target="add">Dodajem…</span>
        </button>
        <p class="mt-4 text-center text-[12px] text-stone">Plaćanje pouzećem · dostava na kućnu adresu</p>

        {{-- Phones: price and button stay within reach until the real button is on screen --}}
        @if ($selected)
            <div x-show="bar" x-cloak x-transition.opacity class="safe-bottom fixed inset-x-0 bottom-0 z-30 flex items-center gap-4 border-t border-line bg-panel/95 px-4 pt-3 backdrop-blur lg:hidden">
                <div class="min-w-0 flex-1">
                    <p class="font-display text-[26px] leading-none text-gold-light">{{ number_format($selected->finalPrice(), 0, ',', '.') }} KM</p>
                    <p class="truncate text-[12px] text-stone">{{ $selected->label() }} · ram {{ mb_strtolower($frames[$frame]['label']) }}</p>
                </div>
                <button type="button" wire:click="add" wire:loading.attr="disabled" class="btn-gold shrink-0 px-5">U korpu</button>
            </div>
        @endif
    @endif
</div>
