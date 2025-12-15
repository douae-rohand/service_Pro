<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contrainte', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('tache_id')->index();
            $table->string('nom', 150);
            $table->decimal('seuil', 10, 2)->nullable();
            $table->string('unite', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrainte');
    }
};