<?php

use Illuminate\Support\Facades\Route;
use App\Models\Room;
use App\Http\Controllers\RoomsController;

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
    return view('welcome');
});

Route::get('/rooms', [App\Http\Controllers\RoomsController::class, 'index'])->name('rooms');
Route::get('/create', [App\Http\Controllers\RoomsController::class, 'create'])->name('create');
Route::post('/insert', [App\Http\Controllers\RoomsController::class, 'insert']);
Route::get('/change/{id}', [App\Http\Controllers\RoomsController::class, 'change'])->name('change');
Route::get('/edit/{id}', [App\Http\Controllers\RoomsController::class, 'edit'])->name('edit');
Route::get('/delete/{id}', [App\Http\Controllers\RoomsController::class, 'delete'])->name('delete');
Route::post('/update/{id}', [App\Http\Controllers\RoomsController::class, 'update'])->name('update');


Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
