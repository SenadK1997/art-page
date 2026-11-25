<!DOCTYPE html>
<html lang="bs" class="theme-light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Kalkulator cijena — {{ config('shop.name') }}</title>
    <meta name="description" content="Brzi kalkulator cijene slike po mjeri — {{ config('shop.name') }}.">
    <meta name="robots" content="noindex">
    <meta name="theme-color" content="#0e0d0c">

    {{-- Installable as its own app that opens straight here --}}
    <link rel="manifest" href="/kalkulator.webmanifest">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Kalkulator">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="icon" href="/favicon.ico" sizes="32x32">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="theme-light min-h-dvh bg-night text-bone">
<div x-data="quickCalc(@js($styles), @js(route('commission')))" class="mx-auto flex min-h-dvh max-w-md flex-col">

    {{-- Top bar --}}
    <header class="theme-dark sticky top-0 z-20 flex items-center gap-3 bg-[#0e0d0c] px-4 pb-3" style="padding-top: max(0.75rem, env(safe-area-inset-top))">
        <img src="/images/logo-160.png" alt="" class="h-10 w-10">
        <div class="min-w-0 flex-1">
            <p class="font-display text-[22px] leading-none">Kalkulator</p>
            <p class="text-[11px] uppercase tracking-[0.16em] text-stone">Foco Art · cijene po mjeri</p>
        </div>
        <span x-show="offline" x-cloak class="rounded-full border border-line-strong px-2.5 py-1 text-[11px] text-stone">offline</span>
        <button type="button" x-show="canInstall" x-cloak @click="install()" class="min-h-10 bg-gold px-3 text-[12px] font-medium uppercase tracking-[0.1em] text-ink">Instaliraj</button>
    </header>

    <main class="flex-1 space-y-6 px-4 pb-32 pt-5">
        {{-- Style --}}
        <section>
            <p class="field-label">Stil</p>
            <div class="grid grid-cols-3 gap-2">
                <template x-for="s in styles" :key="s.id">
                    <button type="button" @click="styleId = s.id" :aria-pressed="styleId === s.id"
                            :class="styleId === s.id ? 'border-bone bg-bone text-night' : 'border-line bg-panel text-bone'"
                            class="min-h-16 border px-2 py-2 text-center transition">
                        <span class="block font-display text-[19px] leading-tight" x-text="s.name"></span>
                        <span class="block text-[11px] opacity-70" x-text="s.multiplier + ' KM/100 cm²'"></span>
                    </button>
                </template>
            </div>
        </section>

        {{-- Size --}}
        <section>
            <div class="flex items-baseline justify-between">
                <p class="field-label">Veličina (cm)</p>
                <button type="button" @click="[width, height] = [height, width]" class="min-h-10 text-[13px] text-stone">⇄ okreni</button>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <template x-for="dim in ['width', 'height']" :key="dim">
                    <div class="border border-line bg-panel p-3">
                        <p class="text-[12px] text-stone" x-text="dim === 'width' ? 'Širina' : 'Visina'"></p>
                        <div class="mt-1 flex items-center gap-1">
                            <button type="button" @click="step(dim, -5)" class="flex h-11 w-11 shrink-0 items-center justify-center border border-line text-[22px] text-stone active:bg-wall" :aria-label="(dim === 'width' ? 'Širina' : 'Visina') + ' manje'">−</button>
                            <input type="number" inputmode="numeric" min="1" max="999" x-model.number="$data[dim]" @focus="$event.target.select()"
                                   class="w-full min-w-0 bg-transparent text-center font-display text-[34px] leading-none focus:outline-none"
                                   :aria-label="dim === 'width' ? 'Širina u cm' : 'Visina u cm'">
                            <button type="button" @click="step(dim, 5)" class="flex h-11 w-11 shrink-0 items-center justify-center border border-line text-[22px] text-stone active:bg-wall" :aria-label="(dim === 'width' ? 'Širina' : 'Visina') + ' više'">+</button>
                        </div>
                    </div>
                </template>
            </div>
            <div class="scroll-row -mx-4 mt-3 px-4">
                <template x-for="p in presets" :key="p.join('x')">
                    <button type="button" @click="width = p[0]; height = p[1]"
                            :class="width === p[0] && height === p[1] ? 'border-bone bg-bone text-night' : 'border-line text-stone'"
                            class="min-h-10 rounded-full border px-4 text-[14px]" x-text="p[0] + '×' + p[1]"></button>
                </template>
            </div>
            <p x-show="outOfRange()" x-cloak class="mt-3 border-l-2 border-gold pl-3 text-[13px] text-stone"
               x-text="'Izvan granica na stranici za ' + current().name + ' (' + current().minW + '×' + current().minH + ' – ' + current().maxW + '×' + current().maxH + ' cm). Cijena je ipak izračunata.'"></p>
        </section>

        {{-- Result --}}
        <section class="theme-dark bg-[#0e0d0c] px-5 py-6 text-center">
            <p class="text-[12px] uppercase tracking-[0.16em] text-stone" x-text="current().name + ' · ' + width + ' × ' + height + ' cm'"></p>
            <p class="mt-2 font-display text-[64px] leading-none text-gold-light" aria-live="polite">
                <span x-text="fmt(finalPrice())"></span><span class="text-[30px]"> KM</span>
            </p>
            <p x-show="discount > 0" x-cloak class="mt-2 text-[15px] text-stone">
                <span class="line-through" x-text="fmt(price()) + ' KM'"></span> · popust <span x-text="discount + '%'"></span>
            </p>
            <p class="mt-3 text-[12px] text-ash" x-text="fmt(width * height) + ' cm² ÷ 100 × ' + current().multiplier + ' KM'"></p>
        </section>

        {{-- Discount --}}
        <section>
            <p class="field-label">Popust</p>
            <div class="flex flex-wrap gap-2">
                <template x-for="d in [0, 5, 10, 15, 20]" :key="d">
                    <button type="button" @click="discount = d"
                            :class="discount === d ? 'border-bone bg-bone text-night' : 'border-line text-stone'"
                            class="min-h-10 min-w-14 rounded-full border px-3 text-[14px]" x-text="d ? d + '%' : 'bez'"></button>
                </template>
                <label class="flex min-h-10 items-center gap-1 rounded-full border border-line px-3 text-[14px] text-stone">
                    <input type="number" inputmode="numeric" min="0" max="90" x-model.number="discount" class="w-10 bg-transparent text-right text-bone focus:outline-none" aria-label="Popust u procentima">%
                </label>
            </div>
        </section>

        {{-- Same size in every style --}}
        <section>
            <p class="field-label" x-text="'Ista veličina u svim stilovima'"></p>
            <ul class="divide-y divide-line border-y border-line">
                <template x-for="s in styles" :key="s.id">
                    <li>
                        <button type="button" @click="styleId = s.id" class="flex min-h-12 w-full items-center justify-between py-2 text-left">
                            <span class="text-[15px]" :class="styleId === s.id && 'font-medium'" x-text="s.name"></span>
                            <span class="font-display text-[22px]" :class="styleId === s.id ? 'text-gold-light' : 'text-bone'" x-text="fmt(priceFor(s)) + ' KM'"></span>
                        </button>
                    </li>
                </template>
            </ul>
        </section>

        {{-- iPhone: no install prompt, so explain it once --}}
        <section x-show="showIosHint" x-cloak class="border border-line bg-panel p-4 text-[14px] leading-relaxed text-stone">
            <p class="text-bone">Dodajte kalkulator na početni ekran</p>
            <p class="mt-1">U Safariju dodirnite <b>Dijeli</b> <span aria-hidden="true">⎋</span>, pa <b>Dodaj na početni ekran</b>. Ikona „Kalkulator“ otvara ovu stranicu odmah, i bez interneta.</p>
            <button type="button" @click="dismissIosHint()" class="mt-2 min-h-10 text-[13px] uppercase tracking-[0.12em] text-gold-light">U redu</button>
        </section>

        <p class="text-center text-[12px] text-ash">
            <a href="{{ route('home') }}" class="inline-flex min-h-10 items-center underline underline-offset-4">Stranica</a>
            ·
            <a href="{{ url('/admin') }}" class="inline-flex min-h-10 items-center underline underline-offset-4">Admin</a>
        </p>
    </main>

    {{-- Send the quote --}}
    <div class="safe-bottom fixed inset-x-0 bottom-0 z-20 mx-auto flex max-w-md gap-2 border-t border-line bg-panel/95 px-4 pt-3 backdrop-blur">
        <button type="button" @click="share()" class="btn-gold min-h-12 flex-1">Pošalji ponudu</button>
        <button type="button" @click="copy()" class="btn-ghost min-h-12 px-4" x-text="copied ? 'Kopirano' : 'Kopiraj'"></button>
    </div>
</div>

<script>
    // Offline support: the calculator keeps working without internet once opened.
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => navigator.serviceWorker.register('/kalkulator-sw.js', { scope: '/kalkulator' }));
    }
</script>
@livewireScripts
</body>
</html>
