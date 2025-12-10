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
        Schema::table('justificatif', function (Blueprint $table) {
            $table->foreign(['intervenant_id'], 'justificatif_ibfk_1')->references(['id'])->on('intervenant')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('justificatif', function (Blueprint $table) {
            $table->dropForeign('justificatif_ibfk_1');
        });
    }
};
