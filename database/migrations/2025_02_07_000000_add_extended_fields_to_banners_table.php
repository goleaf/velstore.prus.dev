<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->string('display_location')->default('home')->after('type');
            $table->unsignedInteger('priority')->default(0)->after('display_location');
            $table->timestamp('starts_at')->nullable()->after('priority');
            $table->timestamp('ends_at')->nullable()->after('starts_at');
        });

        Schema::table('banner_translations', function (Blueprint $table) {
            $table->string('button_text')->nullable()->after('description');
            $table->string('button_url')->nullable()->after('button_text');
        });
    }

    public function down(): void
    {
        Schema::table('banner_translations', function (Blueprint $table) {
            $table->dropColumn(['button_text', 'button_url']);
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['display_location', 'priority', 'starts_at', 'ends_at']);
        });
    }
};
