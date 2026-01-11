<?php

namespace Modules\UPF\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\UPF\Models\MaterialType;
use Modules\UPF\Models\UpfPrice;
use Modules\UPF\Services\UpfService;

class UpfServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UpfService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(UpfService::class);
    }

    /**
     * Test renaming a material type cascades through tables
     */
    public function test_it_can_rename_type_across_tables()
    {
        $oldType = 'PLATE';
        $newType = 'PL';
        
        $type = MaterialType::create([
            'type' => $oldType,
            'title' => 'Plate',
            'pkey' => 'MAT_1',
            'is_active' => true
        ]);

        $price = UpfPrice::create([
            'material_type_id' => $type->id,
            'type' => $oldType,
            'size' => '1/2 X 12',
            'pkey' => 'UPF_1',
            'filekey' => 1,
            'orderkey' => 1
        ]);

        $this->service->renameType($oldType, $newType);

        $this->assertDatabaseHas('material_types', ['type' => $newType]);
        $this->assertDatabaseMissing('material_types', ['type' => $oldType]);
        $this->assertDatabaseHas('upf_prices', ['type' => $newType, 'id' => $price->id]);
    }

    /**
     * Test copying a material type generates new unique keys
     */
    public function test_it_can_copy_type_with_all_rules()
    {
        $sourceType = 'PLATE';
        $targetType = 'PLATE_HEAVY';
        
        $type = MaterialType::create([
            'type' => $sourceType,
            'title' => 'Source Type',
            'pkey' => 'MAT_1',
            'is_active' => true
        ]);

        UpfPrice::create([
            'material_type_id' => $type->id,
            'type' => $sourceType,
            'size' => '1 X 12',
            'pkey' => 'UPF_SOURCE',
            'filekey' => 100,
            'orderkey' => 100
        ]);

        $this->service->copyType($sourceType, $targetType);

        $this->assertDatabaseHas('material_types', ['type' => $targetType]);
        $this->assertDatabaseHas('upf_prices', ['type' => $targetType, 'size' => '1 X 12']);
        
        $sourcePrice = UpfPrice::where('type', $sourceType)->first();
        $targetPrice = UpfPrice::where('type', $targetType)->first();

        $this->assertNotNull($targetPrice);
        $this->assertNotEquals($sourcePrice->pkey, $targetPrice->pkey);
        $this->assertNotEquals($sourcePrice->filekey, $targetPrice->filekey);
    }
}
