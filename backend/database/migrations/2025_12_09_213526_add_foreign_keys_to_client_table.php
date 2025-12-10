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
        Schema::table('client', function (Blueprint $table) {
            $table->foreign(['admin_id'], 'client_ibfk_1')->references(['id'])->on('admin')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['id'], 'client_ibfk_2')->references(['id'])->on('utilisateur')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client', function (Blueprint $table) {
            $table->dropForeign('client_ibfk_1');
            $table->dropForeign('client_ibfk_2');
        });
    }
};
