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
        Schema::table('alumni_registrations', function (Blueprint $blueprint) {
            $blueprint->string('badge_text')->nullable()->after('company');
            $blueprint->string('badge_style')->nullable()->after('badge_text');
            $blueprint->string('top_img_class')->nullable()->after('badge_style');
            $blueprint->string('profile_img_class')->nullable()->after('top_img_class');
            $blueprint->string('subtitle')->nullable()->after('current_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumni_registrations', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['badge_text', 'badge_style', 'top_img_class', 'profile_img_class', 'subtitle']);
        });
    }
};
