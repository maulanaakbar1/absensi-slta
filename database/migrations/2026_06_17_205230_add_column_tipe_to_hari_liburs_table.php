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
        Schema::table('hari_liburs', function (Blueprint $table) {
            $table->date('tanggal')->nullable()->change();
            $table->string('hari')->nullable()->after('tanggal');
            // Pastikan kolom 'ekstrakurikuler_id' memang sudah ada di tabel hari_liburs sebelumnya
            $table->enum('tipe', ['dadakan', 'rutin'])->default('rutin')->after('ekstrakurikuler_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hari_liburs', function (Blueprint $table) {
            // PERBAIKAN: Masukkan 'tanggal' agar semua kolom baru bersih saat di-rollback
            $table->date('tanggal')->nullable(false)->change();
            $table->dropColumn(['tanggal', 'hari', 'tipe']);
        });
    }
};