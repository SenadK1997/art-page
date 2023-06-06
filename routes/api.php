<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// LOGIN
Route::group(['middleware' => 'api', 'prefix'=>'auth'], function() {
    Route::get('/register', [AdminController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AdminController::class, 'register'])->name('register.submit');
    Route::get('/login', [AdminController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminController::class, 'login'])->name('login.submit');
    
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout.submit');

    Route::get('/profile', [AdminController::class, 'profile'])->name('profile');

    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
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
    // ORDERS SECTION FOR ADMIN
    Route::get('admin/orders', [AdminController::class, 'orders'])->name('admin.orders');
});
