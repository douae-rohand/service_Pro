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
        Schema::table('intervention', function (Blueprint $table) {
            // Change status from enum to string to allow more flexible status values
            // and avoid "Data truncated" errors when frontend sends values not in original enum.
            $table->string('status', 50)->default('en_attente')->change();
            // $table->text('description')->nullable(); // Already added by previous migration
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intervention', function (Blueprint $table) {
            $table->enum('status', ['en attend', 'acceptee', 'refuse', 'termine'])->default('en attend')->change();
        });
    }
};
