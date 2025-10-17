<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetDatabaseSequences extends Command
{
    protected $signature = 'db:reset-sequences';

    protected $description = 'Reset database auto-increment sequences to match current max IDs';

    public function handle(): int
    {
        $driver = DB::connection()->getDriverName();
        $this->info("Database driver: {$driver}");

        if ($driver === 'pgsql') {
            $this->resetPostgresSequences();
        } elseif ($driver === 'sqlite') {
            $this->resetSqliteSequences();
        } else {
            $this->error("Unsupported database driver: {$driver}");
            return self::FAILURE;
        }

        $this->info('✅ Database sequences reset successfully!');
        return self::SUCCESS;
    }

    private function resetPostgresSequences(): void
    {
        $tables = ['links', 'posts', 'newsletter_subscriptions', 'profiles'];

        foreach ($tables as $table) {
            try {
                $sequence = "{$table}_id_seq";
                DB::statement("SELECT setval('{$sequence}', (SELECT COALESCE(MAX(id), 1) FROM {$table}))");
                $this->info("✓ Reset sequence for: {$table}");
            } catch (\Exception $e) {
                $this->warn("⚠ Could not reset sequence for {$table}: {$e->getMessage()}");
            }
        }
    }

    private function resetSqliteSequences(): void
    {
        $tables = ['links', 'posts', 'newsletter_subscriptions', 'profiles'];

        foreach ($tables as $table) {
            try {
                $maxId = DB::table($table)->max('id') ?? 0;
                DB::statement("UPDATE sqlite_sequence SET seq = {$maxId} WHERE name = '{$table}'");
                $this->info("✓ Reset sequence for: {$table} (max ID: {$maxId})");
            } catch (\Exception $e) {
                $this->warn("⚠ Could not reset sequence for {$table}: {$e->getMessage()}");
            }
        }
    }
}
