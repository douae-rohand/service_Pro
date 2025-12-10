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
        Schema::table('tache_materiel', function (Blueprint $table) {
            $table->foreign(['materiel_id'], 'tachemateriel_ibfk_1')->references(['id'])->on('materiel')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['tache_id'], 'tachemateriel_ibfk_2')->references(['id'])->on('tache')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tache_materiel', function (Blueprint $table) {
            $table->dropForeign('tachemateriel_ibfk_1');
            $table->dropForeign('tachemateriel_ibfk_2');
        });
    }
};
