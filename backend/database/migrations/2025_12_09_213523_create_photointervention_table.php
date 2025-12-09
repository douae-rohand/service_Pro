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
        Schema::create('photointervention', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('photoPath')->nullable();
            $table->enum('phasePrise', ['avant', 'apres'])->nullable();
            $table->text('description')->nullable();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrentOnUpdate()->useCurrent();
            $table->integer('interventionId')->nullable()->index('interventionid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photointervention');
    }
};
