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
            $table->timestamp('client_last_reminder_sent_at')->nullable()->after('updated_at');
            $table->timestamp('intervenant_last_reminder_sent_at')->nullable()->after('client_last_reminder_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intervention', function (Blueprint $table) {
            $table->dropColumn(['client_last_reminder_sent_at', 'intervenant_last_reminder_sent_at']);
        });
    }
};
