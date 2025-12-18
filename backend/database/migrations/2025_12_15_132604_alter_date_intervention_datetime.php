<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('ALTER TABLE intervention MODIFY date_intervention DATETIME NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE intervention MODIFY date_intervention DATE NULL');
    }
};