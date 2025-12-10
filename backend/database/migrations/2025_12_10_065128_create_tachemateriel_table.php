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
        Schema::create('tachemateriel', function (Blueprint $table) {
            $table->integer('idMateriel');
            $table->integer('idTache')->index('idtache');
            $table->float('prixMateriel')->nullable();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrentOnUpdate()->useCurrent();

            $table->primary(['idMateriel', 'idTache']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tachemateriel');
    }
};
