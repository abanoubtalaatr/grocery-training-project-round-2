<?php

declare(strict_types=1);

/**
 * Shared helpers for PHP memory management demos.
 */
function formatBytes(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = $bytes > 0 ? (int) floor(log($bytes, 1024)) : 0;
    $pow = min($pow, count($units) - 1);
    $value = $bytes / (1024 ** $pow);

    return round($value, 2).' '.$units[$pow];
}

function memorySnapshot(string $label): int
{
    $usage = memory_get_usage(true);
    $real = memory_get_usage(false);
    $peak = memory_get_peak_usage(true);

    echo sprintf(
        "[%-40s] real=%-10s emalloc=%-10s peak=%-10s\n",
        $label,
        formatBytes($usage),
        formatBytes($real),
        formatBytes($peak)
    );

    return $usage;
}

function section(string $title): void
{
    echo "\n".str_repeat('=', 72)."\n";
    echo "  {$title}\n";
    echo str_repeat('=', 72)."\n\n";
}

function note(string $message): void
{
    echo "  → {$message}\n";
}

function gcSnapshot(string $label): void
{
    if (! function_exists('gc_status')) {
        note("gc_status() not available ({$label})");

        return;
    }

    $status = gc_status();
    echo sprintf(
        "  [GC %-28s] runs=%d collected=%d roots=%d threshold=%d\n",
        $label,
        $status['runs'] ?? 0,
        $status['collected'] ?? 0,
        $status['roots'] ?? 0,
        $status['threshold'] ?? 0
    );
}
