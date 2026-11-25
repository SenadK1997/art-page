@props(['image', 'width', 'height', 'frameColor' => null])
{{-- The painting hung over a ~200 cm sofa, drawn to real scale. --}}
<div x-data="wallPreview({{ (int) $width }}, {{ (int) $height }}, @js($frameColor))" {{ $attributes->merge(['class' => 'overflow-hidden']) }}>
    <div class="mb-3 flex items-center justify-between gap-3">
        <p class="text-[12px] uppercase tracking-[0.14em] text-stone">Na zidu · {{ $width }} × {{ $height }} cm</p>
        <div class="flex gap-2" role="group" aria-label="Boja zida">
            <template x-for="w in walls" :key="w.key">
                <button type="button" @click="wall = w.key" :title="w.label" :aria-label="w.label"
                        :class="wall === w.key ? 'ring-1 ring-gold ring-offset-2 ring-offset-night' : ''"
                        class="h-9 w-9 rounded-full border-2 border-panel outline outline-1 outline-line-strong" :style="'background:' + w.color"></button>
            </template>
        </div>
    </div>
    <div x-ref="room" class="relative w-full transition-colors duration-500" :style="'height:' + roomHeight() + 'px;background:' + current().color">
        <div class="absolute inset-0" :style="current().light ? 'background:radial-gradient(ellipse 70% 60% at 50% 10%, rgba(255,255,255,.35), transparent 70%)' : 'background:radial-gradient(ellipse 60% 55% at 50% 0%, rgba(226,190,126,.10), transparent 70%)'"></div>
        <div class="absolute left-1/2 -translate-x-1/2 transition-all duration-300"
             :style="paintingBox() + 'box-shadow:0 18px 30px -12px rgba(0,0,0,.55);'"
>
            <img src="{{ $image }}" alt="" class="block h-full w-full object-cover">
        </div>
        <div class="absolute bottom-0 left-1/2 -translate-x-1/2" :style="'width:' + (200 * scale()) + 'px'">
            <div class="mx-auto rounded-t-[10px]" :style="'height:' + (45 * scale()) + 'px;background:' + current().sofa[0]"></div>
            <div class="rounded-t-[6px]" :style="'height:' + (35 * scale()) + 'px;background:' + current().sofa[1]"></div>
        </div>
        <div class="absolute inset-x-0 bottom-0 h-1.5" :style="'background:' + current().floor"></div>
        <x-room-try class="absolute left-3 top-3" :image="''.json_encode($image)" size="[width, height]" frame="frameColor" />
        <p class="absolute bottom-3 right-3 text-[11px]" :class="current().light ? 'text-black/40' : 'text-ash'">kauč ≈ 200 cm</p>
    </div>
</div>
