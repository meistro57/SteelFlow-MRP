<?php

declare(strict_types=1);

namespace Modules\CncData\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\CncData\Console\AnalyzeCncCommand;
use Modules\CncData\Console\ImportCncCommand;
use Modules\CncData\Engine\PhoenixProgramParser;
use Modules\CncData\Engine\ProgramAnalyzer;

class CncDataServiceProvider extends ServiceProvider
{
    protected string $name = 'CncData';

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'cncdata');
        $this->app->singleton(PhoenixProgramParser::class);
        $this->app->singleton(ProgramAnalyzer::class, fn ($app) => new ProgramAnalyzer($app->make(PhoenixProgramParser::class)));
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->commands([AnalyzeCncCommand::class, ImportCncCommand::class]);
        }
    }
}
