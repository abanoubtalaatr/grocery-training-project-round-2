<?php

declare(strict_types=1);

/**
 * Part 6 helper notes — Tools overview (runnable checklist).
 *
 * Run: php memory-demos/05-tools-overview.php
 */

require __DIR__.'/helpers.php';

section('Part 6: Observability Tools');

echo <<<'TXT'
PHP built-in (used throughout these demos)
------------------------------------------
  memory_get_usage($real_usage = false)
  memory_get_peak_usage($real_usage = false)
  gc_status()
  gc_collect_cycles()
  gc_enable() / gc_disable()

Xdebug (optional — install extension, then debug in IDE)
--------------------------------------------------------
  1. pecl install xdebug  (or brew/apt package)
  2. Enable in php.ini:
       zend_extension=xdebug
       xdebug.mode=debug,develop
  3. Set breakpoints in these scripts or in MemoryTest command
  4. Inspect:
       - Variables panel (locals, refs)
       - Objects / types
       - Call stack
       - Watches for memory_get_usage()

Laravel Debugbar (optional web UI)
----------------------------------
  composer require barryvdh/laravel-debugbar --dev
  Visit any Blade/web route while logged in (or APP_DEBUG=true)
  Debugbar shows:
       - Memory usage
       - Queries (N+1 risk)
       - Execution time
       - Timeline

Laravel Artisan command in this project
---------------------------------------
  php artisan memory:test --mode=compare
  php artisan memory:test --mode=all
  php artisan memory:test --mode=chunk --chunk=500

TXT;

$xdebugLoaded = extension_loaded('xdebug');
echo 'Xdebug loaded in this CLI: '.($xdebugLoaded ? 'YES' : 'NO')."\n";
echo 'Current memory:            '.formatBytes(memory_get_usage(true))."\n";
echo 'Peak memory:               '.formatBytes(memory_get_peak_usage(true))."\n";
gcSnapshot('current');

echo "\nDone.\n";
