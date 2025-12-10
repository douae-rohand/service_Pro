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
        Schema::table('photointervention', function (Blueprint $table) {
            $table->foreign(['interventionId'], 'photointervention_ibfk_1')->references(['id'])->on('intervention')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photointervention', function (Blueprint $table) {
            $table->dropForeign('photointervention_ibfk_1');
        });
    }
};
