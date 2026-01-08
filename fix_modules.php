<?php

$dir = new RecursiveDirectoryIterator(__DIR__ . '/Modules/Inventory');
$iterator = new RecursiveIteratorIterator($dir);

$replacements = [
    'ModulesInventory' => 'Modules\\Inventory',
    'InventoryHttp' => 'Inventory\\Http',
    'HttpControllers' => 'Http\\Controllers',
    'HttpRequests' => 'Http\\Requests',
    'InventoryModels' => 'Inventory\\Models',
    'InventoryProviders' => 'Inventory\\Providers',
    'InventoryServices' => 'Inventory\\Services',
    'DatabaseSeeders' => 'Database\\Seeders',
    'IlluminateSupport' => 'Illuminate\\Support',
    'SupportFacades' => 'Support\\Facades',
    'IlluminateDatabase' => 'Illuminate\\Database',
    'DatabaseEloquent' => 'Database\\Eloquent',
    'EloquentModels' => 'Eloquent\\Models',
    'EloquentRelations' => 'Eloquent\\Relations',
    'EloquentFactories' => 'Eloquent\\Factories',
    'IlluminateHttp' => 'Illuminate\\Http',
    'IlluminateFoundation' => 'Illuminate\\Foundation',
    'InertiaInertia' => 'Inertia\\Inertia',
    'InertiaResponse' => 'Inertia\\Response',
    'AppModels' => 'App\\Models',
    'AppHttp' => 'App\\Http',
    'NwidartModules' => 'Nwidart\\Modules',
    'ModulesTraits' => 'Modules\\Traits',
    'InventoryDatabase' => 'Inventory\\Database',
];

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $original = $content;
        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }
        if ($content !== $original) {
            file_put_contents($file->getPathname(), $content);
            echo "Fixed: " . $file->getPathname() . "\n";
        }
    }
}
