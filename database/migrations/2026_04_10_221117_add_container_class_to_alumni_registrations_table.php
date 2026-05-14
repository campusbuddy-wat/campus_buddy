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
            $blueprint->string('container_class')->nullable()->after('profile_img_class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumni_registrations', function (Blueprint $blueprint) {
            $blueprint->dropColumn('container_class');
        });
    }
};
