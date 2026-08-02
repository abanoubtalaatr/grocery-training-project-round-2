<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MemoryTest extends Command
{
    /**
     * Demo users are tagged so they can be cleaned up safely.
     */
    public const DEMO_EMAIL_PREFIX = 'memory_demo_';

    protected $signature = 'memory:test
                            {--mode=compare : all|chunk|compare}
                            {--chunk=1000 : Chunk size for the good example}
                            {--seed=0 : Seed this many temporary demo users before measuring}
                            {--cleanup : Delete previously seeded memory_demo_* users after the run}';

    protected $description = 'Compare User::all() vs User::chunk() memory usage (PHP GC learning demo)';

    public function handle(): int
    {
        $mode = strtolower((string) $this->option('mode'));
        $chunkSize = max(1, (int) $this->option('chunk'));
        $seedCount = max(0, (int) $this->option('seed'));

        if (! in_array($mode, ['all', 'chunk', 'compare'], true)) {
            $this->error('Invalid --mode. Use all, chunk, or compare.');

            return self::FAILURE;
        }

        if ($seedCount > 0) {
            $this->seedDemoUsers($seedCount);
        }

        $totalUsers = User::query()->count();
        $this->newLine();
        $this->info('PHP Memory Management — Laravel Practical Example');
        $this->line('Users in database: '.$totalUsers);
        $this->line('Peak so far: '.$this->formatBytes(memory_get_peak_usage(true)));
        $this->newLine();

        if ($totalUsers === 0) {
            $this->warn('No users found. Re-run with --seed=5000 (or migrate/seed your app).');

            return self::FAILURE;
        }

        $results = [];

        // Run chunk before all in compare mode so the "good" baseline is not
        // inflated by a prior full-table load in the same process.
        if ($mode === 'chunk' || $mode === 'compare') {
            $results['chunk'] = $this->runGoodExample($chunkSize);
        }

        if ($mode === 'all' || $mode === 'compare') {
            $results['all'] = $this->runBadExample();
        }

        if ($mode === 'compare' && isset($results['all'], $results['chunk'])) {
            $this->printComparison($results['all'], $results['chunk'], $chunkSize);
        }

        if ($this->option('cleanup')) {
            $deleted = $this->cleanupDemoUsers();
            $this->info("Cleaned up {$deleted} demo user(s).");
        }

        return self::SUCCESS;
    }

    /**
     * Bad example: load every user into memory at once.
     *
     * @return array{label: string, start: int, after_load: int, after_loop: int, after_unset: int, processed: int, peak: int}
     */
    private function runBadExample(): array
    {
        $this->warn('BAD EXAMPLE — User::all()');
        $this->line('Problem: for large tables, the full collection stays in memory.');
        $this->newLine();

        gc_collect_cycles();
        $start = memory_get_usage(true);
        $this->line('Memory before: '.$this->formatBytes($start));

        $users = User::all();
        $afterLoad = memory_get_usage(true);
        $this->line('After User::all(): '.$this->formatBytes($afterLoad).' (+'.$this->formatBytes($afterLoad - $start).')');

        $processed = 0;
        foreach ($users as $user) {
            $processed++;
            // Touch an attribute so Eloquent actually hydrates work is realistic.
            $user->email;
        }

        $afterLoop = memory_get_usage(true);
        $this->line('After foreach: '.$this->formatBytes($afterLoop));
        $this->line("Processed: {$processed} users");

        unset($users);
        gc_collect_cycles();
        $afterUnset = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);

        $this->line('After unset + GC: '.$this->formatBytes($afterUnset));
        $this->line('Peak usage: '.$this->formatBytes($peak));
        $this->newLine();

        return [
            'label' => 'User::all()',
            'start' => $start,
            'after_load' => $afterLoad,
            'after_loop' => $afterLoop,
            'after_unset' => $afterUnset,
            'processed' => $processed,
            'peak' => $peak,
        ];
    }

    /**
     * Good example: process users in chunks and release each batch.
     *
     * @return array{label: string, start: int, after_load: int, after_loop: int, after_unset: int, processed: int, peak: int, chunks: int, max_batch: int}
     */
    private function runGoodExample(int $chunkSize): array
    {
        $this->info('GOOD EXAMPLE — User::chunk('.$chunkSize.')');
        $this->line('Flow: load N → process → release → load next N');
        $this->newLine();

        gc_collect_cycles();
        $start = memory_get_usage(true);
        $this->line('Memory before: '.$this->formatBytes($start));

        $processed = 0;
        $chunks = 0;
        $maxBatchMemory = $start;
        $afterLoad = $start;

        User::chunk($chunkSize, function ($users) use (&$processed, &$chunks, &$maxBatchMemory, &$afterLoad): void {
            $chunks++;
            $batchMemory = memory_get_usage(true);
            $afterLoad = max($afterLoad, $batchMemory);
            $maxBatchMemory = max($maxBatchMemory, $batchMemory);

            foreach ($users as $user) {
                $processed++;
                $user->email;
            }

            // Explicitly drop the batch reference before the next chunk loads.
            unset($users);
        });

        $afterLoop = memory_get_usage(true);
        gc_collect_cycles();
        $afterUnset = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);

        $this->line('Highest during chunking: '.$this->formatBytes($maxBatchMemory).' (+'.$this->formatBytes($maxBatchMemory - $start).' vs start)');
        $this->line('After all chunks: '.$this->formatBytes($afterLoop));
        $this->line('After GC: '.$this->formatBytes($afterUnset));
        $this->line("Processed: {$processed} users across {$chunks} chunk(s)");
        $this->line('Peak usage: '.$this->formatBytes($peak));
        $this->newLine();

        return [
            'label' => 'User::chunk('.$chunkSize.')',
            'start' => $start,
            'after_load' => $afterLoad,
            'after_loop' => $afterLoop,
            'after_unset' => $afterUnset,
            'processed' => $processed,
            'peak' => $peak,
            'chunks' => $chunks,
            'max_batch' => $maxBatchMemory,
        ];
    }

    /**
     * @param  array{label: string, start: int, after_load: int, peak: int, processed: int}  $all
     * @param  array{label: string, start: int, after_load: int, peak: int, processed: int, max_batch?: int}  $chunk
     */
    private function printComparison(array $all, array $chunk, int $chunkSize): void
    {
        $allGrowth = $all['after_load'] - $all['start'];
        $chunkGrowth = ($chunk['max_batch'] ?? $chunk['after_load']) - $chunk['start'];

        $this->info('COMPARISON');
        $this->table(
            ['Metric', 'User::all()', 'User::chunk('.$chunkSize.')'],
            [
                ['Users processed', (string) $all['processed'], (string) $chunk['processed']],
                ['Memory growth (load)', $this->formatBytes($allGrowth), $this->formatBytes($chunkGrowth)],
                ['Peak during run', $this->formatBytes($all['peak']), $this->formatBytes($chunk['peak'])],
            ]
        );

        if ($allGrowth > $chunkGrowth) {
            $saved = $allGrowth - $chunkGrowth;
            $this->info('Chunking kept about '.$this->formatBytes($saved).' less working-set growth than loading everything.');
        } else {
            $this->line('With small tables, chunking overhead can dominate — try --seed=10000 for a clearer gap.');
        }

        $this->newLine();
        $this->line('ASCII flow (chunk):');
        $this->line(<<<'ASCII'
  Load 1000 users → Process → Release memory
  Load next 1000  → Process → Release memory
  ...
ASCII);
    }

    private function seedDemoUsers(int $count): void
    {
        $this->info("Seeding {$count} temporary demo users...");

        $password = Hash::make('password');
        $now = now();
        $batch = [];
        $batchSize = 500;

        for ($i = 1; $i <= $count; $i++) {
            $token = Str::lower(Str::random(8)).'_'.$i;
            $batch[] = [
                'username' => self::DEMO_EMAIL_PREFIX.$token,
                'email' => self::DEMO_EMAIL_PREFIX.$token.'@example.test',
                'password' => $password,
                'email_verified' => true,
                'phone_verified' => false,
                'agree_terms' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= $batchSize) {
                DB::table('users')->insert($batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            DB::table('users')->insert($batch);
        }

        $this->info('Seed complete.');
    }

    private function cleanupDemoUsers(): int
    {
        return User::withTrashed()
            ->where('email', 'like', self::DEMO_EMAIL_PREFIX.'%')
            ->forceDelete();
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = $bytes > 0 ? (int) floor(log($bytes, 1024)) : 0;
        $pow = min($pow, count($units) - 1);

        return round($bytes / (1024 ** $pow), 2).' '.$units[$pow];
    }
}
