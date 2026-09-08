<?php

namespace Tests\Feature;

use App\Models\Part;
use App\Models\Project;
use App\Services\BOMExtensionService;
use App\Services\Import\SmlxImporter;
use App\Services\Pricing\WeightCalculator;
use App\Services\ReferenceDataService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use ZipArchive;

class SmlxImportTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Build a minimal but realistic SMLX zip container on disk.
     */
    protected function buildSmlxFixture(): string
    {
        $xml = $this->buildCgrexXml();

        $zipPath = sys_get_temp_dir().'/smlx_fixture_'.bin2hex(random_bytes(6)).'.zip';
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('Contents/model.xml', $xml);
        $zip->addFromString('DSTV/NC/p1.nc1', "ST\nheader\np1\n");
        $zip->addFromString('DSTV/NC/p3.nc1', "ST\nheader\np3\n");
        $zip->close();

        return $zipPath;
    }

    /**
     * Build a CGREX XML document with two assemblies and three parts.
     */
    protected function buildCgrexXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<Document>
  <Object class="CGREXMaterial" version="1"><Object class="CGREXObject" version="2"><Members version="2"><m_nID long="1"/></Members></Object><Members version="1"><m_strName string="ASTMA36"/></Members></Object>
  <Object class="CGREXMaterial" version="1"><Object class="CGREXObject" version="2"><Members version="2"><m_nID long="2"/></Members></Object><Members version="1"><m_strName string="ASTMA992"/></Members></Object>
  <Object class="CGREXSection" version="1"><Object class="CGREXObject" version="2"><Members version="2"><m_nID long="3"/></Members></Object><Members version="1"><m_strName string="W16x45"/></Members></Object>
  <Object class="CGREXSection" version="1"><Object class="CGREXObject" version="2"><Members version="2"><m_nID long="4"/></Members></Object><Members version="1"><m_strName string="HSS 4X4X3/8"/></Members></Object>
  <Object class="CGREXMainPart" version="1"><Object class="CGREXObject" version="2"><Members version="2"><m_nID long="100"/></Members></Object><Members version="1"><m_pMainObject class="CGREXObjectReference" version="1"><Members version="1"><m_nID long="10"/></Members></m_pMainObject></Members></Object>
  <Object class="CGREXMainPart" version="1"><Object class="CGREXObject" version="2"><Members version="2"><m_nID long="200"/></Members></Object><Members version="1"><m_pMainObject class="CGREXObjectReference" version="1"><Members version="1"><m_nID long="30"/></Members></m_pMainObject></Members></Object>
  <Object class="CGREXBeam" version="2"><Object class="CGREXLine" version="1"><Object class="CGREXMember" version="1"><Object class="CGREXObject" version="2"><Members version="2"><m_nID long="10"/></Members></Object><Members version="1"><m_strName string="W16x45"/><m_strMark string="A1"/><m_pMaterial class="CGREXObjectReference" version="1"><Members version="1"><m_nID long="2"/></Members></m_pMaterial><m_bIsMainPart long="1"/><m_dfWeight double="100"/><m_pMainPartEx class="CGREXObjectReference" version="1"><Members version="1"><m_nID long="100"/></Members></m_pMainPartEx></Members></Object><Members version="1"><m_pSectionStart class="CGREXObjectReference" version="1"><Members version="1"><m_nID long="3"/></Members></m_pSectionStart></Members></Object><Members version="2"><m_strSinglePartMark string="p1"/><m_Length double="1.5"/><m_ExactWeight double="100"/></Members></Object>
  <Object class="CGREXPlate" version="1"><Object class="CGREXSurface" version="1"><Object class="CGREXMember" version="1"><Object class="CGREXObject" version="2"><Members version="2"><m_nID long="20"/></Members></Object><Members version="1"><m_strName string="PL 1/4"/><m_strMark string="A1"/><m_pMaterial class="CGREXObjectReference" version="1"><Members version="1"><m_nID long="1"/></Members></m_pMaterial><m_bIsMainPart long="0"/><m_dfWeight double="5"/><m_pMainPartEx class="CGREXObjectReference" version="1"><Members version="1"><m_nID long="100"/></Members></m_pMainPartEx></Members></Object><Members version="1"><m_dfThickness double="0.25"/></Members></Object><Members version="1"><m_strSinglePartMark string="p2"/><m_Length double="0.3"/><m_ExactWeight double="5"/></Members></Object>
  <Object class="CGREXColumn" version="2"><Object class="CGREXLine" version="1"><Object class="CGREXMember" version="1"><Object class="CGREXObject" version="2"><Members version="2"><m_nID long="30"/></Members></Object><Members version="1"><m_strName string="HSS 4X4X3/8"/><m_strMark string="A2"/><m_pMaterial class="CGREXObjectReference" version="1"><Members version="1"><m_nID long="1"/></Members></m_pMaterial><m_bIsMainPart long="1"/><m_dfWeight double="40"/><m_pMainPartEx class="CGREXObjectReference" version="1"><Members version="1"><m_nID long="200"/></Members></m_pMainPartEx></Members></Object><Members version="1"><m_pSectionStart class="CGREXObjectReference" version="1"><Members version="1"><m_nID long="4"/></Members></m_pSectionStart></Members></Object><Members version="2"><m_strSinglePartMark string="p3"/><m_Length double="3"/><m_ExactWeight double="40"/></Members></Object>
</Document>
XML;
    }

    public function test_it_imports_assemblies_and_parts_from_smlx_zip(): void
    {
        $zipPath = $this->buildSmlxFixture();

        try {
            $project = Project::factory()->create();

            $importer = new SmlxImporter(
                new ReferenceDataService,
                new WeightCalculator,
                new BOMExtensionService(new WeightCalculator),
            );

            $result = $importer->import($zipPath, $project);

            $this->assertTrue($result, 'SMLX import should succeed. Errors: '.implode('; ', $importer->getErrors()));
            $this->assertSame([], $importer->getErrors());

            $this->assertSame(2, $project->assemblies()->count());
            $this->assertSame(3, $project->parts()->count());

            $this->assertEqualsCanonicalizing(['A1', 'A2'], $project->assemblies()->pluck('mark')->all());

            $part1 = $project->parts()->where('part_mark', 'p1')->first();
            $this->assertNotNull($part1);
            $this->assertSame('W16x45', $part1->size_imperial);
            $this->assertSame('A992', $part1->grade);
            $this->assertTrue((bool) $part1->is_main_member);
            $this->assertTrue((bool) $part1->nc_data_available);

            $part2 = $project->parts()->where('part_mark', 'p2')->first();
            $this->assertNotNull($part2);
            $this->assertSame('PL 1/4', $part2->size_imperial);
            $this->assertSame('A36', $part2->grade);
            $this->assertFalse((bool) $part2->is_main_member);
            $this->assertFalse((bool) $part2->nc_data_available);

            $part3 = $project->parts()->where('part_mark', 'p3')->first();
            $this->assertNotNull($part3);
            $this->assertSame('A2', $part3->assembly->mark);
            $this->assertSame('A36', $part3->grade);
            $this->assertTrue((bool) $part3->is_main_member);
            $this->assertTrue((bool) $part3->nc_data_available);
        } finally {
            @unlink($zipPath);
        }
    }

    public function test_it_skips_missing_files(): void
    {
        $importer = new SmlxImporter(
            new ReferenceDataService,
            new WeightCalculator,
            new BOMExtensionService(new WeightCalculator),
        );

        $result = $importer->import('/nonexistent/file.smlx', new Project);

        $this->assertFalse($result);
        $this->assertContains('File not found: /nonexistent/file.smlx', $importer->getErrors());
    }
}
