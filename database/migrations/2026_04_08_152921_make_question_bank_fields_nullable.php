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
        Schema::table('question_banks', function (Blueprint $table) {
            $table->string('department')->nullable()->change();
            $table->string('course_code')->nullable()->change();
            $table->string('course_name')->nullable()->change();
            $table->string('title')->nullable()->change();
            $table->string('question_heading')->nullable()->change();
            $table->text('sub_questions')->nullable()->change();
            $table->string('year_semester')->nullable()->change();
            $table->string('status')->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('question_banks', function (Blueprint $table) {
            $table->string('department')->nullable(false)->change();
            $table->string('course_code')->nullable(false)->change();
            $table->string('course_name')->nullable(false)->change();
            $table->string('title')->nullable(false)->change();
            $table->string('question_heading')->nullable(false)->change();
            $table->text('sub_questions')->nullable(false)->change();
            $table->string('year_semester')->nullable(false)->change();
            $table->string('status')->default('approved')->change();
        });
    }
};
