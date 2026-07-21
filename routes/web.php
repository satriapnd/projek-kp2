<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TamuController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublikController;
use Illuminate\Support\Facades\Route;

// ===================================
// HALAMAN PUBLIK (Tanpa Login)
// ===================================
// Halaman utama: Riwayat kunjungan hari ini (bisa dilihat semua orang)
Route::get('/', [PublikController::class, 'kunjungan'])->name('home');

// ===================================
// HALAMAN ADMIN (Wajib Login)
// ===================================
Route::middleware(['auth'])->group(function () {

    // Dashboard Admin
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::delete('/tamu/{id}', [DashboardController::class, 'deleteTamu'])->name('tamu.destroy');

    // Registrasi Wajah Tamu (hanya admin)
    Route::get('/tamu/register', [TamuController::class, 'register'])->name('tamu.register');
    Route::post('/tamu/store', [TamuController::class, 'store'])->name('tamu.store');

    // Scanner Check-In (hanya admin)
    Route::get('/tamu/checkin', [TamuController::class, 'checkin'])->name('tamu.checkin');
    Route::post('/tamu/checkin-process', [TamuController::class, 'processCheckin'])->name('tamu.checkin-process');
    Route::post('/tamu/checkin-confirm', [TamuController::class, 'confirmCheckin'])->name('tamu.checkin-confirm');

    // Scanner Check-Out (hanya admin)
    Route::get('/tamu/checkout', [TamuController::class, 'checkout'])->name('tamu.checkout');
    Route::post('/tamu/checkout-process', [TamuController::class, 'processCheckout'])->name('tamu.checkout-process');
    Route::post('/tamu/checkout-confirm', [TamuController::class, 'confirmCheckout'])->name('tamu.checkout-confirm');

    // Legacy scanner (redirect ke checkin)
    Route::get('/tamu/scan', fn() => redirect()->route('tamu.checkin'))->name('tamu.scan');

    // Profil Admin
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
