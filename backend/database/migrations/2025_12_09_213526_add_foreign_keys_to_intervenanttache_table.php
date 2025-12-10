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
        Schema::table('intervenant_tache', function (Blueprint $table) {
            $table->foreign(['intervenant_id'], 'intervenanttache_ibfk_1')->references(['id'])->on('intervenant')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['tache_id'], 'intervenanttache_ibfk_2')->references(['id'])->on('tache')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intervenant_tache', function (Blueprint $table) {
            $table->dropForeign('intervenanttache_ibfk_1');
            $table->dropForeign('intervenanttache_ibfk_2');
        });
    }
};
