<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\SeoController;
use App\Livewire\CartPage;
use App\Livewire\Checkout;
use App\Livewire\Gallery;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/slika-po-mjeri', [PageController::class, 'commission'])->name('commission');
Route::get('/o-umjetniku', [PageController::class, 'about'])->name('about');

Route::get('/galerija', Gallery::class)->name('gallery');
Route::get('/galerija/{product:slug}', [PageController::class, 'product'])->name('gallery.show');

Route::get('/korpa', CartPage::class)->name('cart');
Route::get('/korpa/fotografija/{key}', [PhotoController::class, 'cart'])->name('cart.photo');
Route::get('/narudzba', Checkout::class)->name('checkout');
Route::get('/narudzba/{order:number}/hvala', [PageController::class, 'thanks'])
    ->middleware('signed')
    ->name('order.thanks');

// Customer photos are private; only a signed-in admin can open them.
Route::get('/admin/fotografija/{item}', [PhotoController::class, 'admin'])
    ->middleware(['auth'])
    ->name('admin.photo');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

// Old URLs from the first version of the site.
Route::redirect('/shop', '/galerija', 301);
Route::redirect('/about', '/o-umjetniku', 301);
Route::redirect('/shop/cart', '/korpa', 301);
