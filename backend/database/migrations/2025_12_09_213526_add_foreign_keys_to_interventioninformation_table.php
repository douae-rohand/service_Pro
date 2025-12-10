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
        Schema::table('intervention_information', function (Blueprint $table) {
            $table->foreign(['intervention_id'], 'interventioninformation_ibfk_1')->references(['id'])->on('intervention')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['information_id'], 'interventioninformation_ibfk_2')->references(['id'])->on('information')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intervention_information', function (Blueprint $table) {
            $table->dropForeign('interventioninformation_ibfk_1');
            $table->dropForeign('interventioninformation_ibfk_2');
        });
    }
};
