<?php

declare(strict_types=1);

use App\Support\OperationalAuditDetailLogGenerator;

try {
    $input = $argv[1] ?? null;
    require __DIR__ . '/../vendor/autoload.php';

    $app = require __DIR__ . '/../bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    $generator = app(OperationalAuditDetailLogGenerator::class);
    $outputPath = $generator->generateFromPath($input);

    echo 'Log detail audit berhasil dibuat.' . PHP_EOL;
    echo 'File: ' . $outputPath . PHP_EOL;
    exit(0);
} catch (\Throwable $throwable) {
    fwrite(STDERR, 'Gagal membuat log detail audit: ' . $throwable->getMessage() . PHP_EOL);
    exit(1);
}
