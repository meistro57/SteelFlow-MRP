<?php

namespace App\Http\Controllers;

use App\Models\Drawing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DrawingController extends Controller
{
    public function show(Drawing $drawing)
    {
        if (!Storage::disk('drawings')->exists($drawing->file_path)) {
            abort(404);
        }

        return Storage::disk('drawings')->response($drawing->file_path);
    }

    public function upload(Request $request, Drawing $drawing)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,dwg,dxf,jpg,png|max:10240',
        ]);

        $path = $request->file('file')->store('project_' . $drawing->project_id, 'drawings');
        
        $drawing->update([
            'file_path' => $path,
            'revised_at' => now(),
        ]);

        return back()->with('success', 'Drawing uploaded successfully');
    }
}
