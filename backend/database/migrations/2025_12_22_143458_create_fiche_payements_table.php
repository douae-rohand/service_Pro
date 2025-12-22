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
        Schema::create('fiche_payement', function (Blueprint $table) {
            $table->id();
            $table->integer('intervention_id');
            $table->decimal('ht_tache', 10, 2)->default(0);
            $table->decimal('ht_materiel', 10, 2)->default(0);
            $table->decimal('ht_total', 10, 2)->default(0);
            $table->decimal('tva_taux', 5, 2)->default(20.00);
            $table->decimal('tva_montant', 10, 2)->default(0);
            $table->decimal('ttc', 10, 2)->default(0);
            $table->string('fichier_path')->nullable();
            $table->timestamps();

            $table->foreign('intervention_id')->references('id')->on('intervention')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiche_payement');
    }
};
