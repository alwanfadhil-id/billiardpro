<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Halaman publik
Route::view('/', 'welcome');

// Autentikasi (diimpor dari auth.php)
require __DIR__.'/auth.php';

// Rute yang memerlukan autentikasi & verifikasi email
Route::middleware(['auth', 'verified'])->group(function () {

    // 🔹 DASHBOARD UTAMA
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // 🔹 TRANSAKSI
    Route::get('/transactions/start/{table}', \App\Livewire\Transactions\StartSession::class)
        ->name('transactions.start');
        
    Route::get('/transactions/add-items/{transaction}', \App\Livewire\Transactions\AddItems::class)
        ->name('transactions.add-items');
        
    Route::get('/transactions/payment/{transaction}', \App\Livewire\Transactions\PaymentProcess::class)
        ->name('transactions.payment');

    // 🔹 LAPORAN HARIAN
    Route::get('/reports', \App\Livewire\Reports\DailyReport::class)
        ->name('report.daily');

    // 🔹 KELOLA MEJA (admin only — proteksi role bisa ditambah di Livewire)
    Route::get('/tables/manage', \App\Livewire\Tables\TableForm::class)
        ->name('tables.manage');

    // 🔹 KELOLA USER (admin only)
    Route::get('/users', \App\Livewire\Users\UserList::class)
        ->name('users.list');

    // 🔹 KELOLA PRODUK
    Route::get('/products', \App\Livewire\Products\ProductList::class)
        ->name('products.list');

    // 🔹 KELOLA PENGATURAN
    Route::get('/settings', \App\Livewire\Settings\SettingsForm::class)
        ->name('settings.index');

    // 🔹 PROFILE
    Route::view('/profile', 'profile')->name('profile');
});

// Logout (hanya butuh auth)
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->middleware('auth')->name('logout');