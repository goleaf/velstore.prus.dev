<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE refunds
            MODIFY COLUMN status ENUM('requested','approved','rejected','completed','failed','pending')
            DEFAULT 'requested'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE refunds
            MODIFY COLUMN status ENUM('requested','approved','rejected','completed','failed')
            DEFAULT 'requested'
        ");
    }
};
