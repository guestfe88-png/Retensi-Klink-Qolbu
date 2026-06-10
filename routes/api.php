<?php

use App\Models\Berkas;
use Illuminate\Support\Facades\Route;

Route::middleware('api.token')->group(function () {
    Route::get('/berkas', function () {
        return Berkas::query()
            ->with('creator:id,nama_lengkap')
            ->latest()
            ->paginate(50);
    });

    Route::get('/berkas/{berkas}', function (Berkas $berkas) {
        return $berkas->load(['creator:id,nama_lengkap', 'patient']);
    });

    Route::get('/stats', function () {
        return [
            'aktif' => Berkas::where('status', 'Aktif')->count(),
            'inaktif' => Berkas::where('status', 'Inaktif')->count(),
            'musnah' => Berkas::where('status', 'Musnah')->count(),
            'pending_destruction' => Berkas::where('destruction_status', 'pending')->count(),
        ];
    });
});
