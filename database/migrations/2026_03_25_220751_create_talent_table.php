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
        Schema::create('talents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('designation')->nullable(); // e.g., Graphics Designer
            $table->string('id_no')->nullable(); // e.g., 0000000000
            $table->string('blood_group', 10)->nullable(); // e.g., B+
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('website')->nullable(); // For portfolio links 
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('talents');
    }
};
