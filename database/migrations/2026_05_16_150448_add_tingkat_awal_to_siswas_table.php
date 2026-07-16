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

            if (!Schema::hasColumn('siswas', 'tingkat_awal')) {
                $table->integer('tingkat_awal')
                    ->default(7)
                    ->after('tahun_masuk');
            }
        });

        DB::statement("
            UPDATE siswas
            SET tingkat_awal = CASE
                WHEN jurusan LIKE 'IX %' THEN 9
                WHEN jurusan LIKE 'VIII %' THEN 8
                WHEN jurusan LIKE 'VII %' THEN 7
                ELSE 7
            END
            WHERE tingkat_awal IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {

            if (Schema::hasColumn('siswas', 'tingkat_awal')) {
                $table->dropColumn('tingkat_awal');
            }
        });
    }
};