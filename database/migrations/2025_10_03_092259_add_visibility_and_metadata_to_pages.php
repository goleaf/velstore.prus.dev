<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('template')->default('default')->after('status');
            $table->boolean('show_in_navigation')->default(false)->after('template');
            $table->boolean('show_in_footer')->default(false)->after('show_in_navigation');
            $table->boolean('is_featured')->default(false)->after('show_in_footer');
            $table->timestamp('published_at')->nullable()->after('is_featured');
        });

        Schema::table('page_translations', function (Blueprint $table) {
            $table->string('excerpt', 500)->nullable()->after('title');
            $table->string('meta_title')->nullable()->after('excerpt');
            $table->text('meta_description')->nullable()->after('meta_title');
        });
    }

    public function down(): void
    {
        Schema::table('page_translations', function (Blueprint $table) {
            $table->dropColumn(['excerpt', 'meta_title', 'meta_description']);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['template', 'show_in_navigation', 'show_in_footer', 'is_featured', 'published_at']);
        });
    }
};
