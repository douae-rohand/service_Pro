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
        Schema::create('client', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->text('address')->nullable();
            $table->string('ville', 100)->nullable();
            $table->boolean('isActive')->nullable()->default(true);
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrentOnUpdate()->useCurrent();
            $table->integer('adminId')->nullable()->index('adminid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client');
    }
};
