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
        Schema::table('favorise', function (Blueprint $table) {
            $table->foreign(['idIntervenant'], 'favorise_ibfk_1')->references(['id'])->on('intervenant')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['idService'], 'favorise_ibfk_2')->references(['id'])->on('service')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['idClient'], 'favorise_ibfk_3')->references(['id'])->on('client')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('favorise', function (Blueprint $table) {
            $table->dropForeign('favorise_ibfk_1');
            $table->dropForeign('favorise_ibfk_2');
            $table->dropForeign('favorise_ibfk_3');
        });
    }
};
