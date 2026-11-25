@props(['image', 'width', 'height', 'frame' => 'none'])
{{-- Product page: the chosen size and frame hung over a ~200 cm sofa, to scale. --}}
<div x-data="wallPreview({{ (int) $width }}, {{ (int) $height }}, @js($frame), @js($image))" {{ $attributes }}>
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
    <x-room-scene tone="wall" />
    <p class="mt-2 text-[12px] leading-snug text-ash">Ilustracija — okvirni prikaz veličine. Stvarna slika, boje i ram mogu izgledati drugačije.</p>
</div>
