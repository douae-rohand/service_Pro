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
        Schema::table('intervenant_service', function (Blueprint $table) {
            if (!Schema::hasColumn('intervenant_service', 'presentation')) {
                $table->text('presentation')->nullable()->after('status');
            }
            if (!Schema::hasColumn('intervenant_service', 'experience')) {
                $table->decimal('experience', 5, 2)->nullable()->after('presentation');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intervenant_service', function (Blueprint $table) {
            $table->dropColumn(['presentation', 'experience']);
        });
    }
};
