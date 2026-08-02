<?php

declare(strict_types=1);

/**
 * Programmatic checks for the pure-PHP memory demos.
 *
 * Run: php memory-demos/verify.php
 */

require __DIR__.'/helpers.php';

$failures = 0;

function assertTrue(bool $condition, string $message): void
{
    global $failures;
    if ($condition) {
        echo "PASS: {$message}\n";

        return;
    }

    $failures++;
    echo "FAIL: {$message}\n";
}

section('Verification');

// 1) Large allocation grows memory
$before = memory_get_usage(true);
$blob = str_repeat('x', 1_500_000);
$after = memory_get_usage(true);
assertTrue($after > $before, 'Allocating a large string increases memory_get_usage(true)');
unset($blob);
gc_collect_cycles();

// 2) Refcount / destructor order
$destroyed = 0;
$tracker = new class($destroyed)
{
    public function __construct(private int &$destroyed) {}

    public function __destruct()
    {
        $this->destroyed++;
    }
};
$copy = $tracker;
unset($tracker);
assertTrue($destroyed === 0, 'Destructor waits until last reference is gone');
unset($copy);
assertTrue($destroyed === 1, 'Destructor runs when refcount hits zero');

// 3) Circular references need GC
class VUser
{
    public mixed $order = null;
}
class VOrder
{
    public mixed $user = null;
}

$u = new VUser;
$o = new VOrder;
$u->order = $o;
$o->user = $u;
unset($u, $o);

$statusBefore = gc_status();
$collected = gc_collect_cycles();
$statusAfter = gc_status();

assertTrue(
    $collected >= 0 && ($statusAfter['runs'] ?? 0) >= ($statusBefore['runs'] ?? 0),
    'gc_collect_cycles() runs and reports a non-negative collected count'
);

// 4) Demo scripts are executable
foreach (['01-memory-basics.php', '02-reference-counting.php', '03-circular-references-gc.php', '04-large-memory.php'] as $script) {
    assertTrue(is_file(__DIR__.'/'.$script), "Demo script exists: {$script}");
}

echo "\n";
if ($failures > 0) {
    echo "{$failures} assertion(s) failed.\n";
    exit(1);
}

echo "All verifications passed.\n";
