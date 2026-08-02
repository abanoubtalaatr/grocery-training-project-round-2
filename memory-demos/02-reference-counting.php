<?php

declare(strict_types=1);

/**
 * Part 2 — Reference Counting & Object Lifecycle
 *
 * Demonstrates refcount via constructors/destructors and shared references.
 *
 * Run: php memory-demos/02-reference-counting.php
 */

require __DIR__.'/helpers.php';

section('Part 2: Reference Counting');

class User
{
    public function __construct()
    {
        echo "User created\n";
    }

    public function __destruct()
    {
        echo "User destroyed\n";
    }
}

note('Create object → Reference Count = 1');
memorySnapshot('before new User');
$user1 = new User;
memorySnapshot('after $user1 = new User');

echo "\n";
note('$user2 = $user1 → Reference Count = 2 (same object, two variables)');
echo <<<'ASCII'

  $user1 ----\
               User Object
  $user2 ----/

ASCII;
$user2 = $user1;
memorySnapshot('after $user2 = $user1');

echo "\n";
note('unset($user1) → Reference Count = 1 (object still alive)');
unset($user1);
memorySnapshot('after unset($user1)');
echo "(Destructor not called yet — \$user2 still holds a reference)\n";

echo "\n";
note('unset($user2) → Reference Count = 0 → Object destroyed');
unset($user2);
memorySnapshot('after unset($user2)');

echo "\n";
note('Scope exit also drops references:');
function makeAndDrop(): void
{
    $temp = new User;
    echo "(leaving makeAndDrop scope)\n";
}
makeAndDrop();

echo "\nDone.\n";
