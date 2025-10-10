<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::view('/', 'welcome');

Route::get('/dashboard', \App\Livewire\Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('reports', function () {
    return view('reports.index');
})->middleware(['auth', 'verified'])->name('reports.index');

Route::get('tables/manage', function () {
    return view('tables.manage');
})->middleware(['auth', 'verified'])->name('tables.manage');

Route::get('users', function () {
    return view('users.index');
})->middleware(['auth', 'verified'])->name('users.list');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::post('logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->middleware(['auth'])->name('logout');

require __DIR__.'/auth.php';
