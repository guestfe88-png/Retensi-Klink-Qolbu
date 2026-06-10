<?php

namespace App\Http\Controllers;

use App\Models\Berkas;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BerkasExportController extends Controller
{
    public function csv(): StreamedResponse
    {
        $this->authorize('export', Berkas::class);

        $filename = 'retensi-berkas-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'No RM', 'Nama Pasien', 'Klasifikasi', 'Nama Berkas', 'Status',
                'Kunjungan Terakhir', 'Jatuh Tempo', 'Lokasi Arsip', 'Legal Hold',
                'Keterangan', 'Petugas', 'Dibuat',
            ]);

            Berkas::with('creator')->orderBy('id')->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->no_rm,
                        $row->nama_pasien,
                        $row->klasifikasi_label,
                        $row->nama_berkas,
                        $row->status,
                        $row->tgl_kunjungan_terakhir?->format('Y-m-d'),
                        $row->tgl_retensi?->format('Y-m-d'),
                        $row->lokasi_arsip,
                        $row->legal_hold ? 'Ya' : 'Tidak',
                        $row->keterangan,
                        $row->creator?->nama_lengkap,
                        $row->created_at?->format('Y-m-d H:i'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
