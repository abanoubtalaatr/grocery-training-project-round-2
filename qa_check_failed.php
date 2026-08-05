<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$job = DB::table('failed_jobs')->orderBy('failed_at', 'desc')->first();
if ($job) {
    echo "UUID: " . $job->uuid . "\n";
    echo "Exception:\n" . $job->exception . "\n";
} else {
    echo "No failed jobs found.\n";
}
