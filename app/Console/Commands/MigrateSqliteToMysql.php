<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateSqliteToMysql extends Command
{
    protected $signature = 'db:sync-to-mysql';
    protected $description = 'Sync all data from local SQLite to remote MySQL database';

    public function handle()
    {
        $this->info('Starting database sync from SQLite to MySQL...');

        $tables = [
            'users',
            'divisions',
            'holidays',
            'attendances',
            'leave_requests',
            'meal_allowance_payments',
            'roles',
            'settings',
            'deleted_attendances',
            'locations',
            'sales_inputs',
        ];

        // Disable foreign key checks for MySQL
        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($tables as $table) {
            $this->info("Syncing table: {$table}");
            
            // Fetch data from SQLite
            $data = DB::connection('sqlite')->table($table)->get()->map(function ($item) {
                return (array) $item;
            })->toArray();

            // Clear table in MySQL
            DB::connection('mysql')->table($table)->truncate();

            // Insert into MySQL in chunks to avoid memory/packet limits
            $chunks = array_chunk($data, 100);
            foreach ($chunks as $chunk) {
                DB::connection('mysql')->table($table)->insert($chunk);
            }
            
            $this->info("✓ Synced " . count($data) . " rows for {$table}.");
        }

        // Re-enable foreign key checks
        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('Database sync completed successfully!');
    }
}
