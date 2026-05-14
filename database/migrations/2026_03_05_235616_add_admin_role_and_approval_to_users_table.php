<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change role from ENUM to string to support 'admin' role
        DB::statement("ALTER TABLE users MODIFY role VARCHAR(20) NOT NULL DEFAULT 'student'");

        // Add is_approved column for CR approval workflow
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_approved')->default(true)->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_approved');
        });

        DB::statement("ALTER TABLE users MODIFY role ENUM('student', 'cr') NOT NULL DEFAULT 'student'");
    }
};