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
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(20) USING role::varchar");
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'student'");
            DB::statement("ALTER TABLE users ALTER COLUMN role SET NOT NULL");
        } else {
            DB::statement("ALTER TABLE users MODIFY role VARCHAR(20) NOT NULL DEFAULT 'student'");
        }

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

        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'pgsql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('student', 'cr') NOT NULL DEFAULT 'student'");
        }
    }
};