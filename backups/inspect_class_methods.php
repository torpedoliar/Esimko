<?php
use App\Helpers\GlobalHelper;

echo "--- METHODS IN GlobalHelper ---\n";
$class = new ReflectionClass('App\Helpers\GlobalHelper');
$methods = $class->getMethods(ReflectionMethod::IS_STATIC);

foreach ($methods as $m) {
    echo $m->name . "\n";
}
