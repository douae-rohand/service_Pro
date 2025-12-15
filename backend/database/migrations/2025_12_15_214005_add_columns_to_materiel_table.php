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
            if (!Schema::hasColumn('materiel', 'description')) {
                $table->text('description')->nullable()->after('nom_materiel');
            }
            if (!Schema::hasColumn('materiel', 'service_id')) {
                $table->integer('service_id')->nullable()->index()->after('nom_materiel');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materiel', function (Blueprint $table) {
            $table->dropColumn(['description', 'service_id']);
        });
    }
};
