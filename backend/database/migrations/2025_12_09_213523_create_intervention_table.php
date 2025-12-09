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
        Schema::create('intervention', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('address')->nullable();
            $table->string('ville', 100)->nullable();
            $table->string('status', 50)->nullable();
            $table->date('dateIntervention')->nullable();
            $table->integer('clientId')->nullable()->index('clientid');
            $table->integer('intervenantId')->nullable()->index('intervenantid');
            $table->integer('tacheId')->nullable()->index('tacheid');
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intervention');
    }
};
