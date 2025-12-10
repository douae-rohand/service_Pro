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
        Schema::create('disponibilite', function (Blueprint $table) {
            $table->integer('id', true);
            $table->enum('type', ['ponctuelle', 'reguliere'])->nullable();
            $table->time('heureDebut')->nullable();
            $table->time('heureFin')->nullable();
            $table->date('dateSpecific')->nullable();
            $table->enum('joursSemaine', ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'])->nullable();
            $table->integer('intervenantId')->nullable()->index('intervenantid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disponibilite');
    }
};
