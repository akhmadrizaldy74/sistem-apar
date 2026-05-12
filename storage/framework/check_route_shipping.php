<?php
$base = dirname(__DIR__, 2);
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$r = app('router')->getRoutes()->getByName('order.shipping.quote');
if (!$r) { echo "missing"; exit(1);} 
echo $r->uri() . "|" . implode(',', $r->methods());
