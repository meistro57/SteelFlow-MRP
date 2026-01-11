<?php

namespace Modules\UPF\Console\Commands;

use Illuminate\Console\Command;
use Modules\UPF\Services\FabTrolImporter;

class ImportUpfCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upf:import {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import UPF data from a CSV file (FabTrol export)';

    /**
     * Execute the console command.
     */
    public function handle(FabTrolImporter $importer): int
    {
        $file = $this->argument('file');

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");

            return Command::FAILURE;
        }

        $this->info("Starting UPF import from {$file}...");

        $stats = $importer->importUPF($file);

        $this->table(
            ['Entity', 'Count'],
            [
                ['Material Types', $stats['types']],
                ['Material Grades', $stats['grades']],
                ['UPF Prices', $stats['prices']],
            ],
        );

        if (! empty($stats['errors'])) {
            $this->warn('Completed with '.count($stats['errors']).' errors.');
            if ($this->confirm('Do you want to see the errors?')) {
                foreach (array_slice($stats['errors'], 0, 20) as $error) {
                    $this->error($error);
                }
                if (count($stats['errors']) > 20) {
                    $this->info('... and '.(count($stats['errors']) - 20).' more.');
                }
            }
        } else {
            $this->info('Import completed successfully!');
        }

        return Command::SUCCESS;
    }
}
