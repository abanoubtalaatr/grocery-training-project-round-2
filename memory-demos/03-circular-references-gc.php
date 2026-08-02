<?php

declare(strict_types=1);

/**
 * Part 3 — Circular References & Garbage Collection
 *
 * Reference counting alone cannot free cycles. PHP's GC collects them.
 *
 * Run: php memory-demos/03-circular-references-gc.php
 */

require __DIR__.'/helpers.php';

section('Part 3: Circular References & Garbage Collector');

class CycleUser
{
    public ?CycleOrder $order = null;

    public function __construct(public string $name = 'User')
    {
        echo "CycleUser({$this->name}) created\n";
    }

    public function __destruct()
    {
        echo "CycleUser({$this->name}) destroyed\n";
    }
}

class CycleOrder
{
    public ?CycleUser $user = null;

    public function __construct(public string $id = 'Order')
    {
        echo "CycleOrder({$this->id}) created\n";
    }

    public function __destruct()
    {
        echo "CycleOrder({$this->id}) destroyed\n";
    }
}

note('Build a circular reference:');
echo <<<'ASCII'

  User <------> Order

ASCII;

gc_enable();
gcSnapshot('before cycle');
memorySnapshot('before cycle');

$user = new CycleUser('Abanoub');
$order = new CycleOrder('ORD-1');
$user->order = $order;
$order->user = $user;

memorySnapshot('after cycle created');
gcSnapshot('after cycle created');

echo "\n";
note('unset() both external variables:');
note('Reference counting cannot clean circular references.');
note('Each object still has refcount >= 1 because of the other.');

unset($user);
unset($order);

echo "(No destructors yet — cycle still in memory)\n";
memorySnapshot('after unset both');
gcSnapshot('after unset both');

echo "\n";
note('Force garbage collection with gc_collect_cycles():');
$collected = gc_collect_cycles();
echo "  gc_collect_cycles() collected {$collected} cycle(s)\n";
gcSnapshot('after gc_collect_cycles');
memorySnapshot('after GC');

echo "\n";
note('Without an explicit collect, PHP may run GC later automatically');
note('when root buffer thresholds are reached (see gc_status()).');

echo "\nDone.\n";
