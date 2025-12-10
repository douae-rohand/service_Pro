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
        Schema::create('tache', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('idService')->nullable()->index('idservice');
            $table->string('nomTache', 150)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 50)->nullable();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tache');
    }
};
