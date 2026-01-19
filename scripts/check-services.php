<?php

require dirname(__DIR__).'/vendor/autoload.php';
$app = require_once dirname(__DIR__).'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$services = [
    'UPF:UpfService' => \Modules\UPF\Services\UpfService::class,
    'UPF:PricingService' => \Modules\UPF\Services\PricingService::class,
    'UPF:StockService' => \Modules\UPF\Services\StockService::class,
    'UPF:KeyGen' => \Modules\UPF\Services\KeyGenerationService::class,
    'BOMExtensionService' => \App\Services\BOMExtensionService::class,
];

$failed = false;
foreach ($services as $name => $class) {
    try {
        if (! class_exists($class)) {
            echo "[MISSING] $name ($class does not exist)\n";
            $failed = true;

            continue;
        }
        app($class);
        echo "[OK] $name resolved\n";
    } catch (\Exception $e) {
        echo "[FAIL] $name could not be resolved: ".$e->getMessage()."\n";
        $failed = true;
    }
}

if ($failed) {
    exit(1);
}
exit(0);
