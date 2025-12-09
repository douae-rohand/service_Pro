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
        Schema::table('serviceinformation', function (Blueprint $table) {
            $table->foreign(['idService'], 'serviceinformation_ibfk_1')->references(['id'])->on('service')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['idInformation'], 'serviceinformation_ibfk_2')->references(['id'])->on('information')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('serviceinformation', function (Blueprint $table) {
            $table->dropForeign('serviceinformation_ibfk_1');
            $table->dropForeign('serviceinformation_ibfk_2');
        });
    }
};
