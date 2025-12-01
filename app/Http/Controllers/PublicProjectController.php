<?php

namespace App\Http\Controllers;

use App\Models\Project;

class PublicProjectController extends Controller
{
    public function index()
    {
        // Public opportunities listing has been retired.
        // Redirect any direct hits back to the homepage.
        return redirect()->route('home');
    }

    public function show($projectId)
    {
        $project = Project::with(['property', 'investorDocuments', 'updates' => function ($query) {
            $query->where('category', 3)
                ->where('deleted', 0)
                ->orderByDesc('sent_on')
                ->limit(5);
        }])
            ->where('project_id', $projectId)
            ->firstOrFail();

        return view('projects.show', compact('project'));
    }
}


