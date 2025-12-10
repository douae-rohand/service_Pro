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
        Schema::table('interventionmateriel', function (Blueprint $table) {
            $table->foreign(['idMateriel'], 'interventionmateriel_ibfk_1')->references(['id'])->on('materiel')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['idIntervention'], 'interventionmateriel_ibfk_2')->references(['id'])->on('intervention')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interventionmateriel', function (Blueprint $table) {
            $table->dropForeign('interventionmateriel_ibfk_1');
            $table->dropForeign('interventionmateriel_ibfk_2');
        });
    }
};
