<div class="pt-28 sm:pt-36">
    <div class="mx-auto max-w-6xl px-6 lg:px-10">
        <p class="eyebrow">Narudžba</p>
        <h1 class="display mt-4 text-[40px] sm:mt-5 sm:text-[64px]">Gdje šaljemo sliku?</h1>
        <p class="mt-4 max-w-xl text-[15px] leading-relaxed text-stone">Nakon narudžbe javljamo vam se telefonom da potvrdimo detalje. Plaćate kuriru pri preuzimanju.</p>

        <form wire:submit="submit" class="mt-8 grid gap-10 sm:mt-14 sm:gap-14 lg:grid-cols-[1.4fr_1fr]" novalidate>
            <div class="grid gap-6 sm:grid-cols-2">
                @php($fields = [
                    ['full_name', 'Ime i prezime', 'text', 'name', 'Amra Hadžić', 2],
                    ['phone', 'Telefon', 'tel', 'tel', '061 234 567', 1],
                    ['email', 'Email (nije obavezno)', 'email', 'email', 'ime@primjer.ba', 1],
                    ['address', 'Ulica i broj', 'text', 'street-address', 'Ferhadija 12', 2],
                    ['city', 'Grad', 'text', 'address-level2', 'Sarajevo', 1],
                    ['zip', 'Poštanski broj', 'text', 'postal-code', '71000', 1],
                    ['country', 'Država', 'text', 'country-name', '', 2],
                ])
                @foreach ($fields as [$name, $label, $type, $auto, $ph, $span])
                    <div class="{{ $span === 2 ? 'sm:col-span-2' : '' }}">
                        <label for="{{ $name }}" class="field-label">{{ $label }}</label>
                        <input id="{{ $name }}" type="{{ $type }}" wire:model.blur="{{ $name }}" autocomplete="{{ $auto }}" placeholder="{{ $ph }}" class="field @error($name) border-red-500 @enderror">
                        @error($name) <p class="mt-2 text-[13px] text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endforeach
                <div class="sm:col-span-2">
                    <label for="note" class="field-label">Napomena (nije obavezno)</label>
                    <textarea id="note" wire:model.blur="note" rows="4" placeholder="Npr. želim nijanse plave, ili najbolje vrijeme za poziv" class="field"></textarea>
                </div>
                <div class="hidden" aria-hidden="true">
                    <label for="website">Website</label>
                    <input id="website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
                </div>
            </div>

            <aside class="h-fit border border-line bg-panel p-6 sm:p-8">
                <p class="field-label">Vaša narudžba</p>
                <ul class="mt-6 space-y-5">
                    @foreach ($lines as $line)
                        <li class="flex justify-between gap-4 text-[14px]">
                            <span>
                                <span class="block">{{ $line['title'] }} @if($line['qty'] > 1)<span class="text-stone">× {{ $line['qty'] }}</span>@endif</span>
                                <span class="block text-[12px] text-stone">@if($line['style_name']){{ $line['style_name'] }} · @endif{{ $line['width'] }} × {{ $line['height'] }} cm · {{ $line['frame_label'] }}</span>
                            </span>
                            <span class="whitespace-nowrap">{{ $line['type'] === 'gift' ? 'Poklon' : number_format($line['total'], 0, ',', '.').' KM' }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-6 border-t border-line pt-6">
                    <x-order-summary :summary="$summary" />
                </div>
                <div class="mt-6 flex gap-3 border border-gold/30 bg-gold/5 p-4 text-[13px] leading-relaxed text-stone">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.3" aria-hidden="true"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="17.5" r="1.5"/><circle cx="17" cy="17.5" r="1.5"/></svg>
                    <span><span class="text-bone">Plaćanje pouzećem.</span> Ne plaćate ništa sada — iznos plaćate kuriru pri preuzimanju.</span>
                </div>
                <button type="submit" class="btn-gold mt-8 w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submit">Potvrdi narudžbu</span>
                    <span wire:loading wire:target="submit">Šaljem…</span>
                </button>
                <a href="{{ route('cart') }}" wire:navigate class="mt-4 block text-center text-[12px] uppercase tracking-[0.14em] text-ash hover:text-bone">← Nazad u korpu</a>
            </aside>
        </form>
    </div>
</div>
