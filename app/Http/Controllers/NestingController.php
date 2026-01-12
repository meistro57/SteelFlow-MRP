<?php

namespace App\Http\Controllers;

use App\Models\Nesting;
use App\Models\Project;
use Inertia\Inertia;
use Inertia\Response;

class NestingController extends Controller
{
    public function index(): Response
    {
        $nestings = Nesting::with(['project'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Nesting/Index', [
            'nestings' => $nestings,
        ]);
    }

    public function show(Nesting $nesting): Response
    {
        $nesting->load(['project', 'bars.nestingParts.partInstance.part', 'bars.stockItem']);

        return Inertia::render('Nesting/Show', [
            'nesting' => $nesting,
        ]);
    }

    public function create(Project $project): Response
    {
        return Inertia::render('Nesting/Create', [
            'project' => $project,
        ]);
    }
}
