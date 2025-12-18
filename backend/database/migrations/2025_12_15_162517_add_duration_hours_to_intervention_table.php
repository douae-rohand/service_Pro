<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intervention', function (Blueprint $table) {
            $table->decimal('duration_hours', 5, 2)->nullable()->after('date_intervention');
        });
    }

    public function down(): void
    {
        Schema::table('intervention', function (Blueprint $table) {
            $table->dropColumn('duration_hours');
        });
    }
};