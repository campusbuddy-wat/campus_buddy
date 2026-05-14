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
        Schema::create('question_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('department');
            $table->string('course_code');
            $table->string('course_name');
            $table->string('title');
            $table->string('difficulty')->default('Medium');
            $table->string('question_heading');
            $table->text('sub_questions'); // Will store bullet points as text
            $table->string('tags')->nullable();
            $table->string('year_semester');
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_banks');
    }
};
