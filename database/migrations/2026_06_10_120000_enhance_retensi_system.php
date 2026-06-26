<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('no_rm', 20)->unique();
            $table->string('nama_pasien', 100);
            $table->date('tgl_lahir')->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
        });

        Schema::create('retention_policies', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('klasifikasi', 30)->nullable()->unique();
            $table->unsignedTinyInteger('tahun_aktif')->default(2);
            $table->unsignedTinyInteger('tahun_inaktif')->default(3);
            $table->unsignedSmallInteger('alert_hari')->default(30);
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 50);
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
        });

        Schema::create('destruction_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('berkas_id')->constrained('berkas')->cascadeOnDelete();
            $table->string('certificate_number', 50)->unique();
            $table->foreignId('approved_by')->constrained('users');
            $table->timestamp('destroyed_at');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::table('berkas', function (Blueprint $table) {
            $table->foreignId('patient_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('klasifikasi', 30)->default('rawat_jalan')->after('nama_berkas');
            $table->string('lokasi_arsip', 150)->nullable()->after('klasifikasi');
            $table->date('tgl_kunjungan_terakhir')->nullable()->after('status');
            $table->boolean('legal_hold')->default(false)->after('tgl_retensi');
            $table->string('destruction_status', 20)->nullable()->after('legal_hold');
            $table->foreignId('approved_by')->nullable()->after('destruction_status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->softDeletes();
        });

        if (Schema::hasColumn('berkas', 'tgl_retensi')) {
            DB::table('berkas')->whereNotNull('tgl_retensi')->update([
                'tgl_kunjungan_terakhir' => DB::raw('tgl_retensi'),
            ]);
        }

        DB::table('retention_policies')->insert([
            [
                'nama' => 'Default - Rawat Jalan',
                'klasifikasi' => 'rawat_jalan',
                'tahun_aktif' => 2,
                'tahun_inaktif' => 3,
                'alert_hari' => 30,
                'keterangan' => 'Sesuai Permenkes: aktif 2 tahun, inaktif 3 tahun sebelum pemusnahan.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $patients = DB::table('berkas')
            ->select('no_rm', 'nama_pasien', 'tgl_lahir', 'alamat')
            ->groupBy('no_rm', 'nama_pasien', 'tgl_lahir', 'alamat')
            ->get();

        foreach ($patients as $patient) {
            $patientId = DB::table('patients')->insertGetId([
                'no_rm' => $patient->no_rm,
                'nama_pasien' => $patient->nama_pasien,
                'tgl_lahir' => $patient->tgl_lahir,
                'alamat' => $patient->alamat,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('berkas')->where('no_rm', $patient->no_rm)->update(['patient_id' => $patientId]);
        }
    }

    public function down(): void
    {
        Schema::table('berkas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('patient_id');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn([
                'klasifikasi',
                'lokasi_arsip',
                'tgl_kunjungan_terakhir',
                'legal_hold',
                'destruction_status',
                'approved_at',
                'deleted_at',
            ]);
        });

        Schema::dropIfExists('destruction_certificates');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('retention_policies');
        Schema::dropIfExists('patients');
    }
};
