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
        Schema::create('docs_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_visible')->default(false);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('hero_title')->default('Campus Buddy');
            $table->text('hero_subtitle')->nullable();
            $table->text('elevator_pitch')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docs_settings');
    }
};
