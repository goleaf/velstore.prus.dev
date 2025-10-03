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
        Schema::table('product_variants', function (Blueprint $table) {
            if (! Schema::hasColumn('product_variants', 'name')) {
                $table->string('name')->default('')->after('product_id');
            }

            if (! Schema::hasColumn('product_variants', 'value')) {
                $table->string('value')->nullable()->after('name');
            }
        });

        Schema::table('product_variant_translations', function (Blueprint $table) {
            if (! Schema::hasColumn('product_variant_translations', 'value')) {
                $table->string('value')->nullable()->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variant_translations', function (Blueprint $table) {
            if (Schema::hasColumn('product_variant_translations', 'value')) {
                $table->dropColumn('value');
            }
        });

        Schema::table('product_variants', function (Blueprint $table) {
            if (Schema::hasColumn('product_variants', 'value')) {
                $table->dropColumn('value');
            }

            if (Schema::hasColumn('product_variants', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};
