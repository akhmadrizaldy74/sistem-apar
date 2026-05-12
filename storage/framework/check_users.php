<?php
$base = dirname(__DIR__, 2);
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$users = Illuminate\Support\Facades\DB::table('users')->whereIn('email', ['admin@gmail.com','teknisi@gmail.com'])->select('email','role','updated_at')->get()->toArray();
var_export($users);
