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
        Schema::create('portfolio', function (Blueprint $table) {
            $table->id();
            $table->integer('intervenant_id')->index();
            $table->string('image_path');
            $table->string('description')->nullable();
            $table->integer('service_id')->nullable(); // Optional link to a service category
            $table->timestamps();
            
            $table->foreign('intervenant_id')->references('id')->on('intervenant')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolio');
    }
};
