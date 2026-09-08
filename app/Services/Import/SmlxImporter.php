<?php

// app/Services/Import/SmlxImporter.php

namespace App\Services\Import;

use App\Models\Assembly;
use App\Models\Part;
use App\Models\Project;
use App\Services\BOMExtensionService;
use App\Services\Pricing\WeightCalculator;
use App\Services\ReferenceDataService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Inventory\Models\Grade;
use Modules\Inventory\Models\Material;
use SimpleXMLElement;
use ZipArchive;

class SmlxImporter
{
    protected array $errors = [];

    protected string $operationId;

    protected int $assemblyCount = 0;

    protected int $partCount = 0;

    protected array $materials = [];

    protected array $sections = [];

    protected array $mainParts = [];

    protected array $members = [];

    protected array $dstvMarks = [];

    public function __construct(
        protected ReferenceDataService $referenceData,
        protected WeightCalculator $weightCalculator,
        protected BOMExtensionService $bomExtensionService,
    ) {}

    /**
     * Parse and import an SMLX (CGREX) zip container.
     */
    public function import(string $filePath, Project $project): bool
    {
        $this->errors = [];
        $this->operationId = Str::uuid()->toString();
        $this->assemblyCount = 0;
        $this->partCount = 0;
        $this->materials = [];
        $this->sections = [];
        $this->mainParts = [];
        $this->members = [];
        $this->dstvMarks = [];

        Log::info('Starting SMLX import', [
            'operation_id' => $this->operationId,
            'project_id' => $project->id,
            'file' => $filePath,
        ]);

        if (! file_exists($filePath)) {
            $this->errors[] = "File not found: {$filePath}";
            Log::error('SMLX import failed - file missing', [
                'operation_id' => $this->operationId,
                'project_id' => $project->id,
                'file' => $filePath,
            ]);

            return false;
        }

        $tempDir = $this->extractZip($filePath);
        if ($tempDir === null) {
            return false;
        }

        try {
            if (! $this->parseArchive($tempDir, $project)) {
                return false;
            }

            $success = DB::transaction(function () use ($project): bool {
                $this->persist($project);

                return true;
            });

            if ($success) {
                $this->bomExtensionService->extendProject($project);
                Log::info('SMLX import completed', [
                    'operation_id' => $this->operationId,
                    'project_id' => $project->id,
                    'assemblies_processed' => $this->assemblyCount,
                    'parts_processed' => $this->partCount,
                    'errors' => $this->errors,
                ]);
            } else {
                Log::error('SMLX import transaction failed', [
                    'operation_id' => $this->operationId,
                    'project_id' => $project->id,
                    'errors' => $this->errors,
                ]);
            }

            return $success;
        } finally {
            $this->removeDirectory($tempDir);
        }
    }

    /**
     * Extract the SMLX zip to a temporary directory.
     */
    protected function extractZip(string $filePath): ?string
    {
        $zip = new ZipArchive;
        $open = $zip->open($filePath);

        if ($open !== true) {
            $this->errors[] = "Unable to open SMLX archive: {$filePath}";
            Log::error('SMLX import failed - cannot open zip', [
                'operation_id' => $this->operationId,
                'file' => $filePath,
                'zip_error' => $open,
            ]);

            return null;
        }

        $tempDir = sys_get_temp_dir().'/smlx_'.Str::uuid()->toString();
        if (! @mkdir($tempDir, 0777, true) && ! is_dir($tempDir)) {
            $this->errors[] = 'Unable to create temporary extraction directory.';
            $zip->close();

            return null;
        }

        if (! $zip->extractTo($tempDir)) {
            $this->errors[] = 'Unable to extract SMLX archive contents.';
            $zip->close();
            $this->removeDirectory($tempDir);

            return null;
        }

        $zip->close();

        return $tempDir;
    }

    /**
     * Locate and parse the CGREX XML and DSTV NC files inside the archive.
     */
    protected function parseArchive(string $tempDir, Project $project): bool
    {
        $xmlFiles = glob($tempDir.'/Contents/*.xml') ?: [];

        if (empty($xmlFiles)) {
            $this->errors[] = 'SMLX archive is missing a CGREX XML file under Contents/.';
            Log::error('SMLX import failed - missing CGREX XML', [
                'operation_id' => $this->operationId,
                'project_id' => $project->id,
            ]);

            return false;
        }

        $this->dstvMarks = $this->collectDstvMarks($tempDir);

        if (! $this->parseXml($xmlFiles[0])) {
            return false;
        }

        return true;
    }

    /**
     * Collect the set of DSTV machine part marks present in the archive.
     */
    protected function collectDstvMarks(string $tempDir): array
    {
        $marks = [];
        $ncFiles = $this->findFiles($tempDir.'/DSTV', '/\.nc1$/i');

        foreach ($ncFiles as $file) {
            $mark = basename($file, '.nc1');
            if ($mark !== '') {
                $marks[$mark] = true;
            }
        }

        return $marks;
    }

    /**
     * Parse the CGREX XML model into material/section/main-part/member indexes.
     */
    protected function parseXml(string $xmlPath): bool
    {
        $contents = file_get_contents($xmlPath);
        if ($contents === false) {
            $this->errors[] = 'Unable to read the CGREX XML file.';
            Log::error('SMLX import failed - unreadable XML', [
                'operation_id' => $this->operationId,
                'file' => $xmlPath,
            ]);

            return false;
        }

        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($contents);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($xml === false) {
            $this->errors[] = 'Unable to parse the CGREX XML file.';
            Log::error('SMLX import failed - invalid XML', [
                'operation_id' => $this->operationId,
                'file' => $xmlPath,
            ]);

            return false;
        }

        foreach ($xml->xpath('//Object[@class="CGREXMaterial"]') as $node) {
            $this->materials[$this->readValue($node, './/m_nID')] = $this->readValue($node, './/m_strName');
        }

        foreach ($xml->xpath('//Object[@class="CGREXSection"]') as $node) {
            $this->sections[$this->readValue($node, './/m_nID')] = $this->readValue($node, './/m_strName');
        }

        foreach ($xml->xpath('//Object[@class="CGREXMainPart"]') as $node) {
            $this->mainParts[$this->readValue($node, './/Object[@class="CGREXObject"]/Members/m_nID')] =
                $this->readValue($node, './Members/m_pMainObject/Members/m_nID');
        }

        foreach (['CGREXBeam', 'CGREXColumn', 'CGREXPlate'] as $class) {
            foreach ($xml->xpath('//Object[@class="'.$class.'"]') as $node) {
                $this->members[] = $this->parseMember($node, $class);
            }
        }

        return true;
    }

    /**
     * Extract the fields needed for import from a single member object.
     *
     * @return array<string, mixed>
     */
    protected function parseMember(SimpleXMLElement $node, string $class): array
    {
        return [
            'class' => $class,
            'id' => $this->readValue($node, './/Object[@class="CGREXObject"]/Members/m_nID'),
            'name' => $this->readValue($node, './/Object[@class="CGREXMember"]/Members/m_strName'),
            'mark' => $this->readValue($node, './/Object[@class="CGREXMember"]/Members/m_strMark'),
            'part_mark' => $this->readValue($node, './Members/m_strSinglePartMark'),
            'is_main' => $this->readValue($node, './/Object[@class="CGREXMember"]/Members/m_bIsMainPart'),
            'main_part_id' => $this->readValue($node, './/Object[@class="CGREXMember"]/Members/m_pMainPartEx/Members/m_nID'),
            'material_id' => $this->readValue($node, './/Object[@class="CGREXMember"]/Members/m_pMaterial/Members/m_nID'),
            'section_id' => $this->readValue($node, './/Object[@class="CGREXLine"]/Members/m_pSectionStart/Members/m_nID'),
            'weight' => $this->readValue($node, './/Object[@class="CGREXMember"]/Members/m_dfWeight'),
            'exact_weight' => $this->readValue($node, './Members/m_ExactWeight'),
            'length' => $this->readValue($node, './Members/m_Length'),
        ];
    }

    /**
     * Build the assembly/part groups and persist them for the project.
     */
    protected function persist(Project $project): void
    {
        $assemblies = [];
        $parts = [];

        foreach ($this->members as $member) {
            $partMark = trim((string) ($member['part_mark'] ?? ''));
            if ($partMark === '' || $partMark === 'Not defined') {
                continue;
            }

            $assemblyMark = $this->resolveAssemblyMark($member);
            if ($assemblyMark === '') {
                $assemblyMark = 'DETACHED';
            }

            $memberData = $this->resolveMemberData($member);

            $key = $assemblyMark."\0".$partMark;
            if (! isset($parts[$key])) {
                $parts[$key] = [
                    'assembly_mark' => $assemblyMark,
                    'part_mark' => $partMark,
                    'quantity' => 0,
                    'is_main' => false,
                ] + $memberData;
            }

            $parts[$key]['quantity']++;
            if ((string) ($member['is_main'] ?? '') === '1') {
                $parts[$key]['is_main'] = true;
            }

            if (! isset($assemblies[$assemblyMark])) {
                $assemblies[$assemblyMark] = [
                    'mark' => $assemblyMark,
                    'quantity' => 1,
                    'main_member' => null,
                ];
            }

            if ((string) ($member['is_main'] ?? '') === '1' && $assemblies[$assemblyMark]['main_member'] === null) {
                $assemblies[$assemblyMark]['main_member'] = $memberData;
            }
        }

        foreach ($assemblies as $assemblyData) {
            $assembly = $this->createAssembly($project, $assemblyData);
            $this->assemblyCount++;

            foreach ($parts as $partData) {
                if ($partData['assembly_mark'] !== $assemblyData['mark']) {
                    continue;
                }

                $this->createPart($project, $assembly, $partData);
                $this->partCount++;
            }
        }
    }

    /**
     * Resolve the assembly mark for a member, falling back to its main member.
     */
    protected function resolveAssemblyMark(array $member): string
    {
        $mark = trim((string) ($member['mark'] ?? ''));
        if ($mark !== '') {
            return $mark;
        }

        $mainPartId = $member['main_part_id'] ?? null;
        if ($mainPartId !== null && isset($this->mainParts[$mainPartId])) {
            $mainMemberId = $this->mainParts[$mainPartId];
            foreach ($this->members as $candidate) {
                if (($candidate['id'] ?? null) === $mainMemberId) {
                    return trim((string) ($candidate['mark'] ?? ''));
                }
            }
        }

        return '';
    }

    /**
     * Resolve section, grade, type, length and weight for a member.
     *
     * @return array<string, mixed>
     */
    protected function resolveMemberData(array $member): array
    {
        $section = '';
        $sectionId = $member['section_id'] ?? null;
        if ($sectionId !== null && isset($this->sections[$sectionId])) {
            $section = trim((string) $this->sections[$sectionId]);
        }
        if ($section === '') {
            $section = trim((string) ($member['name'] ?? ''));
        }

        $gradeName = '';
        $materialId = $member['material_id'] ?? null;
        if ($materialId !== null && isset($this->materials[$materialId])) {
            $gradeName = $this->normalizeGrade((string) $this->materials[$materialId]);
        }

        $lengthMeters = (float) ($member['length'] ?? 0);
        $weightKg = (float) (($member['exact_weight'] ?? '') !== '' ? $member['exact_weight'] : ($member['weight'] ?? 0));

        return [
            'type' => $this->parseMaterialType($section),
            'size_imperial' => $section,
            'grade' => $gradeName,
            'length' => $lengthMeters * 39.3701,
            'weight_each_kg' => $weightKg,
            'weight_each_lbs' => $weightKg * 2.20462,
        ];
    }

    protected function createAssembly(Project $project, array $data): Assembly
    {
        $main = $data['main_member'] ?? [];

        return Assembly::updateOrCreate(
            [
                'project_id' => $project->id,
                'mark' => $data['mark'],
            ],
            [
                'quantity' => 1,
                'description' => $main['size_imperial'] ?? '',
                'main_member_type' => $main['type'] ?? null,
                'main_member_size' => $main['size_imperial'] ?? null,
                'main_member_grade' => $main['grade'] ?? null,
                'main_member_length' => $main['length'] ?? null,
                'weight_each_lbs' => $main['weight_each_lbs'] ?? 0,
                'weight_each_kg' => $main['weight_each_kg'] ?? 0,
                'total_weight_lbs' => $main['weight_each_lbs'] ?? 0,
                'total_weight_kg' => $main['weight_each_kg'] ?? 0,
            ],
        );
    }

    protected function createPart(Project $project, Assembly $assembly, array $data): Part
    {
        $grade = $this->referenceData->findOrCreateGrade($data['grade'] !== '' ? $data['grade'] : 'A36');
        $material = $this->resolveMaterial($data['type'], $data['size_imperial'], $grade);
        $weightEachKg = (float) $data['weight_each_kg'];
        $weightEachLbs = (float) $data['weight_each_lbs'];
        $quantity = (int) $data['quantity'];

        $part = Part::updateOrCreate(
            [
                'project_id' => $project->id,
                'assembly_id' => $assembly->id,
                'part_mark' => $data['part_mark'],
            ],
            [
                'material_id' => $material->id,
                'type' => $data['type'],
                'size_imperial' => $data['size_imperial'],
                'grade' => $grade->code,
                'length' => (float) $data['length'],
                'quantity' => $quantity,
                'is_main_member' => $data['is_main'],
                'weight_each_lbs' => $weightEachLbs,
                'weight_each_kg' => $weightEachKg,
                'total_weight_lbs' => $weightEachLbs * $quantity,
                'total_weight_kg' => $weightEachKg * $quantity,
                'nc_data_available' => isset($this->dstvMarks[$data['part_mark']]),
            ],
        );

        Log::info('Part processed from SMLX import', [
            'operation_id' => $this->operationId,
            'project_id' => $project->id,
            'part_id' => $part->id,
            'assembly_id' => $assembly->id,
            'part_mark' => $data['part_mark'],
            'quantity' => $quantity,
        ]);

        return $part;
    }

    /**
     * Resolve the material catalog entry for a part, creating it if needed.
     */
    protected function resolveMaterial(string $type, string $size, Grade $grade): Material
    {
        $material = $this->referenceData->findMaterial($type, $size);

        if ($material !== null) {
            return $material;
        }

        return Material::firstOrCreate(
            [
                'type' => $type,
                'size_imperial' => $size,
            ],
            [
                'grade_id' => $grade->id,
                'is_active' => true,
            ],
        );
    }

    /**
     * Read an attribute value from the first node matching an XPath.
     */
    protected function readValue(SimpleXMLElement $node, string $path): ?string
    {
        $results = $node->xpath($path);
        if ($results === false || $results === []) {
            return null;
        }

        $attributes = $results[0]->attributes();
        if ($attributes === null) {
            return null;
        }

        foreach (['string', 'long', 'double', 'name'] as $attribute) {
            if (isset($attributes[$attribute])) {
                return trim((string) $attributes[$attribute]);
            }
        }

        return null;
    }

    protected function normalizeGrade(string $materialName): string
    {
        $materialName = trim($materialName);

        if (preg_match('/^ASTM\s*/i', $materialName)) {
            $materialName = trim(substr($materialName, 4));
        }

        $materialName = preg_replace('/GR([A-Z])/i', ' Gr.$1', $materialName);

        return $materialName ?? '';
    }

    protected function parseMaterialType(string $size): string
    {
        if (str_starts_with($size, 'W')) {
            return 'W';
        }
        if (str_starts_with($size, 'L')) {
            return 'L';
        }
        if (str_starts_with($size, 'C')) {
            return 'C';
        }
        if (str_starts_with($size, 'HSS')) {
            return 'HSS';
        }
        if (str_starts_with($size, 'PL')) {
            return 'PL';
        }

        return 'OT';
    }

    /**
     * Recursively find files matching a pattern under a directory.
     *
     * @return array<int, string>
     */
    protected function findFiles(string $directory, string $pattern): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && preg_match($pattern, $file->getFilename()) === 1) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    protected function removeDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        @rmdir($directory);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
