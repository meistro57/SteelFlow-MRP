<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>BOM Report - {{ $project->job_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { bg-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
        .project-info { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Bill of Materials</h1>
        <h2>{{ $project->name }} ({{ $project->job_number }})</h2>
    </div>

    <div class="project-info">
        <p><strong>Customer:</strong> {{ $project->customer->name ?? 'N/A' }}</p>
        <p><strong>Date:</strong> {{ date('m/d/Y') }}</p>
    </div>

    @foreach($project->assemblies as $assembly)
        <h3>Assembly: {{ $assembly->mark }} - {{ $assembly->description }} (Qty: {{ $assembly->quantity }})</h3>
        <table>
            <thead>
                <tr>
                    <th>Part Mark</th>
                    <th>Qty</th>
                    <th>Material</th>
                    <th>Size</th>
                    <th>Length</th>
                    <th>Weight (ea)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assembly->parts as $part)
                    <tr>
                        <td>{{ $part->part_mark }}</td>
                        <td>{{ $part->quantity }}</td>
                        <td>{{ $part->grade }}</td>
                        <td>{{ $part->size_imperial }}</td>
                        <td>{{ $part->length }}</td>
                        <td>{{ number_format($part->weight_each_lbs, 2) }} lbs</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <br>
    @endforeach
</body>
</html>
