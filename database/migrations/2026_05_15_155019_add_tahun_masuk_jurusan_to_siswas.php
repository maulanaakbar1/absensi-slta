<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            if (! Schema::hasColumn('siswas', 'tahun_masuk')) {
                $table->year('tahun_masuk')
                    ->nullable()
                    ->after('kelas');
            }

            if (! Schema::hasColumn('siswas', 'jurusan')) {
                $table->string('jurusan', 50)
                    ->nullable()
                    ->after('tahun_masuk');
            }

            $table->string('kelas', 20)->nullable()->change();
        });

        DB::statement("
            UPDATE siswas
            SET jurusan = TRIM(
                REPLACE(
                    REPLACE(
                        REPLACE(jurusan, 'VIII ', ''),
                    'VII ', ''),
                'IX ', '')
            )
            WHERE jurusan IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {

            if (Schema::hasColumn('siswas', 'jurusan')) {
                $table->dropColumn('jurusan');
            }

            $table->string('kelas', 20)->nullable(false)->change();
        });
    }
};