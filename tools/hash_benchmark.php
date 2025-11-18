<?php
// Simple benchmark for password_hash timing using project env
require_once __DIR__ . '/../bootstrap.php';

$iters = (int)($argv[1] ?? 3);
$cost = (int) env('PASSWORD_COST', 10);
$plain = bin2hex(random_bytes(16));

echo "Mitihub password_hash benchmark\n";
echo "Iterations: {$iters}, cost={$cost}\n";
$times = [];
for ($i = 0; $i < $iters; $i++) {
    $t0 = microtime(true);
    password_hash($plain, PASSWORD_DEFAULT, ['cost' => $cost]);
    $t = microtime(true) - $t0;
    $times[] = $t;
    printf("iter %d: %.3fs\n", $i+1, $t);
}
$avg = array_sum($times) / count($times);
printf("average: %.3fs\n", $avg);

if ($avg > 1.0) {
    echo "NOTE: average hashing time > 1s. Consider lowering PASSWORD_COST in .env for local dev.\n";
}
