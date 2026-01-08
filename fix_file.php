<?php
$file = 'Modules/Inventory/Http/Controllers/InventoryController.php';
$content = file_get_contents($file);
$content = str_replace('ModulesInventoryHttpControllers', 'Modules\Inventory\Http\Controllers', $content);
$content = str_replace('AppHttpControllersController', 'App\Http\Controllers\Controller', $content);
$content = str_replace('IlluminateHttpRedirectResponse', 'Illuminate\Http\RedirectResponse', $content);
$content = str_replace('InertiaInertia', 'Inertia\Inertia', $content);
$content = str_replace('InertiaResponse', 'Inertia\Response', $content);
$content = str_replace('ModulesInventoryHttpRequestsStoreStockItemRequest', 'Modules\Inventory\Http\Requests\StoreStockItemRequest', $content);
$content = str_replace('ModulesInventoryHttpRequestsUpdateStockItemRequest', 'Modules\Inventory\Http\Requests\UpdateStockItemRequest', $content);
$content = str_replace('ModulesInventoryModelsGrade', 'Modules\Inventory\Models\Grade', $content);
$content = str_replace('ModulesInventoryModelsMaterial', 'Modules\Inventory\Models\Material', $content);
$content = str_replace('ModulesInventoryModelsStockItem', 'Modules\Inventory\Models\StockItem', $content);
$content = str_replace('AppModelsProject', 'App\Models\Project', $content);
file_put_contents($file, $content);
