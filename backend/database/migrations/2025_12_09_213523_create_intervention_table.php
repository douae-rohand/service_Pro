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
            $table->date('date_intervention')->nullable();
            $table->integer('client_id')->nullable()->index('client_id');
            $table->integer('intervenant_id')->nullable()->index('intervenant_id');
            $table->integer('tache_id')->nullable()->index('tache_id');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
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
