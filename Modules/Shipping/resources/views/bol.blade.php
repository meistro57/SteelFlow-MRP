<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bill of Lading - {{ $load->load_number }}</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 15px;
            line-height: 1.3;
        }
        .header {
            margin-bottom: 15px;
            border-bottom: 3px double #333;
            padding-bottom: 10px;
        }
        .header-content {
            display: table;
            width: 100%;
        }
        .header-left {
            display: table-cell;
            width: 40%;
            vertical-align: top;
        }
        .header-center {
            display: table-cell;
            width: 30%;
            text-align: center;
            vertical-align: middle;
        }
        .header-right {
            display: table-cell;
            width: 30%;
            text-align: right;
            vertical-align: top;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .doc-type {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
            margin-top: 3px;
        }
        .bol-number {
            font-size: 16px;
            font-weight: bold;
            background: #f0f0f0;
            padding: 8px;
            border: 2px solid #333;
            margin-top: 5px;
        }
        .barcode-placeholder {
            font-family: 'Courier New', monospace;
            font-size: 8px;
            letter-spacing: 3px;
            padding: 5px;
            background: #fff;
            border: 1px solid #ccc;
            margin-top: 5px;
        }
        .section {
            margin-bottom: 10px;
            border: 1px solid #333;
        }
        .section-title {
            font-weight: bold;
            font-size: 11px;
            background-color: #333;
            color: #fff;
            padding: 4px 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .section-content {
            padding: 8px;
        }
        .two-column {
            display: table;
            width: 100%;
        }
        .column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 10px;
        }
        .column:last-child {
            padding-right: 0;
            padding-left: 10px;
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
            width: 35%;
            padding: 2px 0;
            color: #555;
        }
        .info-value {
            display: table-cell;
            padding: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #333;
            padding: 5px;
            text-align: left;
        }
        th {
            background-color: #e0e0e0;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        .totals-section {
            margin-top: 10px;
            border: 2px solid #333;
        }
        .totals-row {
            display: table;
            width: 100%;
            background: #f9f9f9;
        }
        .totals-label {
            display: table-cell;
            width: 70%;
            text-align: right;
            padding: 6px 10px;
            font-weight: bold;
            font-size: 11px;
        }
        .totals-value {
            display: table-cell;
            width: 30%;
            text-align: right;
            padding: 6px 10px;
            font-weight: bold;
            font-size: 12px;
            border-left: 1px solid #333;
        }
        .terms-section {
            font-size: 8px;
            background: #f9f9f9;
            padding: 8px;
            margin-top: 10px;
            border: 1px solid #ccc;
        }
        .terms-section h4 {
            margin: 0 0 5px 0;
            font-size: 9px;
            text-transform: uppercase;
        }
        .signature-row {
            display: table;
            width: 100%;
            margin-top: 15px;
        }
        .signature-box {
            display: table-cell;
            width: 32%;
            border: 1px solid #333;
            padding: 10px;
            vertical-align: top;
        }
        .signature-box:nth-child(2) {
            margin: 0 2%;
        }
        .signature-title {
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            border-bottom: 1px solid #333;
            padding-bottom: 3px;
            margin-bottom: 8px;
        }
        .signature-line {
            margin-top: 30px;
            border-top: 1px solid #333;
            padding-top: 3px;
        }
        .checkbox-item {
            margin: 3px 0;
        }
        .checkbox {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #333;
            margin-right: 5px;
            vertical-align: middle;
        }
        .special-handling {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 8px;
            margin-top: 10px;
        }
        .reference-box {
            border: 1px solid #333;
            padding: 5px 8px;
            margin-bottom: 5px;
            background: #fff;
        }
        .reference-label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
        }
        .reference-value {
            font-size: 11px;
            font-weight: bold;
        }
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header">
        <div class="header-content">
            <div class="header-left">
                <div style="font-size: 14px; font-weight: bold;">SteelFlow Manufacturing</div>
                <div style="font-size: 9px; color: #666;">
                    [Your Company Address]<br>
                    [City, State ZIP]<br>
                    Phone: [Your Phone] | Fax: [Your Fax]
                </div>
            </div>
            <div class="header-center">
                <h1>Bill of Lading</h1>
                <div class="doc-type">Straight Bill of Lading - Original - Not Negotiable</div>
            </div>
            <div class="header-right">
                <div class="bol-number">
                    BOL# {{ $load->bol_number ?? $load->load_number }}
                </div>
                <div class="barcode-placeholder">
                    *{{ $load->load_number }}*
                </div>
            </div>
        </div>
    </div>

    <!-- Reference Numbers Row -->
    <div style="display: table; width: 100%; margin-bottom: 10px;">
        <div style="display: table-cell; width: 20%;">
            <div class="reference-box">
                <div class="reference-label">Load Number</div>
                <div class="reference-value">{{ $load->load_number }}</div>
            </div>
        </div>
        <div style="display: table-cell; width: 20%;">
            <div class="reference-box">
                <div class="reference-label">Ship Date</div>
                <div class="reference-value">{{ $load->ship_date ? date('m/d/Y', strtotime($load->ship_date)) : date('m/d/Y') }}</div>
            </div>
        </div>
        <div style="display: table-cell; width: 20%;">
            <div class="reference-box">
                <div class="reference-label">Job Number</div>
                <div class="reference-value">{{ $load->project->job_number ?? 'N/A' }}</div>
            </div>
        </div>
        <div style="display: table-cell; width: 20%;">
            <div class="reference-box">
                <div class="reference-label">Customer PO#</div>
                <div class="reference-value">{{ $load->project->po_number ?? '___________' }}</div>
            </div>
        </div>
        <div style="display: table-cell; width: 20%;">
            <div class="reference-box">
                <div class="reference-label">PRO Number</div>
                <div class="reference-value">{{ $load->pro_number ?? '___________' }}</div>
            </div>
        </div>
    </div>

    <!-- Shipper and Consignee Section -->
    <div class="two-column" style="margin-bottom: 10px;">
        <div class="column">
            <div class="section">
                <div class="section-title">Ship From (Shipper)</div>
                <div class="section-content">
                    <strong style="font-size: 12px;">SteelFlow Manufacturing</strong><br>
                    [Your Company Address]<br>
                    [City, State ZIP]<br>
                    <br>
                    <strong>Contact:</strong> [Contact Name]<br>
                    <strong>Phone:</strong> [Phone Number]
                </div>
            </div>
        </div>
        <div class="column">
            <div class="section">
                <div class="section-title">Ship To (Consignee)</div>
                <div class="section-content">
                    <strong style="font-size: 12px;">{{ $load->project->customer->name ?? 'Customer Name' }}</strong><br>
                    @if($load->destination)
                        {{ $load->destination }}
                    @else
                        {{ $load->project->customer->address_1 ?? '[Address Line 1]' }}<br>
                        @if($load->project->customer->address_2 ?? false)
                            {{ $load->project->customer->address_2 }}<br>
                        @endif
                        {{ $load->project->customer->city ?? '[City]' }}, {{ $load->project->customer->state ?? '[ST]' }} {{ $load->project->customer->zip ?? '[ZIP]' }}
                    @endif
                    <br><br>
                    <strong>Contact:</strong> {{ $load->project->customer->contact_name ?? '___________' }}<br>
                    <strong>Phone:</strong> {{ $load->project->customer->phone ?? '___________' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Third Party / Bill To (if applicable) -->
    <div class="two-column" style="margin-bottom: 10px;">
        <div class="column">
            <div class="section">
                <div class="section-title">Third Party / Bill To</div>
                <div class="section-content">
                    <div class="checkbox-item">
                        <span class="checkbox"></span> Same as Shipper
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox"></span> Same as Consignee
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox"></span> Third Party:
                    </div>
                    <div style="margin-top: 5px; border-top: 1px dashed #ccc; padding-top: 5px;">
                        _________________________________<br>
                        _________________________________<br>
                        _________________________________
                    </div>
                </div>
            </div>
        </div>
        <div class="column">
            <div class="section">
                <div class="section-title">Carrier Information</div>
                <div class="section-content">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-label">Carrier:</div>
                            <div class="info-value">{{ $load->carrier ?? '___________________' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Driver:</div>
                            <div class="info-value">{{ $load->driver_name ?? '___________________' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Truck #:</div>
                            <div class="info-value">{{ $load->truck_number ?? '___________' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Trailer #:</div>
                            <div class="info-value">{{ $load->trailer_number ?? '___________' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Seal #:</div>
                            <div class="info-value">{{ $load->seal_number ?? '___________' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Freight Description / Items Table -->
    <div class="section">
        <div class="section-title">Shipment Contents</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">Item #</th>
                    <th style="width: 15%;">Mark / ID</th>
                    <th style="width: 27%;">Description</th>
                    <th style="width: 8%; text-align: center;">Pcs</th>
                    <th style="width: 12%; text-align: right;">Weight (lbs)</th>
                    <th style="width: 12%; text-align: right;">Weight (kg)</th>
                    <th style="width: 8%; text-align: center;">Class</th>
                    <th style="width: 10%;">NMFC#</th>
                </tr>
            </thead>
            <tbody>
                @php $itemNum = 1; @endphp
                @forelse($load->items as $item)
                    @php
                        $weightEach = $item->assemblyInstance->assembly->weight_each_lbs ?? 0;
                        $quantity = $item->quantity ?? 1;
                        $totalLbs = $weightEach * $quantity;
                        $totalKg = $totalLbs * 0.453592;
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $itemNum++ }}</td>
                        <td style="font-weight: bold;">{{ $item->assemblyInstance->assembly->mark ?? 'N/A' }}</td>
                        <td>{{ $item->assemblyInstance->assembly->description ?? 'Steel Fabrication' }}</td>
                        <td style="text-align: center;">{{ $quantity }}</td>
                        <td style="text-align: right;">{{ number_format($totalLbs, 0) }}</td>
                        <td style="text-align: right;">{{ number_format($totalKg, 0) }}</td>
                        <td style="text-align: center;">{{ $item->freight_class ?? '85' }}</td>
                        <td>{{ $item->nmfc_code ?? '101780' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 20px; color: #999;">
                            No items loaded
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Totals Section -->
    <div class="totals-section">
        <div class="totals-row">
            <div class="totals-label">Total Pieces:</div>
            <div class="totals-value">{{ $totalPieces }}</div>
        </div>
        <div class="totals-row" style="border-top: 1px solid #333;">
            <div class="totals-label">Total Weight (lbs):</div>
            <div class="totals-value">{{ number_format($totalWeight, 0) }} lbs</div>
        </div>
        <div class="totals-row" style="border-top: 1px solid #333;">
            <div class="totals-label">Total Weight (kg):</div>
            <div class="totals-value">{{ number_format($totalWeight * 0.453592, 0) }} kg</div>
        </div>
    </div>

    <!-- Special Handling / Notes -->
    @if($load->notes || $load->special_handling)
    <div class="special-handling">
        <strong style="font-size: 10px;">SPECIAL INSTRUCTIONS / HANDLING REQUIREMENTS:</strong>
        <div style="margin-top: 5px;">
            {{ $load->notes ?? '' }}
            @if($load->special_handling)
                <br><strong>Special Handling:</strong> {{ $load->special_handling }}
            @endif
        </div>
    </div>
    @endif

    <!-- Freight Charges Section -->
    <div class="section" style="margin-top: 10px;">
        <div class="section-title">Freight Charges</div>
        <div class="section-content">
            <div style="display: table; width: 100%;">
                <div style="display: table-cell; width: 50%;">
                    <div class="checkbox-item">
                        <span class="checkbox"></span> <strong>PREPAID</strong> - Shipper pays freight charges
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox"></span> <strong>COLLECT</strong> - Consignee pays freight charges
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox"></span> <strong>THIRD PARTY</strong> - Bill to address above
                    </div>
                </div>
                <div style="display: table-cell; width: 50%; border-left: 1px solid #ccc; padding-left: 10px;">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-label">Freight Charges:</div>
                            <div class="info-value">$___________</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">COD Amount:</div>
                            <div class="info-value">$___________</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Signatures Section -->
    <div class="signature-row">
        <div class="signature-box">
            <div class="signature-title">Shipper Certification</div>
            <p style="font-size: 8px; margin: 5px 0;">
                This is to certify that the above named materials are properly classified, described, packaged, marked, and labeled, and are in proper condition for transportation.
            </p>
            <div class="signature-line">
                <div>Signature: _______________________</div>
            </div>
            <div style="margin-top: 5px;">
                <div style="display: table; width: 100%;">
                    <div style="display: table-cell; width: 50%;">Date: ____________</div>
                    <div style="display: table-cell; width: 50%;">Time: ____________</div>
                </div>
            </div>
        </div>
        <div style="display: table-cell; width: 2%;"></div>
        <div class="signature-box">
            <div class="signature-title">Carrier Acknowledgment</div>
            <p style="font-size: 8px; margin: 5px 0;">
                Carrier acknowledges receipt of goods in apparent good order unless noted below.
            </p>
            <div style="margin: 5px 0; font-size: 9px;">
                Exceptions/Damages: ________________
            </div>
            <div class="signature-line">
                <div>Driver Signature: _________________</div>
            </div>
            <div style="margin-top: 5px;">
                <div style="display: table; width: 100%;">
                    <div style="display: table-cell; width: 50%;">Date: ____________</div>
                    <div style="display: table-cell; width: 50%;">Time: ____________</div>
                </div>
            </div>
        </div>
        <div style="display: table-cell; width: 2%;"></div>
        <div class="signature-box">
            <div class="signature-title">Consignee Receipt</div>
            <p style="font-size: 8px; margin: 5px 0;">
                Received the above goods in good order except as noted.
            </p>
            <div style="margin: 5px 0; font-size: 9px;">
                Exceptions: ______________________
            </div>
            <div class="signature-line">
                <div>Signature: _______________________</div>
            </div>
            <div style="margin-top: 5px;">
                <div style="display: table; width: 100%;">
                    <div style="display: table-cell; width: 50%;">Date: ____________</div>
                    <div style="display: table-cell; width: 50%;">Time: ____________</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms & Conditions -->
    <div class="terms-section">
        <h4>Terms and Conditions</h4>
        <p style="margin: 0;">
            Subject to the National Motor Freight Classification in effect on date of shipment. The agreed or declared value of the property is hereby specifically stated to be the released value not exceeding $_____ per pound. The carrier shall not be liable for any delay, loss or damage to shipment occurring while in the possession of connecting carriers. Shipper agrees to all terms and conditions stated herein.
        </p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Document generated on {{ date('m/d/Y H:i:s') }} | SteelFlow MRP - Manufacturing Resource Planning System</p>
        <p>This is an original Bill of Lading. Retain for your records. | Page 1 of 1</p>
    </div>
</body>
</html>
