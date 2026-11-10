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
        Schema::create('prestasis', function (Blueprint $table) {
            $table->id();

            // Prinsip Microservices:
            // mahasiswa_id        -> merujuk ke tabel users di Auth Service (db_auth, Port 8001)
            // dosen_pembimbing_id -> merujuk ke tabel users di Auth Service (db_auth, Port 8001, nullable)
            // Tanpa Foreign Key SQL lintas database, melainkan indexed columns.
            $table->unsignedBigInteger('mahasiswa_id')->index()->comment('ID Mahasiswa dari Auth Service');
            $table->unsignedBigInteger('dosen_pembimbing_id')->nullable()->index()->comment('ID Dosen Pembimbing dari Auth Service');

            $table->string('judul_prestasi');
            $table->enum('tingkat', ['Lokal', 'Provinsi', 'Nasional', 'Internasional']);
            $table->string('file_sertifikat')->comment('Path file sertifikat yang disimpan di storage');

            $table->enum('status_validasi_dosen', ['Menunggu', 'Disetujui', 'Ditolak'])->default('Menunggu');
            $table->enum('status_validasi_akhir', ['Menunggu', 'Disetujui', 'Ditolak'])->default('Menunggu');

            $table->text('catatan')->nullable()->comment('Alasan penolakan atau catatan dari dosen / admin kemahasiswaan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestasis');
    }
};

