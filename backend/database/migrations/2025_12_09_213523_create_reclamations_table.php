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
        Schema::create('reclamations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('signale_par_id'); // ID de l'utilisateur qui signale
            $table->string('signale_par_type'); // 'client' ou 'intervenant'
            $table->unsignedBigInteger('concernant_id')->nullable(); // ID de l'utilisateur concerné
            $table->string('concernant_type')->nullable(); // 'client' ou 'intervenant'
            $table->string('raison');
            $table->text('message');
            $table->enum('priorite', ['haute', 'moyenne', 'basse'])->default('moyenne');
            $table->enum('statut', ['en_attente', 'en_cours', 'resolu'])->default('en_attente');
            $table->text('notes_admin')->nullable();
            $table->boolean('archived')->default(false);
            $table->timestamps();

            // Foreign keys (optional, depending on whether you want strict FKs or polymorphic relations)
            // $table->foreign('signale_par_id')->references('id')->on('utilisateurs')->onDelete('cascade');
            // $table->foreign('concernant_id')->references('id')->on('utilisateurs')->onDelete('cascade');

            // Polymorphic relation example (if needed)
            // $table->morphs('reportable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reclamations');
    }
};
