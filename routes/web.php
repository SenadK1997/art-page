<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
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

// LOGIN
Route::get('/admin/login', [AdminController::class, 'showLoginForm'])
    ->name('admin.login');

Route::post('/admin/login', [AdminController::class, 'login'])
    ->name('admin.login.submit')->middleware('admins');;
// DASHBOARD
Route::middleware(['admins'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])
        ->name('admin.dashboard');
    // ALL PRODUCTS
    Route::get('/admin/product/products', [AdminController::class, 'products'])
        ->name('admin.product.products');
    // CREATE PRODUCT
    Route::get('admin/product/create', [AdminController::class, 'create'])->name('admin.product.create');
    Route::post('admin/product/create', [AdminController::class, 'store'])->name('admin.product.store');
    // EDIT PRODUCT
    Route::get('/admin/product/edit/{id}', [AdminController::class, 'edit'])
        ->name('admin.product.edit');
    Route::put('/admin/product/edit/{id}', [AdminController::class, 'update'])->name('admin.product.update');
    // DELETE PRODUCT 
    Route::delete('/admin/product/delete/{id}', [AdminController::class, 'delete'])
        ->name('admin.product.delete');
    // DELETE TAGS FROM EDIT PRODUCTS
    Route::delete('/admin/update/delete/{id}/{name}', [AdminController::class, 'delete_tags'])
    ->name('admin.update.delete');


// TAGS SECTION

// ALL TAGS
Route::get('admin/tag/tags', [AdminController::class, 'tags'])->name('admin.tag.tags');
// CREATE TAGS
Route::get('admin/tag/create', [AdminController::class, 'make'])->name('admin.tag.create');
Route::post('admin/tag/create', [AdminController::class, 'save'])->name('admin.tag.save');
// EDIT TAGS
Route::get('/admin/tag/edit{id}', [AdminController::class, 'remake'])->name('admin.tag.remake');
Route::put('/admin/tag/edit{id}', [AdminController::class, 'update_tags'])->name('admin.tag.update_tags');
// DELETE TAGS
Route::delete('/admin/tag/trash/{id}', [AdminController::class, 'trash'])
    ->name('admin.tag.trash');
});

// IMAGES SECTION
Route::get('admin/image/images', [AdminController::class, 'images'])->name('admin.image.images');
// CREATE IMAGES
Route::get('admin/image/create', [AdminController::class, 'createImage'])->name('admin.image.create');
Route::post('admin/image/create', [AdminController::class, 'saveImage'])->name('admin.image.save');
// EDIT IMAGES
Route::get('admin/image/edit{id}', [AdminController::class, 'editImage'])->name('admin.image.edit');
Route::put('admin/image/edit{id}', [AdminController::class, 'remakeImage'])->name('admin.image.edit_images');
// DELETE IMAGES
Route::delete('/admin/image/delete/{id}', [AdminController::class, 'deleteImages'])->name('admin.image.delete');

// ORDERS SECTION
Route::get('admin/orders', [AdminController::class, 'orders'])->name('admin.orders');

// CART SECTION
Route::get('shop/cart', [ProductController::class, 'cart'])->name('shop.cart');
Route::post('shop/cart/add', [ProductController::class, 'addToCart'])->name('shop.cart.add');
Route::post('shop/cart/update/{itemId}', [ProductController::class, 'updateCart'])->name('shop.cart.update');
Route::delete('shop/cart/remove/{itemId}', [ProductController::class, 'removeFromCart'])->name('shop.cart.remove');

// Checkout
// Route::get('shop/cart/checkout', [ProductController::class, 'checkout'])->name('shop.cart.checkout');

// Payment
Route::post('shop/cart/checkout/request-payment', [ProductController::class, 'requestPayment'])->name('request.payment');
Route::get('shop/cart/checkout/success', [ProductController::class, 'paymentSuccess'])->name('payment.success');
Route::get('shop/cart/checkout/cancel', [ProductController::class, 'paymentCancel'])->name('payment.cancel');