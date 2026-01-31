<?php

use Illuminate\Support\Facades\Route;
use App\Models\Room;
use App\Http\Controllers\RoomsController;
use App\Http\Controllers\StaffManagementController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;

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

Route::get('/rooms', [App\Http\Controllers\RoomsController::class, 'rooms'])->name('rooms');
Route::get('/booking', [App\Http\Controllers\RoomsController::class, 'booking'])->name('booking');
Route::get('/manage_room', [App\Http\Controllers\RoomsController::class, 'manage_room'])->name('manage_room');
Route::get('/history', [App\Http\Controllers\RoomsController::class, 'history'])->name('history');
Route::get('/profile', [App\Http\Controllers\RoomsController::class, 'profile'])->name('profile');
Route::get('/manage_staff', [App\Http\Controllers\RoomsController::class, 'manage_staff'])->name('manage_staff');
Route::get('/create', [App\Http\Controllers\RoomsController::class, 'create'])->name('create');
Route::post('/insert', [App\Http\Controllers\RoomsController::class, 'insert']);
Route::get('/change/{id}', [App\Http\Controllers\RoomsController::class, 'change'])->name('change');
Route::get('/edit/{id}', [App\Http\Controllers\RoomsController::class, 'edit'])->name('edit');
Route::get('/delete/{id}', [App\Http\Controllers\RoomsController::class, 'delete'])->name('delete');
Route::post('/update/{id}', [App\Http\Controllers\RoomsController::class, 'update'])->name('update');


Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('admin/staff/index', [StaffManagementController::class, 'index'])->name('admin_staff_index');
    Route::get('admin/staff/create', [StaffManagementController::class, 'create'])->name('admin_staff_create');
    Route::post('admin/staff/store', [StaffManagementController::class, 'store'])->name('admin_staff_store');
    Route::get('/edit_staff/{id}', [StaffManagementController::class, 'edit_staff'])->name('admin_staff_edit');
    Route::post('/update_staff/{id}', [StaffManagementController::class, 'update_staff'])->name('admin_staff_update');
    Route::delete('/delete_staff/{id}', [StaffManagementController::class, 'delete_staff'])->name('admin_staff_delete');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);

Route::get('/register', function () {
    return view('auth.register');
})->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
