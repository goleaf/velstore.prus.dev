<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtendedFieldsToSiteSettingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('support_email')->nullable()->after('contact_email');
            $table->string('support_hours')->nullable()->after('support_email');
            $table->boolean('maintenance_mode')->default(false)->after('support_hours');
            $table->text('maintenance_message')->nullable()->after('maintenance_mode');
            $table->string('primary_color')->nullable()->after('maintenance_message');
            $table->string('secondary_color')->nullable()->after('primary_color');
            $table->string('facebook_url')->nullable()->after('secondary_color');
            $table->string('twitter_url')->nullable()->after('facebook_url');
            $table->string('instagram_url')->nullable()->after('twitter_url');
            $table->string('linkedin_url')->nullable()->after('instagram_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'support_email',
                'support_hours',
                'maintenance_mode',
                'maintenance_message',
                'primary_color',
                'secondary_color',
                'facebook_url',
                'twitter_url',
                'instagram_url',
                'linkedin_url',
            ]);
        });
    }
}
