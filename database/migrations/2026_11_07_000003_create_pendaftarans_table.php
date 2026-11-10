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
        Schema::create('pendaftarans', function (Blueprint $table) {
            $table->id();

            // Sesuai Prinsip Microservices:
            // kegiatan_id -> ID kegiatan di Kegiatan Service (db_kegiatan, Port 8002)
            // mahasiswa_id -> ID mahasiswa di Auth Service (db_auth, Port 8001)
            // dosen_wali_id -> ID dosen wali di Auth Service (db_auth, Port 8001, nullable)
            // Tanpa foreign key SQL lintas database, melainkan plain indexed columns.
            $table->unsignedBigInteger('kegiatan_id')->index()->comment('ID Kegiatan dari Kegiatan Service');
            $table->unsignedBigInteger('mahasiswa_id')->index()->comment('ID Mahasiswa dari Auth Service');
            $table->unsignedBigInteger('dosen_wali_id')->nullable()->index()->comment('ID Dosen Wali dari Auth Service');

            $table->string('file_syarat')->nullable()->comment('Path file syarat yang diunggah ke storage');
            $table->enum('status_rekomendasi_dosen', ['Menunggu', 'Direkomendasikan', 'Ditolak'])->default('Menunggu');
            $table->enum('status_pendaftaran', ['Menunggu', 'Lolos Berkas', 'Diterima', 'Revisi', 'Ditolak'])->default('Menunggu');
            $table->text('catatan_verifikator')->nullable();
            $table->timestamps();

            // Mencegah mahasiswa mendaftar dua kali pada kegiatan yang sama
            $table->unique(['mahasiswa_id', 'kegiatan_id'], 'unique_mahasiswa_kegiatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftarans');
    }
};
