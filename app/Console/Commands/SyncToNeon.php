<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncToNeon extends Command
{
    protected $signature = 'db:sync-to-neon {password}';
    protected $description = 'Syncs all data from local MySQL to remote Neon PostgreSQL';

    public function handle()
    {
        $password = $this->argument('password');

        // Dynamically configure the Neon connection
        config(['database.connections.neon' => [
            'driver' => 'pgsql',
            'host' => 'ep-ancient-bread-aoqgz8ej.c-2.ap-southeast-1.aws.neon.tech', // Direct connection (no pooler)
            'port' => '5432',
            'database' => 'neondb',
            'username' => 'neondb_owner',
            'password' => $password,
            'charset' => 'utf8',
            'prefix' => '',
            'search_path' => 'public',
            'sslmode' => 'require',
        ]]);

        $this->info("Connecting to Neon...");
        try {
            DB::connection('neon')->getPdo();
            $this->info("Successfully connected to Neon!");
        } catch (\Exception $e) {
            $this->error("Failed to connect to Neon: " . $e->getMessage());
            return;
        }

        $tables = [
            'district_associations',
        ];

        foreach ($tables as $table) {
            $this->info("Syncing table: {$table}");
            
            try {
                // Clear the remote table first
                DB::connection('neon')->table($table)->delete();
                
                $records = DB::connection('mysql')->table($table)->get();
                $count = 0;
                
                $chunks = $records->chunk(100);
                foreach ($chunks as $chunk) {
                    $insertData = json_decode(json_encode($chunk->toArray()), true);
                    DB::connection('neon')->table($table)->insert($insertData);
                    $count += count($insertData);
                }
                
                $this->line("✅ Migrated {$count} records into {$table}");
            } catch (\Exception $e) {
                $this->error("❌ Failed to sync {$table}: " . $e->getMessage());
            }
        }

        $this->info("🎉 Migration Complete!");
    }
}
