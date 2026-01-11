<?php

namespace Modules\UPF\Services;

use Illuminate\Support\Facades\DB;
use Modules\UPF\Models\AutoHandling;
use Modules\UPF\Models\LaborRate;
use Modules\UPF\Models\MaterialGrade;
use Modules\UPF\Models\MaterialType;
use Modules\UPF\Models\UpfPrice;
use Modules\UPF\Models\UpfPurchaseOrderItem;
use Modules\UPF\Models\UpfStockItem;

class UpfService
{
    public function __construct(protected KeyGenerationService $keyGen) {}

    public function renameType(string $oldType, string $newType): void
    {
        DB::transaction(function () use ($oldType, $newType) {
            MaterialType::where('type', $oldType)->update(['type' => $newType]);
            MaterialGrade::where('type', $oldType)->update(['type' => $newType]);
            UpfPrice::where('type', $oldType)->update(['type' => $newType]);
            LaborRate::where('type', $oldType)->update(['type' => $newType]);
            AutoHandling::where('type', $oldType)->update(['type' => $newType]);
            UpfStockItem::where('type', $oldType)->update(['type' => $newType]);
            UpfPurchaseOrderItem::where('type', $oldType)->update(['type' => $newType]);

            DB::table('materials')->where('type', $oldType)->update(['type' => $newType]);
            DB::table('stock_items')->where('type', $oldType)->update(['type' => $newType]);
            DB::table('purchase_order_lines')->where('type', $oldType)->update(['type' => $newType]);
            DB::table('nestings')->where('material_type', $oldType)->update(['material_type' => $newType]);
        });
    }

    public function copyType(string $sourceType, string $targetType): void
    {
        DB::transaction(function () use ($sourceType, $targetType) {
            $sourceModel = MaterialType::where('type', $sourceType)->firstOrFail();
            $targetModel = $sourceModel->replicate();
            $targetModel->type = $targetType;
            $targetModel->pkey = $this->keyGen->generateUniquePKey('material_types', 'MAT');
            $targetModel->save();

            foreach (MaterialGrade::where('type', $sourceType)->get() as $grade) {
                $newGrade = $grade->replicate();
                $newGrade->material_type_id = $targetModel->id;
                $newGrade->type = $targetType;
                $newGrade->pkey = $this->keyGen->generateUniquePKey('material_grades', 'GRD');
                $newGrade->save();
            }

            foreach (UpfPrice::where('type', $sourceType)->get() as $price) {
                $newPrice = $price->replicate();
                $newPrice->material_type_id = $targetModel->id;
                $newPrice->type = $targetType;
                $newPrice->pkey = $this->keyGen->generateUniquePKey('upf_prices', 'UPF');
                $newPrice->filekey = $this->keyGen->getNextFileKey();
                $newPrice->orderkey = $this->keyGen->getNextOrderKey();
                $newPrice->save();
            }
        });
    }
}
