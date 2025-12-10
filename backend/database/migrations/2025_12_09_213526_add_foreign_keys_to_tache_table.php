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
        Schema::table('tache', function (Blueprint $table) {
            $table->foreign(['service_id'], 'tache_ibfk_1')->references(['id'])->on('service')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tache', function (Blueprint $table) {
            $table->dropForeign('tache_ibfk_1');
        });
    }
};
