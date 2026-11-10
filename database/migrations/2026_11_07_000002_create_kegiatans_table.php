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
        Schema::create('kegiatans', function (Blueprint $table) {
            $table->id();

            // Prinsip Microservices:
            // pembuat_id menyimpan user_id dari Auth Service (db_auth).
            // Tidak ada constraint foreign key SQL lintas database, cukup index untuk optimasi query.
            $table->unsignedBigInteger('pembuat_id')->index()->comment('ID Pembuat dari Auth Service (db_auth)');

            // Relasi lokal dalam database db_kegiatan
            $table->foreignId('kategori_id')->constrained('kategori_kegiatans')->onDelete('cascade');

            $table->string('judul_kegiatan');
            $table->text('deskripsi');
            $table->dateTime('batas_pendaftaran');
            $table->unsignedInteger('kuota_maksimal');
            $table->unsignedInteger('kuota_terisi')->default(0);
            $table->enum('status_publikasi', ['Draft', 'Published'])->default('Draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatans');
    }
};
