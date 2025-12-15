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
        Schema::table('materiel', function (Blueprint $table) {
            // Add service_id as foreign key
            $table->integer('service_id')->nullable()->after('id');
            
            // Add foreign key constraint
            $table->foreign('service_id')
                  ->references('id')
                  ->on('service')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materiel', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['service_id']);
            
            // Drop the column
            $table->dropColumn('service_id');
        });
    }
};
