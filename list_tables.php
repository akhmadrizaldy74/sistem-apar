<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = \Illuminate\Support\Facades\Schema::getTables();
foreach ($tables as $table) {
    $tableArray = (array)$table;
    $tableName = reset($tableArray);
    echo $tableName . "\n";
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing($tableName);
    echo "  Columns: " . implode(', ', $columns) . "\n\n";
}
