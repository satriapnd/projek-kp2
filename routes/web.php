<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TamuController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublikController;
use App\Http\Controllers\FaceController;
use Illuminate\Support\Facades\Route;

// ===================================
// HALAMAN PUBLIK (Tanpa Login)
// ===================================
// Halaman utama: Riwayat kunjungan hari ini (bisa dilihat semua orang)
Route::get('/', [PublikController::class, 'kunjungan'])->name('home');

// Registrasi tamu mandiri (publik — bisa dari mana saja tanpa login)
Route::get('/daftar', [TamuController::class, 'register'])->name('tamu.register');
Route::post('/daftar', [TamuController::class, 'store'])->name('tamu.store');

// Login tamu (via face recognition, publik)
Route::get('/masuk', [TamuController::class, 'loginPage'])->name('tamu.login');
Route::post('/masuk', [TamuController::class, 'loginProcess'])->name('tamu.login.process');
Route::post('/keluar-tamu', [TamuController::class, 'logoutTamu'])->name('tamu.logout.tamu');

// Profil tamu (memerlukan session tamu)
Route::get('/profil-saya', [TamuController::class, 'profil'])->name('tamu.profil');

// ===================================
// HALAMAN ADMIN (Wajib Login)
// ===================================
Route::middleware(['auth'])->group(function () {

    // Dashboard Admin
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::delete('/tamu/{id}', [DashboardController::class, 'deleteTamu'])->name('tamu.destroy');

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

    // ===================================
    // FACE API PROXY (Laravel → Flask)
    // Semua request frontend dikirim ke sini, bukan langsung ke Flask.
    // API key Flask aman di sisi server.
    // TODO: Tambahkan rate limiting jika sudah production
    //   Route::middleware('throttle:60,1')->group(function () { ... })
    // ===================================
    Route::get('/face/demo',      [FaceController::class, 'demo'])->name('face.demo');
    Route::post('/face/recognize', [FaceController::class, 'recognize'])->name('face.recognize');
    Route::post('/face/register',  [FaceController::class, 'register'])->name('face.register');
});

require __DIR__.'/auth.php';
