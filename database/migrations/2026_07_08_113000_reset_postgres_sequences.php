<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only run if the database connection driver is PostgreSQL (which is used in production on Render)
        if (DB::connection()->getDriverName() === 'pgsql') {
            $tables = [
                'users', 
                'schedules', 
                'class_tasks', 
                'materials', 
                'events', 
                'clubs', 
                'alumni_registrations', 
                'posts', 
                'comments', 
                'likes',
                'question_banks'
            ];

            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    DB::statement("
                        SELECT setval(
                            pg_get_serial_sequence('{$table}', 'id'), 
                            COALESCE((SELECT MAX(id) FROM \"{$table}\"), 1), 
                            (SELECT MAX(id) FROM \"{$table}\") IS NOT NULL
                        )
                    ");
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback action needed for resetting sequence values
    }
};
