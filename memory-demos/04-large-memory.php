<?php

declare(strict_types=1);

/**
 * Part 4 — Large Memory Usage
 *
 * Shows significant growth when holding many rows in an array, then release via unset().
 *
 * Run: php memory-demos/04-large-memory.php
 * Optional: php memory-demos/04-large-memory.php 200000
 */

require __DIR__.'/helpers.php';

$count = isset($argv[1]) ? max(1, (int) $argv[1]) : 100_000;

section("Part 4: Large Memory Usage ({$count} rows)");

note('Before: low memory');
$before = memory_get_usage(true);
memorySnapshot('before loop');

$users = [];
for ($i = 0; $i < $count; $i++) {
    $users[] = [
        'id' => $i,
        'name' => 'User '.$i,
    ];
}

note('After adding data: memory increases significantly');
$after = memory_get_usage(true);
memorySnapshot('after building $users');
echo '  Growth: +'.formatBytes($after - $before)."\n";
echo '  Rows:   '.count($users)."\n";

unset($users);

note('After unset: memory released (often back near baseline for real usage)');
$afterUnset = memory_get_usage(true);
memorySnapshot('after unset($users)');
echo '  Released vs peak array: '.formatBytes($after - $afterUnset)."\n";
echo '  Peak usage:             '.formatBytes(memory_get_peak_usage(true))."\n";

if ($after <= $before) {
    fwrite(STDERR, "Unexpected: memory did not grow after allocating {$count} rows.\n");
    exit(1);
}

echo "\nDone.\n";
