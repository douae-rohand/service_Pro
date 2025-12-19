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
            // We MUST keep 'user_id' because Laravel's session driver expects it.
            // But we want it to point to our 'utilisateur' table instead of 'users'.
            
            // 1. If 'utilisateur_id' exists (from previous bad migration), rename it back to 'user_id'
            if (Schema::hasColumn('sessions', 'utilisateur_id') && !Schema::hasColumn('sessions', 'user_id')) {
                $table->renameColumn('utilisateur_id', 'user_id');
            }
        });

        Schema::table('sessions', function (Blueprint $table) {
            if (Schema::hasColumn('sessions', 'user_id')) {
                // 2. Ensure type matches 'utilisateur.id' (integer)
                $table->integer('user_id')->nullable()->change();
                
                // 3. Add foreign key to 'utilisateur' table
                // Note: We might need to drop existing foreign keys first if they point to 'users'
                // $table->dropForeign(['user_id']); // Uncomment if needed/known
                
                // Add the correct FK
                $table->foreign('user_id')->references('id')->on('utilisateur')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }
};
