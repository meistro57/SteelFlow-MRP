<?php

declare(strict_types=1);

namespace Modules\CncData\Engine;

/**
 * Raw result of walking a Phoenix EIA-274 plasma program.
 *
 * Framework-free on purpose: this class and the rest of Engine/ have no Laravel
 * dependencies, so the engine can run from a CLI, a queue worker or a test
 * without booting the app.
 */
final class ParsedProgram
{
    /**
     * @param  list<string>  $lines
     * @param  list<array{type:string,line:int,len:float,feed:float,time:float,kerf:float,th:bool,ar:bool,pts:list<array{0:float,1:float}>}>  $segments
     * @param  list<array{x:float,y:float,line:int,cut:bool,th:bool}>  $pierces
     * @param  list<array{line:int,code:string,message:string}>  $warnings  0-based line numbers
     * @param  array{w:float,h:float}|null  $sheet
     * @param  array{cut:float,mark:float,rapid:float,travel:float}  $lengths
     * @param  array{cut:float,mark:float,rapid:float,travel:float}  $minutes
     * @param  array{minx:float,miny:float,maxx:float,maxy:float}  $bounds
     * @param  array{minx:float,miny:float,maxx:float,maxy:float}  $cutBounds
     */
    public function __construct(
        public readonly array $lines,
        public readonly array $segments,
        public readonly array $pierces,
        public readonly array $warnings,
        public readonly ?array $sheet,
        public readonly string $material,
        public readonly float $thickness,
        public readonly string $process,
        public readonly float $dwellSeconds,
        public readonly array $lengths,
        public readonly array $minutes,
        public readonly array $bounds,
        public readonly array $cutBounds,
        public readonly string $units,
    ) {}
}
