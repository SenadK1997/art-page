<?php

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
Route::get('/search', [ProductController::class, 'search']);

Route::get('/product/{id}', [ProductController::class, 'show']);