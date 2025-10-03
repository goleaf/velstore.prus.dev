<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('currency', 3)->default('USD')->after('total_amount');
            $table->string('shipping_method')->nullable()->after('currency');
            $table->string('shipping_tracking_number')->nullable()->after('shipping_method');
            $table->timestamp('shipping_estimated_at')->nullable()->after('shipping_tracking_number');
            $table->decimal('shipping_amount', 10, 2)->default(0)->after('shipping_estimated_at');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('shipping_amount');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('discount_amount');
            $table->decimal('adjustment_amount', 10, 2)->default(0)->after('tax_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'currency',
                'shipping_method',
                'shipping_tracking_number',
                'shipping_estimated_at',
                'shipping_amount',
                'discount_amount',
                'tax_amount',
                'adjustment_amount',
            ]);
        });
    }
};
