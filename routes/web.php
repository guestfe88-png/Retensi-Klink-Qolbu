<?php

use App\Http\Controllers\BerkasExportController;
use App\Http\Controllers\BerkasPdfController;
use App\Http\Controllers\DestructionCertificateController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/favicon.ico', fn () => response()->file(public_path('favicon.png'), ['Content-Type' => 'image/png']));

Route::redirect('/', '/home');

Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'pages::login')->name('login');
    Route::livewire('/forgot-password', 'pages::forgot-password')->name('password.request');
    Route::livewire('/reset-password/{token}', 'pages::reset-password')->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::livewire('/home', 'pages::home')->name('home');
    Route::livewire('/dashboard', 'pages::dashboard')->name('dashboard');
    Route::livewire('/alerts', 'pages::alerts')->name('alerts');
    Route::livewire('/berkas/create', 'pages::berkas-form')->name('berkas.create');
    Route::livewire('/berkas/{id}/edit', 'pages::berkas-form')->name('berkas.edit');
    Route::livewire('/berkas/{id}', 'pages::berkas-detail')->name('berkas.show');
    Route::livewire('/change-password', 'pages::change-password')->name('change-password');

    Route::get('/berkas/{berkas}/pdf', BerkasPdfController::class)->name('berkas.pdf');
    Route::get('/export/berkas.csv', [BerkasExportController::class, 'csv'])->name('berkas.export');
    Route::get('/certificates/{certificate}', [DestructionCertificateController::class, 'show'])->name('certificates.show');

    Route::middleware('admin')->group(function () {
        Route::livewire('/users', 'pages::users')->name('users.index');
        Route::livewire('/retention-settings', 'pages::retention-settings')->name('retention.settings');
        Route::livewire('/audit-logs', 'pages::audit-logs')->name('audit-logs');
        Route::livewire('/destruction-requests', 'pages::destruction-requests')->name('destruction.requests');
    });

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});
