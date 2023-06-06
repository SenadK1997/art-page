<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('home');
});
Route::get('/shop', [ProductController::class, 'shop']);

Route::get('/about', function () {
    return view('about');
});

Route::get('/search', [ProductController::class, 'index']);

Route::get('/product/{id}', [ProductController::class, 'show']);

// PROTECTED ROUTES
// Route::group(['middleware' => 'auth'], function() {
    
// });

// CART SECTION
Route::get('shop/cart', [ProductController::class, 'cart'])->name('shop.cart');
Route::post('shop/cart/add', [ProductController::class, 'addToCart'])->name('shop.cart.add');
Route::post('shop/cart/update/{itemId}', [ProductController::class, 'updateCart'])->name('shop.cart.update');
Route::delete('shop/cart/remove/{itemId}', [ProductController::class, 'removeFromCart'])->name('shop.cart.remove');

// Payment
Route::post('shop/cart/checkout/request-payment', [ProductController::class, 'requestPayment'])->name('request.payment');
Route::get('shop/cart/checkout/success', [ProductController::class, 'paymentSuccess'])->name('payment.success');
Route::get('shop/cart/checkout/cancel', [ProductController::class, 'paymentCancel'])->name('payment.cancel');

// ORDERS SECTION FOR USER

Route::get('completed/order/{id}', [ProductController::class, 'completedOrder'])->name('completed.order');
Route::post('completed/order/{id}', [AdminController::class, 'updateStatus'])->name('completed.order.update');
