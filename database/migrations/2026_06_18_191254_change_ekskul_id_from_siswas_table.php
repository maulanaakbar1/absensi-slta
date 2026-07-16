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
            $table->dropForeign(['ekstrakurikuler_id']);
            $table->dropColumn('ekstrakurikuler_id');
        });

        Schema::table('siswas', function (Blueprint $table) {
            $table->json('ekstrakurikuler_id')->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn('ekstrakurikuler_id');
        });

        Schema::table('siswas', function (Blueprint $table) {
            $table->unsignedBigInteger('ekstrakurikuler_id')->nullable();
            $table->foreign('ekstrakurikuler_id')->references('id')->on('ekstrakurikulers')->nullOnDelete();
        });
    }
};
