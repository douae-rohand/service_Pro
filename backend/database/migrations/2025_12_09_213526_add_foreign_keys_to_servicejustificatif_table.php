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
        Schema::table('service_justificatif', function (Blueprint $table) {
            $table->foreign(['service_id'], 'servicejustificatif_ibfk_1')->references(['id'])->on('service')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['justificatif_id'], 'servicejustificatif_ibfk_2')->references(['id'])->on('justificatif')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_justificatif', function (Blueprint $table) {
            $table->dropForeign('servicejustificatif_ibfk_1');
            $table->dropForeign('servicejustificatif_ibfk_2');
        });
    }
};
