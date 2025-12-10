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
        Schema::create('justificatif', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('type')->nullable();
            $table->string('cheminFichier', 100)->nullable();
            $table->integer('intervenantId')->nullable()->index('intervenantid');
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('justificatif');
    }
};
