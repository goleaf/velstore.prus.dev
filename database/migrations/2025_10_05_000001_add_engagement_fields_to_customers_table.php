<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('marketing_opt_in')->default(false)->after('status');
            $table->string('loyalty_tier', 20)->default('bronze')->after('marketing_opt_in');
            $table->text('notes')->nullable()->after('loyalty_tier');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['marketing_opt_in', 'loyalty_tier', 'notes']);
        });
    }
};
