<div x-data="commissionCalculator(@js($payload))" class="-mx-6 grid gap-px bg-line sm:mx-0 lg:grid-cols-[1fr_1.15fr]">
    {{-- Controls (left on desktop; under the pinned wall on phones) --}}
    <div class="order-2 bg-night p-6 sm:p-10 lg:order-none lg:row-span-2">
        <fieldset>
            <legend class="field-label">1 · Stil</legend>
            <div class="grid grid-cols-3 gap-3">
                <template x-for="s in styles" :key="s.id">
                    <button type="button" @click="styleId = s.id" :class="styleId === s.id ? 'choice-on' : ''" class="choice group p-2 sm:p-3" :aria-pressed="styleId === s.id">
                        <span class="block aspect-[4/3] overflow-hidden bg-wall">
                            <img x-show="s.image" :src="s.image" alt="" class="h-full w-full object-cover opacity-80 transition group-hover:opacity-100">
                        </span>
                        <span class="mt-2.5 block truncate font-display text-[17px] leading-none sm:text-[19px]" x-text="s.name"></span>
                        <span class="mt-1.5 block text-[11px] leading-snug text-stone" x-text="s.multiplier + ' KM / 100 cm²'"></span>
                    </button>
                </template>
            </div>
        </fieldset>

        <fieldset class="mt-10">
            <legend class="field-label">2 · Veličina platna</legend>
            <div class="space-y-6">
                <label class="block">
                    <span class="flex items-baseline justify-between text-[14px]">
                        <span class="text-stone">Širina</span>
                        <span class="flex items-baseline gap-1">
                            <input type="number" x-model.number="width" @change="clamp()" :min="current().minW" :max="current().maxW" class="w-16 border-b border-line-strong bg-transparent text-right font-display text-[22px] focus:border-gold focus:outline-none" aria-label="Širina u centimetrima">
                            <span class="text-stone">cm</span>
                        </span>
                    </span>
                    <input type="range" x-model.number="width" :min="current().minW" :max="current().maxW" step="5" class="slider mt-4" aria-label="Širina">
                </label>
                <label class="block">
                    <span class="flex items-baseline justify-between text-[14px]">
                        <span class="text-stone">Visina</span>
                        <span class="flex items-baseline gap-1">
                            <input type="number" x-model.number="height" @change="clamp()" :min="current().minH" :max="current().maxH" class="w-16 border-b border-line-strong bg-transparent text-right font-display text-[22px] focus:border-gold focus:outline-none" aria-label="Visina u centimetrima">
                            <span class="text-stone">cm</span>
                        </span>
                    </span>
                    <input type="range" x-model.number="height" :min="current().minH" :max="current().maxH" step="5" class="slider mt-4" aria-label="Visina">
                </label>
            </div>
            <p class="mt-4 text-[12px] text-ash" x-text="current().name + ': od ' + current().minW + ' × ' + current().minH + ' do ' + current().maxW + ' × ' + current().maxH + ' cm'"></p>
            <div class="mt-4 flex flex-wrap gap-2">
                <template x-for="p in presets()" :key="p.join('x')">
                    <button type="button" @click="width = p[0]; height = p[1]" :class="width === p[0] && height === p[1] ? 'choice-on' : ''" class="choice px-3 py-1.5 text-[12px]" x-text="p[0] + ' × ' + p[1]"></button>
                </template>
                <button type="button" @click="[width, height] = [height, width]; clamp()" class="choice px-3 py-1.5 text-[12px] text-stone" title="Zamijeni širinu i visinu">⇄ okreni</button>
            </div>
        </fieldset>

        <fieldset class="mt-10">
            <legend class="field-label">3 · Ram</legend>
            <div class="flex flex-wrap gap-2">
                <template x-for="f in frames" :key="f.key">
                    <button type="button" @click="frame = f.key" :class="frame === f.key ? 'choice-on' : ''" class="choice flex items-center gap-2 px-3 py-1.5 text-[12px]">
                        <span class="h-3 w-3 rounded-full border border-line-strong" :style="f.color ? 'background:' + f.color : ''"></span>
                        <span x-text="f.label"></span>
                    </button>
                </template>
            </div>
            <p class="mt-3 text-[12px] text-ash">Ram ne mijenja cijenu slike.</p>
        </fieldset>

        <fieldset class="mt-10" x-show="current().photo">
            <legend class="field-label">4 · Vaša fotografija <span class="normal-case tracking-normal text-ash">(nije obavezno)</span></legend>
            <p class="text-[13px] leading-relaxed text-stone">Pošaljite fotografiju koju želite naslikanu — portret, vaš grad, kuća, ljubimac. Umjetnik je pregleda i javi vam se prije izrade.</p>

            <label x-show="!localPhoto" class="mt-4 flex cursor-pointer flex-col items-center justify-center gap-2 border border-dashed border-line-strong px-6 py-8 text-center transition hover:border-gold">
                <svg class="h-7 w-7 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><path d="M4 16l4-4 4 4 3-3 5 5M4 6h16v14H4zM9 9.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/></svg>
                <span class="text-[14px] text-bone">Odaberite fotografiju</span>
                <span class="text-[12px] text-ash">JPG, PNG ili WEBP, do 15 MB</span>
                <input type="file" accept="image/*" class="sr-only" wire:model="photo" @change="pickPhoto($event)">
            </label>

            <div x-show="localPhoto" x-cloak class="mt-4 flex items-center gap-4 border border-line p-3">
                <img :src="localPhoto" alt="Vaša fotografija" class="h-16 w-16 object-cover">
                <div class="flex-1 text-[13px]">
                    <p class="text-bone">Fotografija odabrana</p>
                    <p class="text-ash" wire:loading wire:target="photo">Učitavam…</p>
                    <p class="text-ok" wire:loading.remove wire:target="photo">Prikazana je na zidu desno</p>
                </div>
                <button type="button" @click="clearPhoto(); $wire.removePhoto()" class="text-[12px] uppercase tracking-[0.14em] text-ash hover:text-bone">Ukloni</button>
            </div>
            @error('photo') <p class="mt-3 text-[13px] text-red-500">{{ $message }}</p> @enderror
        </fieldset>
    </div>

    {{-- Wall preview: first and pinned under the header on phones, so every change is visible --}}
    <div class="sticky top-20 z-10 order-1 bg-panel shadow-[0_8px_16px_-12px_rgba(0,0,0,.35)] lg:static lg:order-none lg:shadow-none">
        <div class="relative overflow-hidden bg-wall" x-ref="wall" :style="'height:' + wallHeight() + 'px'">
            <div class="spotlight absolute inset-0"></div>
            <div class="absolute left-1/2 -translate-x-1/2 transition-all duration-300 ease-out frame-shadow" :style="paintingStyle()">
                <div class="h-full w-full bg-cover bg-center" :style="image() ? 'background-image:url(' + image() + ')' : 'background:#3a352f'"></div>
            </div>
            <div class="absolute bottom-0 left-1/2 -translate-x-1/2" :style="'width:' + (200 * scale()) + 'px'">
                <div class="mx-auto rounded-t-[10px] bg-[#8c8173]" :style="'height:' + (45 * scale()) + 'px'"></div>
                <div class="rounded-t-[6px] bg-[#9d9284]" :style="'height:' + (35 * scale()) + 'px'"></div>
                <div class="h-1.5 bg-[#6d6357]"></div>
            </div>
            <p class="absolute bottom-3 right-4 text-[11px] tracking-[0.08em] text-ash">kauč ≈ 200 cm</p>
            <div class="absolute left-3 top-3 sm:left-4 sm:top-4">
                <x-room-try image="image()" size="[width, height]" frame="frameColor()" />
            </div>
            {{-- Price badge on the pinned phone preview --}}
            <p class="absolute right-3 top-3 bg-[#0e0d0c]/85 px-3 py-1.5 font-display text-[20px] leading-none text-[#e2be7e] lg:hidden">
                <span x-text="formatted()"></span> KM
            </p>
        </div>
    </div>

    {{-- Price and add to cart --}}
    <div class="order-3 bg-panel lg:order-none">
        <div class="p-6 sm:p-10">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-[12px] uppercase tracking-[0.14em] text-stone">Cijena</p>
                    <p class="mt-1 font-display text-[56px] leading-none text-gold-light" aria-live="polite">
                        <span x-text="formatted()"></span> <span class="text-[28px]">KM</span>
                    </p>
                </div>
                <p class="text-right text-[13px] leading-relaxed text-stone">
                    <span x-text="current().name"></span> · <span x-text="width + ' × ' + height + ' cm'"></span><br>
                    <span x-text="fmt(width * height) + ' cm² × ' + current().multiplier + ' KM / 100 cm²'"></span>
                </p>
            </div>

            <button type="button" x-ref="buy" class="btn-gold mt-8 w-full" wire:loading.attr="disabled" wire:target="add,photo"
                    @click="$wire.add(styleId, width, height, frame)">
                <span wire:loading.remove wire:target="add,photo">Dodaj u korpu</span>
                <span wire:loading wire:target="add,photo">Samo trenutak…</span>
            </button>
            @foreach (['width', 'height', 'styleId'] as $field)
                @error($field) <p class="mt-3 text-[13px] text-red-500">{{ $message }}</p> @enderror
            @endforeach
            <p class="mt-4 flex items-center justify-center gap-2 text-[12px] text-stone">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.3" aria-hidden="true"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="17.5" r="1.5"/><circle cx="17" cy="17.5" r="1.5"/></svg>
                Plaćanje pouzećem — plaćate tek pri dostavi
            </p>
        </div>
    </div>

    {{-- Phones: price and button always within reach --}}
    <div x-show="bar" x-cloak x-transition.opacity class="safe-bottom fixed inset-x-0 bottom-0 z-30 flex items-center gap-4 border-t border-line bg-panel/95 px-4 pt-3 backdrop-blur lg:hidden">
        <div class="min-w-0 flex-1">
            <p class="font-display text-[26px] leading-none text-gold-light"><span x-text="formatted()"></span> KM</p>
            <p class="truncate text-[12px] text-stone" x-text="current().name + ' · ' + width + ' × ' + height + ' cm'"></p>
        </div>
        <button type="button" class="btn-gold shrink-0 px-5" wire:loading.attr="disabled" wire:target="add,photo" @click="$wire.add(styleId, width, height, frame)">
            <span wire:loading.remove wire:target="add,photo">U korpu</span>
            <span wire:loading wire:target="add,photo">…</span>
        </button>
    </div>
</div>
