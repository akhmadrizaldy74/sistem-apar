<?php
$base = dirname(__DIR__, 2);
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$route = app('router')->getRoutes()->getByName('order.payment.store');
if (method_exists($route, 'excludedMiddleware')) {
    var_export($route->excludedMiddleware());
} elseif (property_exists($route, 'excludedMiddleware')) {
    var_export($route->excludedMiddleware);
} else {
    echo 'no excluded middleware accessor';
}
