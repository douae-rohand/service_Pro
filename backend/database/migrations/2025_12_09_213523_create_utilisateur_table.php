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
        Schema::create('utilisateur', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nom', 100);
            $table->string('prenom', 100)->nullable();
            $table->string('email', 150)->unique('email');
            $table->string('password');
            $table->string('telephone', 20)->nullable();
            $table->string('url')->nullable();
            $table->string('googlePw')->nullable();
            $table->text('address')->nullable();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilisateur');
    }
};
