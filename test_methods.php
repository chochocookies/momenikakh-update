<?php
use Illuminate\Foundation\Configuration\Middleware;
$reflection = new ReflectionClass(Middleware::class);
foreach ($reflection->getMethods() as $method) {
    echo $method->getName() . PHP_EOL;
}
?>