<?php

namespace App\Http\Controllers;

use App\Models\Berkas;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BerkasPdfController extends Controller
{
    public function __invoke(Berkas $berkas): StreamedResponse
    {
        $this->authorize('view', $berkas);

        if (! $berkas->file_pdf) {
            abort(404, 'Berkas PDF tidak ditemukan.');
        }

        $path = 'berkas/'.$berkas->file_pdf;

        if (! Storage::disk('local')->exists($path) && Storage::disk('public')->exists($path)) {
            Storage::disk('local')->put($path, Storage::disk('public')->get($path));
        }

        if (! Storage::disk('local')->exists($path)) {
            abort(404, 'File tidak ditemukan di server.');
        }

        return Storage::disk('local')->download($path, $berkas->file_pdf, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
