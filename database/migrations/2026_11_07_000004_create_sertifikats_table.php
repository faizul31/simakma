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
        Schema::create('sertifikats', function (Blueprint $table) {
            $table->id();

            // Prinsip Microservices:
            // pendaftaran_id -> ID dari Pendaftaran Service (db_pendaftaran, Port 8003)
            // kegiatan_id    -> ID dari Kegiatan Service (db_kegiatan, Port 8002)
            // mahasiswa_id   -> ID dari Auth Service (db_auth, Port 8001)
            // Tanpa Foreign Key SQL lintas database, melainkan indexed columns.
            $table->unsignedBigInteger('pendaftaran_id')->index()->comment('ID Pendaftaran dari Pendaftaran Service');
            $table->unsignedBigInteger('kegiatan_id')->index()->comment('ID Kegiatan dari Kegiatan Service');
            $table->unsignedBigInteger('mahasiswa_id')->index()->comment('ID Mahasiswa dari Auth Service');

            $table->string('nomor_sertifikat')->unique()->comment('Nomor unik registrasi sertifikat (misal: CERT/2026/XI/001)');
            $table->enum('peran', ['Peserta', 'Panitia', 'Narasumber', 'Moderator'])->default('Peserta');
            $table->date('tanggal_terbit');
            $table->string('file_sertifikat')->nullable()->comment('Path file e-sertifikat PDF/gambar');
            $table->enum('status', ['Valid', 'Revoked'])->default('Valid');
            $table->timestamps();

            // Mencegah duplikasi sertifikat untuk pendaftaran dan peran yang sama
            $table->unique(['pendaftaran_id', 'peran'], 'unique_pendaftaran_peran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sertifikats');
    }
};

