@extends('pdfcenter::layouts.pdf')

@section('header_right')
    <span class="font-bold" style="font-size: 16px;">NON-CONFORMANCE REPORT</span><br>
    No: {{ $ncr->number }}
@endsection

@section('content')
    <div style="margin-bottom: 30px;">
        <h3>General Information</h3>
        <table>
            <tr>
                <th width="25%">Date Reported</th>
                <td width="25%">{{ $ncr->created_at->format('Y-m-d') }}</td>
                <th width="25%">Reported By</th>
                <td width="25%">{{ $ncr->reporter->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ $ncr->status->label() }}</td>
                <th>Disposition</th>
                <td>{{ $ncr->disposition ?? 'Pending' }}</td>
            </tr>
        </table>
    </div>

    <div style="margin-bottom: 30px;">
        <h3>Affected Item</h3>
        <table>
            <tr>
                <th width="25%">Part Mark</th>
                <td width="75%">{{ $ncr->partInstance->part->part_mark ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Project</th>
                <td>{{ $ncr->partInstance->project->name ?? 'N/A' }} ({{ $ncr->partInstance->project->job_number ?? 'N/A' }})</td>
            </tr>
            <tr>
                <th>Material Heat #</th>
                <td>{{ $ncr->stockItem->heat_number ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <div style="margin-bottom: 30px;">
        <h3>Failure Description</h3>
        <div style="border: 1px solid #ddd; padding: 15px; min-height: 100px;">
            {{ $ncr->failure_reason }}
        </div>
    </div>

    @if($ncr->disposition)
    <div style="margin-bottom: 30px;">
        <h3>Remediation Plan</h3>
        <table>
            <tr>
                <th width="25%">Dispositioned By</th>
                <td width="25%">{{ $ncr->dispositioner->name ?? 'N/A' }}</td>
                <th width="25%">Date</th>
                <td width="25%">{{ $ncr->dispositioned_at ? $ncr->dispositioned_at->format('Y-m-d') : 'N/A' }}</td>
            </tr>
        </table>
        <div style="border: 1px solid #ddd; padding: 15px; min-height: 100px;">
            <span class="font-bold">Action: {{ $ncr->disposition }}</span><br><br>
            {{ $ncr->remediation_notes }}
        </div>
    </div>
    @endif
@endsection
