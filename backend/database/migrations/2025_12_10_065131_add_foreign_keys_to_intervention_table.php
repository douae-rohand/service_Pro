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
        Schema::table('intervention', function (Blueprint $table) {
            $table->foreign(['clientId'], 'intervention_ibfk_1')->references(['id'])->on('client')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['tacheId'], 'intervention_ibfk_2')->references(['id'])->on('tache')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['intervenantId'], 'intervention_ibfk_3')->references(['id'])->on('intervenant')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intervention', function (Blueprint $table) {
            $table->dropForeign('intervention_ibfk_1');
            $table->dropForeign('intervention_ibfk_2');
            $table->dropForeign('intervention_ibfk_3');
        });
    }
};
