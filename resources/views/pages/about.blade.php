<x-layouts::site title="O umjetniku" type="profile" description="Amer Fočo — slikar iz Sarajeva. Pejzaži, panorame i apstraktne kompozicije bogate teksturom i svjetlom, originali i slike po narudžbi.">
    <section class="spotlight pt-28 sm:pt-36">
        <div class="mx-auto grid max-w-7xl gap-16 px-6 lg:grid-cols-[1fr_1.2fr] lg:px-10">
            <div class="lg:sticky lg:top-32 lg:h-fit">
                <p class="eyebrow">O umjetniku</p>
                <h1 class="display mt-4 text-[46px] sm:mt-5 sm:text-[80px]">{{ config('shop.artist') }}</h1>
                <p class="mt-6 font-display text-[22px] italic text-stone">Slikar · Sarajevo</p>
                @if ($works->isNotEmpty())
                    <div class="mt-12 grid grid-cols-3 gap-3">
                        @foreach ($works as $w)
                            <a href="{{ route('gallery.show', $w) }}" wire:navigate class="block aspect-[4/5] overflow-hidden bg-wall">
                                <img src="{{ $w->imageUrl() }}" alt="{{ $w->title }}" loading="lazy" class="h-full w-full object-cover opacity-80 transition hover:opacity-100">
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="space-y-6 text-[16px] leading-[1.8] text-stone sm:space-y-7 sm:text-[17px]">
                <p class="font-display text-[26px] leading-snug text-bone sm:text-[30px]">
                    Amer Fočo je talentirani slikar koji je već u svom ranom djetinjstvu pokazao izvanredan interes i vještinu u crtanju.
                </p>
                <p>Svoju umjetničku strast otkrio je dok je još bio vrlo mlad, i od tada nije prestao stvarati. Rođen sa umjetničkim duhom, Amer je uvijek osjećao da je crtanje nešto posebno i jedinstveno. Za njega, crtanje nije samo vještina, već prvenstveno dar od Boga. Vjeruje da je svaki potez kista ili olovke Božji poklon koji mu omogućava da izrazi svoju kreativnost i prenese svoje emocije na platno.</p>
                <p>Amerov stil obuhvata razne teme i motive. Bez obzira radi li se o pejzažima, panorami ili apstraktnim kompozicijama, njegovi radovi odišu dubokom emotivnošću i suptilnošću. Svaka slika nosi ličnu priču i odražava Amerovu unutarnju viziju svijeta.</p>
                <p>Kroz godine neprekidnog usavršavanja svog zanata, razvio je vlastiti prepoznatljivi stil i tehniku. Njegove slike su bogate teksturama, slojevima boja i igrom svjetlosti, stvarajući živopisne i dinamične kompozicije.</p>
                <p>Poznat je i po predanom pristupu radu. Provodi sate i sate u svom ateljeu, posvećen svakom detalju. Istrajnost i strpljenje omogućavaju mu visoku razinu preciznosti u svakoj slici.</p>
                <p>Njegove slike ostavljaju dubok dojam na promatrače, prenoseći emocije i priče koje nadilaze riječi — a svaka nova slika predstavlja novo poglavlje njegovog umjetničkog putovanja.</p>
                <div class="grid gap-3 pt-6 sm:flex sm:flex-wrap sm:gap-4">
                    <a href="{{ route('gallery') }}" wire:navigate class="btn-gold">Pogledaj djela</a>
                    <a href="{{ route('commission') }}" class="btn-ghost">Naruči sliku po mjeri</a>
                </div>
                <div class="border-t border-line pt-8">
                    <p class="text-[14px] text-stone">Pratite nastajanje novih slika:</p>
                    <x-social-links class="mt-4" />
                </div>
            </div>
        </div>
    </section>
</x-layouts::site>
