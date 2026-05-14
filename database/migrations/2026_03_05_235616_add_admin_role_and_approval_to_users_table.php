<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * Changed from MySQL MODIFY to PostgreSQL-compatible ALTER COLUMN.
     * Since the base migration now uses string('role', 20), this migration
     * just changes the default and adds is_approved — no type change needed.
     */
    public function up(): void
    {
        // PostgreSQL-compatible: change the default on the role column
        DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'student'");
        DB::statement("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(20)");

        // Add is_approved column for CR approval workflow
        if (!Schema::hasColumn('users', 'is_approved')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_approved')->default(true)->after('role');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_approved');
        });

        // Restore original default
        DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'student'");
    }
};