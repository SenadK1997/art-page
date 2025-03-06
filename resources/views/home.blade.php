@extends('layouts.master')

@section("title")

Homepage || Foco-art

@endsection

@section('content')

<section class="flex flex-col max-w-screen-xl mx-auto items-center mt-[120px] max-md:mt-18 mb-[64px] max-md:mb-[0px] max-md:w-full">
    <div class="flex max-w-screen-xl mx-auto justify-between w-full gap-x-3 p-2 max-md:flex-col max-md:items-center max-md:w-full max-md:p-[40px]">
        <div class="flex flex-col w-full h-[450px] p-5 bg-[url('https://i.ibb.co/7ktbnPC/focoslika1.jpg')] bg-cover max-md:bg-contain max-md:bg-no-repeat max-md:min-h-full hover:animate-pulse max-md:mb-[-100px]">
            <p class="text-white text-xl">Sarajevska Katedrala</p>
            <a href="/shop" class="text-white">Kupi sada</a>
        </div>
        <div class="flex flex-col w-full h-[450px] p-5 bg-[url('https://i.ibb.co/TRLVbgb/focoslika2.jpg')] bg-cover max-md:bg-contain max-md:bg-no-repeat max-md:min-h-full hover:animate-pulse max-md:mb-[-150px]">
            <p class="text-white text-xl">Sebilj</p>
            <a href="/shop" class="text-white">Kupi sada</a>
        </div>
    </div>
    <div class="flex max-w-screen-xl mx-auto items-center mt-[60px] max-md:mt-0 max-md:p-5">
        <p class="text-center p-7 text-lg">Dobrodošli u moju online umjetničku galeriju! Kao iskusan slikar, uzbuđen sam što mogu s vama podijeliti svoju jedinstvenu perspektivu i kreativnost. Moja strast prema umjetnosti dovela me je do istraživanja raznih stilova i tehnika, od apstraktnog ekspresionizma do realizma, i svega između. Svaka moja slika je rad ljubavi, pedantno izrađen da izazove emocije i uhvati ljepotu svijeta oko nas. Bilo da tražite originalni komad za svoj dom ili ured, ili jednostavno želite da se divite ljepoti umjetnosti, nadam se da ćete ovdje pronaći nešto što će vas inspirirati. Hvala na posjeti!</p>
    </div>
    <div class="flex mb-10 items-center gap-x-3">
        <a href="/shop" class="ease-in duration-300 min-w-[120px] py-2 text-center border-solid border-2 border-stone-900 text-white bg-stone-900 hover:text-stone-900 hover:bg-white">Naruci sliku</a>
        <a href="/shop" class="ease-in duration-300 min-w-[120px] py-2 text-center border-solid border-2 border-stone-900 text-white bg-stone-900 hover:text-stone-900 hover:bg-white">Kupi sliku</a>
    </div>
    {{-- <div class="flex max-w-screen-xl mx-auto justify-between w-full gap-x-3 p-2 max-md:flex-col max-md:items-center max-md:w-full">
        <div class="flex flex-col w-full h-[450px] p-5 bg-[url('https://i.ibb.co/7ktbnPC/focoslika1.jpg')] bg-cover max-md:bg-contain max-md:bg-no-repeat max-md:min-h-full hover:animate-pulse">
            <p class="text-white text-xl">Umjetnicko djelo</p>
            <p class="text-white">Kupi sada</p>
        </div>
        <div class="flex flex-col w-full h-[450px] p-5 bg-[url('https://i.ibb.co/TRLVbgb/focoslika2.jpg')] bg-cover max-md:bg-contain max-md:bg-no-repeat max-md:min-h-full hover:animate-pulse">
            <p class="text-white text-xl">Umjetnicko djelo broj 2</p>
            <p class="text-white">Saznaj vise</p>
        </div>
    </div> --}}
    <div class="flex w-full gap-x-[50px] p-2 max-md:flex-col max-md:mx-auto p-5 max-md:gap-y-4 max-md:w-full max-md:items-center">
        <div class="bg-white rounded-lg shadow-md p-6 w-[400px] h-[431px]">
            <h1 class="text-2xl font-bold mb-4 text-blue-500">Izračunaj cijenu:</h1>
            <p class="mb-4 text-gray-400">Cijena se računa po cm2</p>
            <div class="mb-4">
              <label for="multiplier" class="block font-semibold mb-2">Vrsta slike:</label>
              <select id="multiplier" class="w-full p-2 border border-gray-300 rounded-md">
                <option value="5">Apstrakcija</option>
                <option value="10">Realizam</option>
                <option value="15">Panorama</option>
              </select>
            </div>
            <div class="mb-4">
              <label for="width" class="block font-semibold mb-2">Širina (cm):</label>
              <input type="number" id="width" placeholder="Širina" class="w-full p-2 border border-gray-300 rounded-md">
            </div>
            <div class="mb-4">
              <label for="height" class="block font-semibold mb-2">Dužina (cm):</label>
              <input type="number" id="height" placeholder="Dužina" class="w-full p-2 border border-gray-300 rounded-md">
            </div>
            <p id="price" class="font-semibold text-lg text-green-500"></p>
          </div>
          <div class="flex flex-wrap w-full gap-y-5 max-md:gap-y-2 max-md:flex-col max-md:mx-auto max-md:gap-y-3 max-md:p-5">
            <div class="flex gap-x-5 w-full max-md:gap-x-1 max-md:flex-col max-md:gap-y-2">
                <div class="flex flex-col p-5 bg-[url('https://i.ibb.co/yyMrdV8/apstrakcija.png')] bg-cover bg-center max-md:bg-contain max-md:bg-no-repeat max-md:min-h-full hover:animate-pulse w-full md:w-1/2 max-md:bg-cover max-md:h-[220px]">
                    <p class="text-white text-xl">Apstrakcija</p>
                    <p class="text-white">Primjer slika</p>
                </div>
                <div class="flex flex-col p-5 bg-[url('https://i.ibb.co/kxx0bnh/realizam.png')] bg-cover bg-center max-md:bg-contain max-md:bg-no-repeat max-md:min-h-full hover:animate-pulse w-full md:w-1/2 max-md:bg-cover max-md:h-[220px]">
                    <p class="text-white text-xl">Realizam</p>
                    <p class="text-white">Primjer slika</p>
                </div>
            </div>
            <div class="flex flex-col p-5 bg-[url('https://i.ibb.co/yqhrkn2/panorama.png')] bg-cover bg-center max-md:bg-contain max-md:bg-no-repeat max-md:min-h-full hover:animate-pulse w-full max-md:bg-cover max-md:h-[220px]">
              <p class="text-white text-xl">Panorama</p>
              <p class="text-white">Primjer slika</p>
            </div>
          </div>
    </div>
</section>
@endsection
@push('script')
    <script>
        $(document).ready(function() {
            $('#width, #height, #multiplier').on('change', function() {
                var width = $('#width').val();
                var height = $('#height').val();
                var multiplier = $('#multiplier').val();
                
                if (width && height) {
                var price = Math.round(width * height / 100 * multiplier);
                $('#price').text('Cijena: ' + price + ' ' + 'BAM');
                } else {
                $('#price').text('');
                }
            });
        });
    </script>
@endpush