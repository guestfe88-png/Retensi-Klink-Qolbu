<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('berkas')
            ->where('klasifikasi', '!=', 'rawat_jalan')
            ->update(['klasifikasi' => 'rawat_jalan']);

        DB::table('retention_policies')
            ->where(function ($query) {
                $query->whereNull('klasifikasi')
                    ->orWhere('klasifikasi', '!=', 'rawat_jalan');
            })
            ->delete();

        $exists = DB::table('retention_policies')
            ->where('klasifikasi', 'rawat_jalan')
            ->exists();

        if (! $exists) {
            DB::table('retention_policies')->insert([
                'nama' => 'Default - Rawat Jalan',
                'klasifikasi' => 'rawat_jalan',
                'tahun_aktif' => 2,
                'tahun_inaktif' => 3,
                'alert_hari' => 30,
                'keterangan' => 'Sesuai Permenkes: aktif 2 tahun, inaktif 3 tahun sebelum pemusnahan.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Tidak mengembalikan klasifikasi/unit yang sudah dihapus.
    }
};
