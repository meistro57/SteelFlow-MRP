<?php

namespace Modules\DrawingFlow\Http\Controllers;

use App\Http\Requests\StoreSubmittalNoteRequest;
use App\Models\DrawingSubmittal;
use Illuminate\Http\JsonResponse;

class SubmittalNoteController extends Controller
{
    public function index(DrawingSubmittal $submittal): JsonResponse
    {
        $notes = $submittal->submittalNotes()
            ->with('user:id,name')
            ->latest()
            ->get();

        return response()->json(['data' => $notes]);
    }

    public function store(StoreSubmittalNoteRequest $request, DrawingSubmittal $submittal): JsonResponse
    {
        $note = $submittal->submittalNotes()->create([
            'user_id' => $request->user()->id,
            'message' => $request->string('message')->toString(),
        ])->load('user:id,name');

        return response()->json(['data' => $note], 201);
    }
}
