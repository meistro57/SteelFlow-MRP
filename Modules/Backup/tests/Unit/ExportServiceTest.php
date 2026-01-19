<?php

namespace Modules\Backup\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery;
use Modules\Backup\Events\DataExportCompleted;
use Modules\Backup\Models\DataExport;
use Modules\Backup\Services\ExportService;
use Modules\Backup\Services\StorageService;
use Tests\TestCase;

class ExportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $storageMock;

    protected $exportService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storageMock = Mockery::mock(StorageService::class);
        $this->exportService = Mockery::mock(ExportService::class, [$this->storageMock])->makePartial()->shouldAllowMockingProtectedMethods();
    }

    public function test_it_can_create_a_data_export()
    {
        Event::fake([DataExportCompleted::class]);

        $path = 'exports/test_export.csv';
        $size = 512;
        $rowCount = 10;

        $this->exportService->shouldReceive('executeExport')
            ->once()
            ->andReturn($path);

        $this->storageMock->shouldReceive('getFileSize')
            ->once()
            ->with($path)
            ->andReturn($size);

        $this->exportService->shouldReceive('countRows')
            ->once()
            ->with($path, 'csv')
            ->andReturn($rowCount);

        $export = $this->exportService->export('projects', 'csv', ['name' => 'test_export']);

        $this->assertInstanceOf(DataExport::class, $export);
        $this->assertEquals('completed', $export->status);
        $this->assertEquals($path, $export->path);
        $this->assertEquals($size, $export->size);
        $this->assertEquals($rowCount, $export->row_count);

        Event::assertDispatched(DataExportCompleted::class, function ($event) use ($export) {
            return $event->export->id === $export->id;
        });
    }

    public function test_it_handles_failed_exports()
    {
        $this->exportService->shouldReceive('executeExport')
            ->once()
            ->andThrow(new \Exception('Export failed'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Export failed');

        try {
            $this->exportService->export('projects');
        } catch (\Exception $e) {
            $export = DataExport::first();
            $this->assertEquals('failed', $export->status);
            $this->assertEquals('Export failed', $export->error_message);
            throw $e;
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
