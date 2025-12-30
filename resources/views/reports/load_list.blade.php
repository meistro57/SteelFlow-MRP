<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Load List - {{ $load->load_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { bg-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
        .info { margin-bottom: 20px; display: flex; justify-content: space-between; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Load Shipping List</h1>
        <h2>Load #: {{ $load->load_number }}</h2>
    </div>

    <div class="info">
        <div>
            <p><strong>Project:</strong> {{ $load->project->name }} ({{ $load->project->job_number }})</p>
            <p><strong>Destination:</strong> {{ $load->destination }}</p>
        </div>
        <div>
            <p><strong>Ship Date:</strong> {{ $load->ship_date }}</p>
            <p><strong>Carrier:</strong> {{ $load->carrier }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Mark</th>
                <th>Description</th>
                <th>Qty</th>
                <th>Weight (ea)</th>
                <th>Total Weight</th>
            </tr>
        </thead>
        <tbody>
            @foreach($load->items as $item)
                <tr>
                    <td>{{ $item->mark }}</td>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->weight_each_lbs, 2) }} lbs</td>
                    <td>{{ number_format($item->total_weight_lbs, 2) }} lbs</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2">TOTALS</th>
                <th>{{ $load->total_pieces }}</th>
                <th></th>
                <th>{{ number_format($load->total_weight_lbs, 2) }} lbs</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
