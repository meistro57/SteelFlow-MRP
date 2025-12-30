<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Project;
use App\Services\Import\KissImporter;
use App\Services\ReferenceDataService;
use App\Services\Pricing\WeightCalculator;
use Mockery;

class ImportTest extends TestCase
{
    public function test_it_skips_missing_files()
    {
        $refData = Mockery::mock(ReferenceDataService::class);
        $weightCalc = Mockery::mock(WeightCalculator::class);
        $importer = new KissImporter($refData, $weightCalc);
        $project = new Project();

        $result = $importer->import('non_existent_file.kiss', $project);

        $this->assertFalse($result);
        $this->assertContains("File not found: non_existent_file.kiss", $importer->getErrors());
    }
}
