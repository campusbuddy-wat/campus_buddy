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
        Schema::create('alumni_registrations', function (Blueprint $table) {
            $table->id();

            // Personal info
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('profile_image')->nullable();

            // Academic info
            $table->string('student_id');
            $table->string('department');
            $table->string('batch');
            $table->string('graduation_year');

            // Professional info
            $table->string('current_position');   // e.g. "Software Engineer"
            $table->string('company');             // e.g. "Google"
            $table->string('company_logo')->nullable();
            $table->string('category');            // e.g. "software-engineering", "finance"
            $table->string('linkedin_url')->nullable();

            // Card display
            $table->string('card_bg_image')->nullable();  // background for card-top

            // Admin
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_registrations');
    }
};
