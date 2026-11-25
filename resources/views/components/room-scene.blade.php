@props(['height' => 'sceneHeight()', 'tone' => "'light'"])
{{--
    The painting hung over a ~200 cm sofa, drawn to real scale.
    Lives inside an Alpine component that provides: width, height (cm), frame (key),
    sceneScale() (px per cm), sceneImage(). $height and $tone are JS expressions.
--}}
<div x-ref="wall" {{ $attributes->merge(['class' => 'room relative w-full overflow-hidden']) }}
     :class="'room--' + ({{ $tone }})"
     :style="'height:' + ({{ $height }}) + 'px'">
    {{-- Wall: plaster texture and light falling from the ceiling --}}
    <div class="room__wall absolute inset-0"></div>

    {{-- The painting: frame material, canvas depth, shadow on the wall --}}
    <div class="absolute left-1/2 -translate-x-1/2 transition-all duration-300 ease-out" :style="artBox()">
        <div class="h-full w-full bg-cover bg-center" :style="artCanvas()"></div>
    </div>

    {{-- Skirting board and wooden floor --}}
    <div class="room__floor absolute inset-x-0 bottom-0" :style="'height:' + (14 * sceneScale()) + 'px'"></div>

    {{-- Sofa, ~200 cm wide (the SVG is drawn in centimetres) --}}
    <svg class="absolute left-1/2 -translate-x-1/2" viewBox="0 0 220 92" aria-hidden="true"
         :style="'width:' + (220 * sceneScale()) + 'px;bottom:' + (8 * sceneScale()) + 'px'">
        <defs>
            <linearGradient id="sofa-back" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" class="sofa-hi" /><stop offset="1" class="sofa-mid" />
            </linearGradient>
            <linearGradient id="sofa-seat" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" class="sofa-hi" /><stop offset="1" class="sofa-lo" />
            </linearGradient>
            <radialGradient id="sofa-shadow"><stop offset="0" stop-color="#000" stop-opacity=".32" /><stop offset="1" stop-color="#000" stop-opacity="0" /></radialGradient>
        </defs>
        <ellipse cx="110" cy="88" rx="108" ry="5" fill="url(#sofa-shadow)" />
        <rect x="24" y="80" width="3.5" height="9" rx="1" class="sofa-leg" />
        <rect x="192.5" y="80" width="3.5" height="9" rx="1" class="sofa-leg" />
        <rect x="10" y="38" width="200" height="45" rx="9" class="sofa-base" />
        <rect x="29" y="11" width="80" height="42" rx="11" fill="url(#sofa-back)" />
        <rect x="111" y="11" width="80" height="42" rx="11" fill="url(#sofa-back)" />
        <rect x="29" y="49" width="80" height="20" rx="7" fill="url(#sofa-seat)" />
        <rect x="111" y="49" width="80" height="20" rx="7" fill="url(#sofa-seat)" />
        <rect x="10" y="30" width="22" height="53" rx="10" fill="url(#sofa-back)" />
        <rect x="188" y="30" width="22" height="53" rx="10" fill="url(#sofa-back)" />
    </svg>

    <p class="room__label absolute bottom-2 right-3 text-[11px] tracking-[0.06em]">kauč ≈ 200 cm</p>
    {{ $slot }}
</div>
