<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

\Illuminate\Support\Facades\DB::statement('ALTER TABLE complains ADD foto_path VARCHAR(255) NULL;');
\Illuminate\Support\Facades\DB::statement('ALTER TABLE testimonis ADD foto_path VARCHAR(255) NULL, ADD is_anonymous TINYINT(1) DEFAULT 0;');
echo "Database altered successfully.\n";
