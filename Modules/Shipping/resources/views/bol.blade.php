<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bill of Lading - {{ $load->load_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }
        .section {
            margin-bottom: 15px;
            border: 1px solid #333;
            padding: 10px;
        }
        .section-title {
            font-weight: bold;
            font-size: 13px;
            background-color: #f0f0f0;
            padding: 5px;
            margin: -10px -10px 10px -10px;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            font-weight: bold;
            width: 30%;
            padding: 3px 0;
        }
        .info-value {
            display: table-cell;
            padding: 3px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #333;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .totals {
            margin-top: 15px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 2px solid #333;
        }
        .signature-section {
            margin-top: 30px;
            display: table;
            width: 100%;
        }
        .signature-box {
            display: table-cell;
            width: 48%;
            border: 1px solid #333;
            padding: 10px;
            min-height: 80px;
        }
        .signature-box:first-child {
            margin-right: 4%;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Bill of Lading</h1>
        <p style="margin: 5px 0;">STRAIGHT BILL OF LADING - NOT NEGOTIABLE</p>
    </div>

    <!-- Load Information -->
    <div class="section">
        <div class="section-title">LOAD INFORMATION</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Load Number:</div>
                <div class="info-value">{{ $load->load_number }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">BOL Number:</div>
                <div class="info-value">{{ $load->bol_number ?? 'TBD' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date:</div>
                <div class="info-value">{{ $load->ship_date ? date('m/d/Y', strtotime($load->ship_date)) : date('m/d/Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Project:</div>
                <div class="info-value">{{ $load->project->job_number }} - {{ $load->project->name }}</div>
            </div>
        </div>
    </div>

    <!-- Shipper Information -->
    <div class="section">
        <div class="section-title">SHIPPER (FROM)</div>
        <div style="padding: 5px;">
            <p><strong>SteelFlow Manufacturing</strong></p>
            <p>[Your Company Address]</p>
            <p>[City, State ZIP]</p>
            <p>Phone: [Your Phone]</p>
        </div>
    </div>

    <!-- Consignee Information -->
    <div class="section">
        <div class="section-title">CONSIGNEE (TO)</div>
        <div style="padding: 5px;">
            <p><strong>{{ $load->project->customer->name ?? 'Customer Name' }}</strong></p>
            @if($load->destination)
                <p>{{ $load->destination }}</p>
            @else
                <p>{{ $load->project->customer->address_1 ?? '[Address]' }}</p>
                <p>{{ $load->project->customer->city ?? '[City]' }}, {{ $load->project->customer->state ?? '[State]' }} {{ $load->project->customer->zip ?? '[ZIP]' }}</p>
            @endif
        </div>
    </div>

    <!-- Carrier Information -->
    <div class="section">
        <div class="section-title">CARRIER INFORMATION</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Carrier:</div>
                <div class="info-value">{{ $load->carrier ?? '[Carrier Name]' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Truck Number:</div>
                <div class="info-value">{{ $load->truck_number ?? '[Truck #]' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Trailer Number:</div>
                <div class="info-value">{{ $load->trailer_number ?? '[Trailer #]' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Driver Name:</div>
                <div class="info-value">{{ $load->driver_name ?? '[Driver Name]' }}</div>
            </div>
        </div>
    </div>

    <!-- Items/Assemblies Table -->
    <div class="section">
        <div class="section-title">ITEMS / SHIPMENT CONTENTS</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 20%;">Assembly Mark</th>
                    <th style="width: 40%;">Description</th>
                    <th style="width: 10%; text-align: center;">Quantity</th>
                    <th style="width: 15%; text-align: right;">Weight Each</th>
                    <th style="width: 15%; text-align: right;">Total Weight</th>
                </tr>
            </thead>
            <tbody>
                @forelse($load->items as $item)
                    <tr>
                        <td>{{ $item->assemblyInstance->assembly->mark ?? 'N/A' }}</td>
                        <td>{{ $item->assemblyInstance->assembly->description ?? '-' }}</td>
                        <td style="text-align: center;">{{ $item->quantity ?? 1 }}</td>
                        <td style="text-align: right;">{{ number_format($item->assemblyInstance->assembly->weight_each_lbs ?? 0, 0) }} lbs</td>
                        <td style="text-align: right;">{{ number_format(($item->assemblyInstance->assembly->weight_each_lbs ?? 0) * ($item->quantity ?? 1), 0) }} lbs</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px; color: #999;">
                            No items loaded
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Totals -->
    <div class="totals">
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label" style="width: 70%; text-align: right;">TOTAL PIECES:</div>
                <div class="info-value" style="font-weight: bold; text-align: right;">{{ $totalPieces }}</div>
            </div>
            <div class="info-row">
                <div class="info-label" style="width: 70%; text-align: right;">TOTAL WEIGHT:</div>
                <div class="info-value" style="font-weight: bold; text-align: right;">{{ number_format($totalWeight, 0) }} lbs</div>
            </div>
        </div>
    </div>

    <!-- Special Instructions -->
    @if($load->notes)
    <div class="section">
        <div class="section-title">SPECIAL INSTRUCTIONS / NOTES</div>
        <div style="padding: 5px;">
            {{ $load->notes }}
        </div>
    </div>
    @endif

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <p><strong>SHIPPER SIGNATURE</strong></p>
            <div style="margin-top: 40px; border-top: 1px solid #333; padding-top: 5px;">
                <p style="margin: 0;">Signature</p>
            </div>
            <div style="margin-top: 10px;">
                <p style="margin: 0;">Date: _________________</p>
            </div>
        </div>
        <div style="display: table-cell; width: 4%;"></div>
        <div class="signature-box">
            <p><strong>DRIVER/CARRIER SIGNATURE</strong></p>
            <div style="margin-top: 40px; border-top: 1px solid #333; padding-top: 5px;">
                <p style="margin: 0;">Signature</p>
            </div>
            <div style="margin-top: 10px;">
                <p style="margin: 0;">Date: _________________</p>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>This is a computer-generated document. Generated on {{ date('m/d/Y H:i:s') }}</p>
        <p>SteelFlow MRP - Manufacturing Resource Planning System</p>
    </div>
</body>
</html>
