<?php

declare(strict_types=1);

/**
 * Run all pure-PHP memory demos in order.
 *
 * Run: php memory-demos/run-all.php
 */
$scripts = [
    '01-memory-basics.php',
    '02-reference-counting.php',
    '03-circular-references-gc.php',
    '04-large-memory.php',
    '05-tools-overview.php',
];

$failed = 0;

foreach ($scripts as $script) {
    $path = __DIR__.'/'.$script;
    echo "\n>>> Running {$script}\n";

    $command = escapeshellarg(PHP_BINARY).' '.escapeshellarg($path);
    passthru($command, $exitCode);

    if ($exitCode !== 0) {
        fwrite(STDERR, "FAILED: {$script} (exit {$exitCode})\n");
        $failed++;
    }
}

echo "\n".str_repeat('-', 40)."\n";
if ($failed > 0) {
    echo "{$failed} demo(s) failed.\n";
    exit(1);
}

echo "All memory demos completed successfully.\n";
