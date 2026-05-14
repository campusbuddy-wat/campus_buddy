<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('assignments', 'class_tasks');

        Schema::table('class_tasks', function (Blueprint $table) {
            $table->string('type')->default('assignment')->after('id');
            $table->string('topic')->nullable()->after('title');
            $table->string('duration_or_slot')->nullable()->after('deadline');
            $table->string('progress_status')->nullable()->after('duration_or_slot');
            $table->text('tip_1')->nullable()->after('description');
            $table->text('tip_2')->nullable()->after('tip_1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_tasks', function (Blueprint $table) {
            $table->dropColumn(['type', 'topic', 'duration_or_slot', 'progress_status', 'tip_1', 'tip_2']);
        });

        Schema::rename('class_tasks', 'assignments');
    }
};