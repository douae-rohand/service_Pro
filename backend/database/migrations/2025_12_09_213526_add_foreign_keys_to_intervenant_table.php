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
        Schema::table('intervenant', function (Blueprint $table) {
            $table->foreign(['admin_id'], 'intervenant_ibfk_1')->references(['id'])->on('admin')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['id'], 'intervenant_ibfk_2')->references(['id'])->on('utilisateur')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intervenant', function (Blueprint $table) {
            $table->dropForeign('intervenant_ibfk_1');
            $table->dropForeign('intervenant_ibfk_2');
        });
    }
};
