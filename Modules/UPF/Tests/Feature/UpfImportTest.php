<?php

namespace Modules\UPF\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\UPF\Services\FabTrolImporter;
use Tests\TestCase;

class UpfImportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test full import cycle from CSV to database
     */
    public function test_it_imports_upf_csv_correctly()
    {
        // 1. Create mock CSV content
        $csvContent = "TYPE,TYPEDESC,GRADE,SIZE,PRICE,PUNIT,THICK,WTFT\n".
                     "PLATE,Plate Material,A36,1/2,0.45,LB,0.5,20.42\n".
                     'BEAM,Wide Flange,A992,W14X22,0.65,LB,0,22.0';

        $tempFile = tempnam(sys_get_temp_dir(), 'upf_test');
        file_put_contents($tempFile, $csvContent);

        // 2. Run importer
        $importer = app(FabTrolImporter::class);
        $results = $importer->importUPF($tempFile);

        // 3. Assertions
        $this->assertEquals(2, $results['types'], 'Should import 2 types');
        $this->assertEquals(2, $results['grades'], 'Should import 2 grades');
        $this->assertEquals(2, $results['prices'], 'Should import 2 price records');
        $this->assertCount(0, $results['errors'], 'Should have no errors');

        $this->assertDatabaseHas('material_types', ['type' => 'PLATE']);
        $this->assertDatabaseHas('material_grades', ['grade_name' => 'A36']);
        $this->assertDatabaseHas('upf_prices', [
            'type' => 'BEAM',
            'size' => 'W14X22',
            'unit_price' => 0.65,
        ]);

        unlink($tempFile);
    }
}
