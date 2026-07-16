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
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('nisn', 20)->unique()->after('nis');
            $table->text('alamat')->nullable()->after('jenis_kelamin');
            $table->string('tempat_lahir')->nullable()->after('alamat');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->string('nama_ayah')->nullable()->after('tanggal_lahir');
            $table->string('nama_ibu')->nullable()->after('nama_ayah');
            $table->string('no_telp_ayah', 15)->nullable()->after('nama_ibu');
            $table->string('no_telp_ibu', 15)->nullable()->after('no_telp_ayah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn([
                'nisn', 
                'alamat', 
                'tempat_lahir', 
                'tanggal_lahir', 
                'nama_ayah', 
                'nama_ibu', 
                'no_telp_ayah', 
                'no_telp_ibu'
            ]);
        });
    }
};