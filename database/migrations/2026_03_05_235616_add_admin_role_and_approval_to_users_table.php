<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    // This migration must NOT run inside a transaction
    // because DDL changes in PostgreSQL can cause issues in aborted transactions
    public $withinTransaction = false;

    /**
     * Run the migrations.
     *
     * NOTE: The role column was originally an ENUM in MySQL. It is now a
     * string(20) in the base migration, so no ALTER is needed here.
     * This migration only adds the is_approved column.
     */
    public function up(): void
    {
        // Add is_approved column for CR approval workflow (if it doesn't already exist)
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
        if (Schema::hasColumn('users', 'is_approved')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_approved');
            });
        }
    }
};