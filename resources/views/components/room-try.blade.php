@props(['image', 'size', 'frame' => 'null'])
{{-- $image, $size and $frame are JavaScript expressions evaluated in the parent Alpine scope. --}}
<div x-data="roomTry(() => {{ $image }}, () => {{ $size }}, () => {{ $frame }})" {{ $attributes }}>
    <label class="inline-flex cursor-pointer items-center gap-2 bg-ink/85 px-3 py-2 text-[11px] font-medium uppercase tracking-[0.14em] text-[#ede9e3] backdrop-blur transition hover:bg-ink">
        <svg class="h-4 w-4 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M3 8h3l2-3h8l2 3h3v11H3z"/><circle cx="12" cy="13" r="3.5"/></svg>
        Isprobaj na svom zidu
        <input type="file" accept="image/*" class="sr-only" @change="pick($event)">
    </label>

    <template x-teleport="body">
        <div x-show="open" x-cloak x-transition.opacity @keydown.escape.window="close()"
             class="fixed inset-0 z-50 flex flex-col bg-[#0e0d0c]/95 p-4 text-[#ede9e3] sm:p-8" role="dialog" aria-modal="true" aria-label="Slika na vašem zidu">
            <div class="mx-auto flex w-full max-w-5xl items-center justify-between gap-4">
                <p class="font-display text-[26px]">Na vašem zidu</p>
                <button type="button" @click="close()" class="px-2 text-[28px] leading-none text-[#9a938a] hover:text-white" aria-label="Zatvori">✕</button>
            </div>
            <p class="mx-auto mt-1 w-full max-w-5xl text-[13px] text-[#9a938a]">Povucite sliku na željeno mjesto i podesite veličinu. Vaša fotografija ostaje na vašem uređaju — ne šaljemo je nigdje.</p>

            <div class="mx-auto mt-4 flex min-h-0 w-full max-w-5xl flex-1 items-center justify-center">
                <div x-ref="stage" class="relative max-h-full touch-none select-none" @pointermove="move($event)" @pointerup="stop()" @pointercancel="stop()">
                    <img :src="room" alt="Vaš zid" class="block max-h-[68vh] w-auto max-w-full" draggable="false">
                    <div class="absolute -translate-x-1/2 -translate-y-1/2 cursor-grab bg-cover bg-center active:cursor-grabbing"
                         :style="overlay() + 'background-image:url(' + img() + ');box-shadow:0 14px 28px -10px rgba(0,0,0,.6)'"
                         @pointerdown.prevent="start($event)"></div>
                </div>
            </div>

            <div class="mx-auto mt-4 flex w-full max-w-5xl flex-wrap items-center gap-4">
                <label class="flex flex-1 items-center gap-3 text-[13px] text-[#9a938a]">
                    Veličina
                    <input type="range" min="5" max="90" step="1" x-model.number="widthPct" class="slider flex-1">
                </label>
                <label class="cursor-pointer border border-[#4a453f] px-4 py-2 text-[12px] uppercase tracking-[0.14em] hover:border-[#c9a15e]">
                    Druga fotografija
                    <input type="file" accept="image/*" class="sr-only" @change="pick($event)">
                </label>
                <button type="button" @click="close()" class="bg-[#c9a15e] px-6 py-2 text-[12px] font-medium uppercase tracking-[0.14em] text-[#0e0d0c] hover:bg-[#e2be7e]">Gotovo</button>
            </div>
        </div>
    </template>
</div>
