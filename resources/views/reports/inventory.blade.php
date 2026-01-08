<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report - {{ date('m/d/Y') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .header { text-align: center; margin-bottom: 20px; }
        .summary { margin-bottom: 20px; background: #fafafa; padding: 15px; border: 1px solid #eee; }
        .footer { margin-top: 30px; text-align: center; font-size: 9px; color: #777; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Inventory Status Report</h1>
        <p>Generated on {{ date('m/d/Y H:i') }}</p>
    </div>

    @php
        if (!function_exists('formatLength')) {
            function formatLength($length) {
                if (!$length) return '-';
                $feet = floor($length);
                $inches = round(($length - $feet) * 12);
                if ($inches == 12) { $feet++; $inches = 0; }
                return $inches > 0 ? "{$feet}' {$inches}\"" : "{$feet}'";
            }
        }
    @endphp

    <div class="summary">
        <table style="border: none; margin-top: 0;">
            <tr style="border: none;">
                <td style="border: none; width: 33%;">
                    <span class="font-bold">Total Pieces:</span> {{ number_format($summary['totalItems']) }}
                </td>
                <td style="border: none; width: 33%;">
                    <span class="font-bold">Total Valuation:</span> ${{ number_format($summary['valuation'], 2) }}
                </td>
                <td style="border: none; width: 33%;">
                    <span class="font-bold">Material Types:</span> {{ count($summary['byType']) }}
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Stock ID</th>
                <th>Type</th>
                <th>Size</th>
                <th>Grade</th>
                <th class="text-right">Length</th>
                <th class="text-right">Qty</th>
                <th>Status</th>
                <th>Project</th>
                <th class="text-right">Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->stock_id }}</td>
                    <td>{{ $item->type }}</td>
                    <td>{{ $item->size }}</td>
                    <td>{{ $item->grade }}</td>
                    <td class="text-right">{{ formatLength($item->length) }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td>{{ ucfirst($item->status) }}</td>
                    <td>{{ $item->reservedProject->job_number ?? '-' }}</td>
                    <td class="text-right">${{ number_format($item->cost_per_unit * $item->quantity, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        SteelFlow MRP - Confidential Commercial Document
    </div>
</body>
</html>
