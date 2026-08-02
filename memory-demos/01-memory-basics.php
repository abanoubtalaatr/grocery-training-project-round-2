<?php

declare(strict_types=1);

/**
 * Part 1 — PHP Memory Basics
 *
 * Demonstrates variable allocation, memory_get_usage(), and unset().
 *
 * Run: php memory-demos/01-memory-basics.php
 */

require __DIR__.'/helpers.php';

section('Part 1: Variable Allocation & unset()');

note('Creating a variable consumes memory.');
note('unset() removes the reference; when no references remain, PHP frees memory.');
echo "\n";

$before = memory_get_usage();
echo 'Before $name:          '.formatBytes($before)." ({$before} bytes)\n";

$name = 'Abanoub';
$afterAssign = memory_get_usage();
echo 'After $name = Abanoub: '.formatBytes($afterAssign)." ({$afterAssign} bytes)\n";
echo 'Delta after assign:    '.formatBytes($afterAssign - $before)."\n";

unset($name);
$afterUnset = memory_get_usage();
echo 'After unset($name):    '.formatBytes($afterUnset)." ({$afterUnset} bytes)\n";
echo 'Delta after unset:     '.formatBytes($afterUnset - $afterAssign)."\n";

echo "\n";
note('Small strings may sit inside already-allocated Zend memory arenas,');
note('so emalloc deltas can look tiny. Peak usage still tracks growth.');
echo 'Peak usage so far:     '.formatBytes(memory_get_peak_usage())."\n";

// Larger allocation makes the effect obvious
echo "\n";
note('Larger string makes allocation/release clearer:');
$beforeLarge = memory_get_usage(true);
$large = str_repeat('A', 2_000_000);
$afterLarge = memory_get_usage(true);
echo 'After 2MB string:      '.formatBytes($afterLarge).' (delta +'.formatBytes($afterLarge - $beforeLarge).")\n";

unset($large);
$afterLargeUnset = memory_get_usage(true);
echo 'After unset(large):    '.formatBytes($afterLargeUnset).' (delta '.formatBytes($afterLargeUnset - $afterLarge).")\n";

echo "\nDone.\n";
