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
        Schema::table('intervenant_service', function (Blueprint $table) {
            $table->foreign('intervenant_id', 'intervenant_service_ibfk_1')->references('id')->on('intervenant')->onUpdate('no action')->onDelete('cascade');
            $table->foreign('service_id', 'intervenant_service_ibfk_2')->references('id')->on('service')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intervenant_service', function (Blueprint $table) {
            $table->dropForeign('intervenant_service_ibfk_1');
            $table->dropForeign('intervenant_service_ibfk_2');
        });
    }
};
