<x-layouts::site title="Slika po mjeri" description="Naručite sliku u veličini koju želite ili po svojoj fotografiji. Cijena se računa odmah, plaćanje pouzećem.">
    <section class="theme-dark spotlight pt-28 pb-12 sm:pt-36 sm:pb-20">
        <div class="mx-auto max-w-7xl px-6 lg:px-10">
            <p class="eyebrow">Slika po mjeri</p>
            <h1 class="display mt-4 max-w-3xl text-[42px] sm:mt-5 sm:text-[72px]">Vaša ideja, <em class="text-gold-light">naslikana rukom.</em></h1>
            <p class="mt-6 max-w-xl text-[16px] leading-relaxed text-stone">
                Izaberite stil i dimenzije — ili pošaljite svoju fotografiju: portret, kuću, grad, ljubimca. Cijenu vidite odmah, a plaćate tek kada slika stigne.
            </p>
        </div>
    </section>

    <section class="pt-10 sm:pt-16">
        <div class="mx-auto max-w-7xl px-6 lg:px-10">
            <livewire:commission-calculator />
        </div>
    </section>

    <section class="pt-20 sm:pt-28">
        <div class="mx-auto grid max-w-7xl gap-16 px-6 lg:grid-cols-[1fr_1.4fr] lg:px-10">
            <div>
                <p class="eyebrow">Kako ide</p>
                <h2 class="display mt-4 text-[36px] sm:mt-5 sm:text-[44px]">Od fotografije do platna</h2>
            </div>
            <ol class="grid gap-12 sm:grid-cols-3">
                @foreach ([
                    ['Pošaljite', 'Stil, veličinu i — ako želite — fotografiju koju treba naslikati.'],
                    ['Dogovorimo', 'Umjetnik pregleda fotografiju i javi vam se da potvrdi detalje i rok.'],
                    ['Primite', 'Gotova slika stiže zapakovana na vašu adresu. Plaćate kuriru.'],
                ] as $i => [$t, $d])
                    <li>
                        <p class="font-display text-[56px] leading-none text-gold">0{{ $i + 1 }}</p>
                        <p class="mt-5 font-display text-[24px]">{{ $t }}</p>
                        <p class="mt-3 text-[14px] leading-relaxed text-stone">{{ $d }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="pt-20 sm:pt-28">
        <div class="mx-auto max-w-4xl px-6 lg:px-10">
            <p class="eyebrow">Pitanja</p>
            <h2 class="display mt-4 text-[36px] sm:mt-5 sm:text-[44px]">Često pitate</h2>
            <div class="mt-10 divide-y divide-line border-y border-line">
                @php($faq = [
                    ['Koje veličine su moguće?', $styles->map(fn ($s) => "{$s->name}: od {$s->min_width} × {$s->min_height} do {$s->max_width} × {$s->max_height} cm")->join(' · ')],
                    ['Kako se računa cijena?', 'Širina × visina ÷ 100 × cijena stila. Npr. Realizam 50 × 70 cm = 3.500 ÷ 100 × 10 = 350 KM.'],
                    ['Koliko traje izrada?', ucfirst($settings->get('made_to_order_time')).'. Tačan rok potvrđujemo telefonom.'],
                    ['Kako plaćam?', 'Pouzećem — iznos plaćate kuriru kada preuzmete sliku. Ne plaćate ništa unaprijed.'],
                    ['Šta ako moja fotografija nije dobra za sliku?', 'Umjetnik vam se javi prije izrade i predloži drugu fotografiju ili kadar. Ništa se ne slika bez vašeg dogovora.'],
                ])
                @foreach ($faq as [$q, $a])
                    <details class="group py-6">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-6 font-display text-[22px]">
                            {{ $q }}
                            <span class="text-gold transition group-open:rotate-45" aria-hidden="true">+</span>
                        </summary>
                        <p class="mt-4 text-[15px] leading-relaxed text-stone">{{ $a }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts::site>
