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
        Schema::create('serviceinformation', function (Blueprint $table) {
            $table->integer('idInformation');
            $table->integer('idService')->index('idservice');
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrentOnUpdate()->useCurrent();

            $table->primary(['idInformation', 'idService']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('serviceinformation');
    }
};
