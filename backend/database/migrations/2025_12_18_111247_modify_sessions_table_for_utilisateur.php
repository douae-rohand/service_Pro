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
            // Safe drop of existing foreign key if it exists
            try {
                $table->dropForeign(['user_id']);
            } catch (\Exception $e) {
                // Ignore if foreign key doesn't exist
            }
            
            // Rename if column exists
            if (Schema::hasColumn('sessions', 'user_id') && !Schema::hasColumn('sessions', 'utilisateur_id')) {
                $table->renameColumn('user_id', 'utilisateur_id');
            }
            
            // Re-add foreign key if column exists and utilisateur table exists
            if (Schema::hasColumn('sessions', 'utilisateur_id')) {
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
