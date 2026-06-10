<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('berkas', function (Blueprint $table) {
            $table->id();
            $table->string('no_rm', 20);
            $table->string('nama_pasien', 100);
            $table->date('tgl_lahir')->nullable();
            $table->text('alamat')->nullable();
            $table->string('nama_berkas', 100)->nullable();
            $table->string('file_pdf', 255)->nullable();
            $table->enum('status', ['Aktif', 'Inaktif', 'Musnah'])->default('Aktif');
            $table->date('tgl_retensi')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berkas');
    }
};
