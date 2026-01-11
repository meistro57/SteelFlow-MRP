<?php

namespace Modules\Backup\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery;
use Modules\Backup\Events\BackupCompleted;
use Modules\Backup\Models\Backup;
use Modules\Backup\Services\BackupService;
use Modules\Backup\Services\DatabaseBackupService;
use Modules\Backup\Services\StorageService;
use Tests\TestCase;

class BackupServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $databaseBackupMock;
    protected $storageMock;
    protected $backupService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->databaseBackupMock = Mockery::mock(DatabaseBackupService::class);
        $this->storageMock = Mockery::mock(StorageService::class);
        
        $this->backupService = new BackupService(
            $this->databaseBackupMock,
            $this->storageMock
        );
    }

    public function test_it_can_create_a_database_backup()
    {
        Event::fake([BackupCompleted::class]);

        $path = 'backups/db_backup.sql';
        $size = 1024;

        $this->databaseBackupMock->shouldReceive('backup')
            ->once()
            ->andReturn($path);

        $this->storageMock->shouldReceive('getFileSize')
            ->once()
            ->with($path)
            ->andReturn($size);

        $backup = $this->backupService->create('database', ['notes' => 'Test backup']);

        $this->assertInstanceOf(Backup::class, $backup);
        $this->assertEquals('completed', $backup->status);
        $this->assertEquals($path, $backup->path);
        $this->assertEquals($size, $backup->size);
        $this->assertEquals('Test backup', $backup->notes);

        Event::assertDispatched(BackupCompleted::class, function ($event) use ($backup) {
            return $event->backup->id === $backup->id;
        });
    }

    public function test_it_handles_failed_backups()
    {
        $this->databaseBackupMock->shouldReceive('backup')
            ->once()
            ->andThrow(new \Exception('Backup failed'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Backup failed');

        try {
            $this->backupService->create('database');
        } catch (\Exception $e) {
            $backup = Backup::first();
            $this->assertEquals('failed', $backup->status);
            $this->assertEquals('Backup failed', $backup->error_message);
            throw $e;
        }
    }

    public function test_it_can_delete_a_backup()
    {
        $backup = Backup::create([
            'name' => 'test_backup',
            'type' => 'database',
            'status' => 'completed',
            'path' => 'backups/test.sql',
            'size' => 100
        ]);

        $this->storageMock->shouldReceive('delete')
            ->once()
            ->with('backups/test.sql');

        $result = $this->backupService->delete($backup);

        $this->assertTrue($result);
        $this->assertSoftDeleted($backup);
    }

    public function test_it_can_verify_a_backup()
    {
        $backup = Backup::create([
            'name' => 'test_backup',
            'type' => 'database',
            'status' => 'completed',
            'path' => 'backups/test.sql',
            'size' => 100
        ]);

        $this->storageMock->shouldReceive('exists')
            ->once()
            ->with('backups/test.sql')
            ->andReturn(true);

        $this->storageMock->shouldReceive('getFileSize')
            ->once()
            ->with('backups/test.sql')
            ->andReturn(100);

        $this->databaseBackupMock->shouldReceive('verify')
            ->once()
            ->with('backups/test.sql')
            ->andReturn(true);

        $result = $this->backupService->verify($backup);

        $this->assertTrue($result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
