@extends('layouts.master')

@section("title")

Homepage || Foco-art

@endsection

@section('content')

<section class="flex flex-col max-w-screen-xl mx-auto items-center mt-[120px] max-md:mt-18 mb-[64px]">
    <div class="flex max-w-screen-xl mx-auto justify-between w-full gap-x-3 p-2 max-md:flex-col max-md:items-center max-md:w-full">
        <div class="flex flex-col w-full h-[450px] p-5 bg-[url('https://i.ibb.co/7ktbnPC/focoslika1.jpg')] bg-cover max-md:bg-contain max-md:bg-no-repeat max-md:min-h-full hover:animate-pulse">
            <p class="text-white text-xl">Umjetnicko djelo</p>
            <p class="text-white">Kupi sada</p>
        </div>
        <div class="flex flex-col w-full h-[450px] p-5 bg-[url('https://i.ibb.co/TRLVbgb/focoslika2.jpg')] bg-cover max-md:bg-contain max-md:bg-no-repeat max-md:min-h-full hover:animate-pulse">
            <p class="text-white text-xl">Umjetnicko djelo broj 2</p>
            <p class="text-white">Saznaj vise</p>
        </div>
    </div>
    <div class="flex max-w-screen-xl mx-auto items-center mt-[60px] max-md:mt-0">
        <p class="text-center p-7 text-lg">Dobrodošli u moju online umjetničku galeriju! Kao iskusan slikar, uzbuđen sam što mogu s vama podijeliti svoju jedinstvenu perspektivu i kreativnost. Moja strast prema umjetnosti dovela me je do istraživanja raznih stilova i tehnika, od apstraktnog ekspresionizma do realizma, i svega između. Svaka moja slika je rad ljubavi, pedantno izrađen da izazove emocije i uhvati ljepotu svijeta oko nas. Bilo da tražite originalni komad za svoj dom ili ured, ili jednostavno želite da se divite ljepoti umjetnosti, nadam se da ćete ovdje pronaći nešto što će vas inspirirati. Hvala na posjeti!</p>
    </div>
    <div class="flex mb-10 items-center gap-x-3">
        <a href="/shop" class="ease-in duration-300 min-w-[120px] py-2 text-center border-solid border-2 border-stone-900 text-white bg-stone-900 hover:text-stone-900 hover:bg-white">Naruci sliku</a>
        <a href="/shop" class="ease-in duration-300 min-w-[120px] py-2 text-center border-solid border-2 border-stone-900 text-white bg-stone-900 hover:text-stone-900 hover:bg-white">Kupi sliku</a>
    </div>
    <div class="flex max-w-screen-xl mx-auto justify-between w-full gap-x-3 p-2 max-md:flex-col max-md:items-center max-md:w-full">
        <div class="flex flex-col w-full h-[450px] p-5 bg-[url('https://i.ibb.co/7ktbnPC/focoslika1.jpg')] bg-cover max-md:bg-contain max-md:bg-no-repeat max-md:min-h-full hover:animate-pulse">
            <p class="text-white text-xl">Umjetnicko djelo</p>
            <p class="text-white">Kupi sada</p>
        </div>
        <div class="flex flex-col w-full h-[450px] p-5 bg-[url('https://i.ibb.co/TRLVbgb/focoslika2.jpg')] bg-cover max-md:bg-contain max-md:bg-no-repeat max-md:min-h-full hover:animate-pulse">
            <p class="text-white text-xl">Umjetnicko djelo broj 2</p>
            <p class="text-white">Saznaj vise</p>
        </div>
    </div>
</section>

@endsection