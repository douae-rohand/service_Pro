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
        Schema::table('intervenantmateriel', function (Blueprint $table) {
            $table->foreign(['idMateriel'], 'intervenantmateriel_ibfk_1')->references(['id'])->on('materiel')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['idIntervenant'], 'intervenantmateriel_ibfk_2')->references(['id'])->on('intervenant')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intervenantmateriel', function (Blueprint $table) {
            $table->dropForeign('intervenantmateriel_ibfk_1');
            $table->dropForeign('intervenantmateriel_ibfk_2');
        });
    }
};
