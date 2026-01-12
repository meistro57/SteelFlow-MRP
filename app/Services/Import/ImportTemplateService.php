<?php

// app/Services/Import/ImportTemplateService.php

namespace App\Services\Import;

use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportTemplateService
{
    /**
     * Available import template types
     */
    public const TEMPLATE_CUSTOMERS = 'customers';
    public const TEMPLATE_KISS = 'kiss';
    public const TEMPLATE_XSR = 'xsr';
    public const TEMPLATE_UPF = 'upf';

    /**
     * Get template metadata for all available templates
     */
    public function getAvailableTemplates(): array
    {
        return [
            self::TEMPLATE_CUSTOMERS => [
                'name' => 'Customers',
                'description' => 'Import customer records with contact information',
                'filename' => 'customers_import_template.csv',
            ],
            self::TEMPLATE_KISS => [
                'name' => 'KISS Format (BOM)',
                'description' => 'Import assemblies and parts from KISS CAD format',
                'filename' => 'kiss_import_template.csv',
            ],
            self::TEMPLATE_XSR => [
                'name' => 'XSR Format (BOM)',
                'description' => 'Import parts from XSR CAD format',
                'filename' => 'xsr_import_template.csv',
            ],
            self::TEMPLATE_UPF => [
                'name' => 'UPF Material Catalog',
                'description' => 'Import material types, grades, and pricing from FabTrol UPF format',
                'filename' => 'upf_material_catalog_template.csv',
            ],
        ];
    }

    /**
     * Generate a streamed CSV response for the given template type
     */
    public function download(string $templateType): StreamedResponse
    {
        $templates = $this->getAvailableTemplates();

        if (! isset($templates[$templateType])) {
            abort(404, 'Template not found');
        }

        $template = $templates[$templateType];
        $content = $this->generateTemplateContent($templateType);

        return response()->streamDownload(
            function () use ($content): void {
                echo $content;
            },
            $template['filename'],
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $template['filename'] . '"',
            ]
        );
    }

    /**
     * Generate the CSV content for a template type
     */
    protected function generateTemplateContent(string $templateType): string
    {
        return match ($templateType) {
            self::TEMPLATE_CUSTOMERS => $this->generateCustomersTemplate(),
            self::TEMPLATE_KISS => $this->generateKissTemplate(),
            self::TEMPLATE_XSR => $this->generateXsrTemplate(),
            self::TEMPLATE_UPF => $this->generateUpfTemplate(),
            default => '',
        };
    }

    /**
     * Generate the Customers import template CSV
     */
    protected function generateCustomersTemplate(): string
    {
        $rows = [
            // Header row
            ['code', 'name', 'address_1', 'address_2', 'city', 'state', 'zip', 'country', 'phone', 'email', 'notes', 'is_active'],
            // Example rows with sample data
            ['CUST001', 'Acme Steel Corporation', '123 Industrial Blvd', 'Suite 100', 'Houston', 'TX', '77001', 'USA', '713-555-0100', 'orders@acmesteel.com', 'Primary steel supplier', 'true'],
            ['CUST002', 'Metro Construction LLC', '456 Commerce Drive', '', 'Dallas', 'TX', '75201', 'USA', '214-555-0200', 'purchasing@metroconstruction.com', 'General contractor', 'true'],
            ['CUST003', 'Pacific Fabricators Inc', '789 Harbor Way', 'Building B', 'Long Beach', 'CA', '90802', 'USA', '562-555-0300', 'info@pacificfab.com', 'West coast fabrication partner', 'true'],
        ];

        return $this->arrayToCsv($rows);
    }

    /**
     * Generate the KISS format import template CSV
     *
     * KISS format uses two record types:
     * - SHP: Assembly/shipping records
     * - DET: Detail/part records
     */
    protected function generateKissTemplate(): string
    {
        $rows = [
            // Assembly records (SHP type)
            // Format: Type, Mark, Qty, Description, WeightEach, WeightTotal, Remark, Drawing
            ['SHP', 'A1', '2', 'BEAM ASSEMBLY', '1250.5', '2501.0', 'Main beam', 'DWG-001'],
            ['SHP', 'A2', '4', 'COLUMN ASSEMBLY', '850.0', '3400.0', 'Corner column', 'DWG-002'],
            ['SHP', 'B1', '1', 'BRACE ASSEMBLY', '425.25', '425.25', 'Lateral brace', 'DWG-003'],

            // Part records (DET type)
            // Format: Type, AssemblyMark, PartMark, QtyEach, Description, Material, Size, Length, WeightEach, WeightTotal
            ['DET', 'A1', 'P1', '2', 'MAIN BEAM', 'A36', 'W24X76', '20-0-0', '380.0', '760.0'],
            ['DET', 'A1', 'P2', '4', 'STIFFENER PLATE', 'A36', 'PL1/2X6', '1-6-0', '15.3', '61.2'],
            ['DET', 'A1', 'P3', '8', 'GUSSET PLATE', 'A572-50', 'PL3/8X8', '0-8-0', '8.5', '68.0'],
            ['DET', 'A2', 'P4', '1', 'COLUMN', 'A992', 'W14X48', '15-0-0', '720.0', '720.0'],
            ['DET', 'A2', 'P5', '2', 'BASE PLATE', 'A36', 'PL1X14', '1-2-0', '33.25', '66.5'],
            ['DET', 'B1', 'P6', '2', 'BRACE MEMBER', 'A500-B', 'HSS6X6X3/8', '12-6-0', '187.5', '375.0'],
            ['DET', 'B1', 'P7', '4', 'CONNECTION ANGLE', 'A36', 'L4X4X3/8', '0-6-0', '6.0', '24.0'],
        ];

        return $this->arrayToCsv($rows);
    }

    /**
     * Generate the XSR format import template CSV
     *
     * XSR format uses header-based CSV with flexible column names
     */
    protected function generateXsrTemplate(): string
    {
        $rows = [
            // Header row (supports alternate column names shown in comments)
            ['AssemblyMark', 'PartMark', 'Quantity', 'Material', 'Shape', 'Length', 'Weight'],
            // Alternate headers: Assembly, Mark, Qty, Grade, Size

            // Example data rows
            ['A1', 'P1', '2', 'A36', 'W24X76', '240', '380.0'],
            ['A1', 'P2', '4', 'A36', 'PL1/2X6', '18', '15.3'],
            ['A1', 'P3', '8', 'A572-50', 'PL3/8X8', '8', '8.5'],
            ['A2', 'P4', '1', 'A992', 'W14X48', '180', '720.0'],
            ['A2', 'P5', '2', 'A36', 'PL1X14', '14', '33.25'],
            ['B1', 'P6', '2', 'A500-B', 'HSS6X6X3/8', '150', '187.5'],
            ['B1', 'P7', '4', 'A36', 'L4X4X3/8', '6', '6.0'],
            ['', 'P8', '10', 'A36', 'PL1/4X4', '12', '4.1'],  // Part without assembly (DETACHED)
        ];

        return $this->arrayToCsv($rows);
    }

    /**
     * Generate the UPF Material Catalog import template CSV
     *
     * UPF format imports material types, grades, and pricing information
     */
    protected function generateUpfTemplate(): string
    {
        $rows = [
            // Header row
            ['TYPE', 'TYPEDESC', 'GRADE', 'SIZE', 'PRICE', 'PUNIT', 'THICK', 'WTFT', 'PKEY', 'FILEKEY', 'ORDERKEY'],
            // Alternate headers: TYPE_NAME, DESCRIPTION, GRADE_NAME, UNIT_PRICE, UNIT, THICKNESS, WEIGHT_FT

            // Wide Flange Beams
            ['W', 'Wide Flange', 'A992', 'W24X76', '0.85', 'LB', '0.44', '76', '', '', ''],
            ['W', 'Wide Flange', 'A992', 'W24X55', '0.85', 'LB', '0.395', '55', '', '', ''],
            ['W', 'Wide Flange', 'A992', 'W14X48', '0.87', 'LB', '0.34', '48', '', '', ''],
            ['W', 'Wide Flange', 'A992', 'W14X30', '0.87', 'LB', '0.27', '30', '', '', ''],
            ['W', 'Wide Flange', 'A36', 'W12X26', '0.82', 'LB', '0.23', '26', '', '', ''],

            // Channels
            ['C', 'Channel', 'A36', 'C12X20.7', '0.90', 'LB', '0.282', '20.7', '', '', ''],
            ['C', 'Channel', 'A36', 'C10X15.3', '0.90', 'LB', '0.24', '15.3', '', '', ''],

            // Angles
            ['L', 'Angle', 'A36', 'L4X4X3/8', '0.88', 'LB', '0.375', '9.8', '', '', ''],
            ['L', 'Angle', 'A36', 'L3X3X1/4', '0.88', 'LB', '0.25', '4.9', '', '', ''],

            // HSS (Hollow Structural Sections)
            ['HSS', 'HSS Tube/Pipe', 'A500-B', 'HSS6X6X3/8', '1.05', 'LB', '0.375', '27.48', '', '', ''],
            ['HSS', 'HSS Tube/Pipe', 'A500-B', 'HSS4X4X1/4', '1.05', 'LB', '0.25', '12.21', '', '', ''],

            // Plates
            ['PL', 'Plate', 'A36', 'PL1/4X48', '0.75', 'LB', '0.25', '10.2', '', '', ''],
            ['PL', 'Plate', 'A36', 'PL3/8X48', '0.75', 'LB', '0.375', '15.3', '', '', ''],
            ['PL', 'Plate', 'A36', 'PL1/2X48', '0.75', 'LB', '0.5', '20.4', '', '', ''],
            ['PL', 'Plate', 'A572-50', 'PL1X48', '0.78', 'LB', '1.0', '40.8', '', '', ''],
        ];

        return $this->arrayToCsv($rows);
    }

    /**
     * Convert array of rows to CSV string
     */
    protected function arrayToCsv(array $rows): string
    {
        $output = fopen('php://temp', 'r+');

        foreach ($rows as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
