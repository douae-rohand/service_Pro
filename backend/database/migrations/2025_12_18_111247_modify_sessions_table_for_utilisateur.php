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
        Schema::table('sessions', function (Blueprint $table) {
            // Rename column if it exists as 'user_id'
            if (Schema::hasColumn('sessions', 'user_id') && !Schema::hasColumn('sessions', 'utilisateur_id')) {
                // Check if there is an index to drop first (optional but safer)
                // $table->dropIndex(['user_id']); 
                
                $table->renameColumn('user_id', 'utilisateur_id');
            }
        });

        Schema::table('sessions', function (Blueprint $table) {
            // Re-add foreign key constraint pointing to utilisateur table
            if (Schema::hasColumn('sessions', 'utilisateur_id')) {
                // Ensure we don't have a conflict if we try to add it again
                // utilisateur.id is created as $table->integer('id', true), which is a signed integer.
                // We must match the type exactly.
                $table->integer('utilisateur_id')->nullable()->change();
                $table->foreign('utilisateur_id')->references('id')->on('utilisateur')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            // Drop the foreign key
            $table->dropForeign(['utilisateur_id']);
            
            // Rename utilisateur_id back to user_id
            $table->renameColumn('utilisateur_id', 'user_id');
        });
    }
};
