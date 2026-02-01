<?php

use Illuminate\Support\Facades\Route;
use App\Models\Room;
use App\Http\Controllers\RoomsController;
use App\Http\Controllers\StaffManagementController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookingController;

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


Route::get('/rooms', [RoomsController::class, 'rooms'])->name('rooms');
Route::get('/booking', [RoomsController::class, 'booking'])->name('booking');
Route::get('/profile', [RoomsController::class, 'profile'])->name('profile');




Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('admin/staff/index', [StaffManagementController::class, 'index'])->name('admin_staff_index');
    Route::get('admin/staff/create', [StaffManagementController::class, 'create'])->name('admin_staff_create');
    Route::post('admin/staff/store', [StaffManagementController::class, 'store'])->name('admin_staff_store');
    Route::get('/edit_staff/{id}', [StaffManagementController::class, 'edit_staff'])->name('admin_staff_edit');
    Route::post('/update_staff/{id}', [StaffManagementController::class, 'update_staff'])->name('admin_staff_update');
    Route::delete('/delete_staff/{id}', [StaffManagementController::class, 'delete_staff'])->name('admin_staff_delete');

    Route::get('/manage_staff', [RoomsController::class, 'manage_staff'])->name('manage_staff');
});
Route::middleware(['auth', 'role:staff,admin'])->group(function () {
    Route::get('/manage_room', [RoomsController::class, 'manage_room'])->name('manage_room');
    Route::get('/create', [RoomsController::class, 'create'])->name('create');
    Route::post('/insert', [RoomsController::class, 'insert']);
    Route::get('/change/{id}', [RoomsController::class, 'change'])->name('change');
    Route::get('/edit/{id}', [RoomsController::class, 'edit'])->name('edit');
    Route::get('/delete/{id}', [RoomsController::class, 'delete'])->name('delete');
    Route::post('/update/{id}', [RoomsController::class, 'update'])->name('update');
    Route::get('/admin/bookings', [BookingController::class, 'manage'])->name('admin_booking_manage');
    Route::post('/admin/bookings/update/{id}', [BookingController::class, 'updateStatus'])->name('admin_booking_update');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/booking', [BookingController::class, 'index'])->name('booking');
    Route::post('/booking', [BookingController::class, 'store'])->name('booking_store');
    Route::get('/booking/history', [BookingController::class, 'history'])->name('booking_history');
    Route::get('/room-details/{id}', function ($id) {
        $room = \App\Models\Room::find($id);
        return response()->json($room);
    });
    Route::patch('/booking/cancel/{id}', [App\Http\Controllers\BookingController::class, 'cancel'])->name('booking_cancel');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');



Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
