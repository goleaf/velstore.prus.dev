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
        Schema::table('coupons', function (Blueprint $table) {
            $table->decimal('minimum_spend', 10, 2)->nullable()->after('type');
            $table->unsignedInteger('usage_limit')->nullable()->after('minimum_spend');
            $table->unsignedInteger('usage_count')->default(0)->after('usage_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['minimum_spend', 'usage_limit', 'usage_count']);
        });
    }
};
