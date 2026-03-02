<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MonitorDashboardController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\ClientDetailController;
// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified', 'role:ops'])->group(function () {
    Route::get('/dashboard', MonitorDashboardController::class)
        ->name('dashboard');
    Route::get('/monitor/client/{client}', ClientDetailController::class)
        ->name('monitor.client.show');
    Route::get('/monitor/vendor/{vendor}', VendorController::class)
        ->name('monitor.vendor.show');
});
  





Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



 

require __DIR__.'/auth.php';
