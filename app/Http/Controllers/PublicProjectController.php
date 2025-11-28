<?php

namespace App\Http\Controllers;

use App\Models\Project;

class PublicProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with('property')
            ->whereIn('status', [
                Project::STATUS_PENDING_EQUITY,
                Project::STATUS_PENDING_PURCHASE,
                Project::STATUS_PENDING_CONSTRUCTION,
                Project::STATUS_UNDER_CONSTRUCTION,
                Project::STATUS_PENDING_SALE,
                Project::STATUS_PENDING_REMORTGAGE,
                Project::STATUS_PENDING_LET,
                Project::STATUS_ON_MARKET,
            ])
            ->orderByDesc('launched_on')
            ->paginate(9);

        return view('projects.index', compact('projects'));
    }

    public function show($projectId)
    {
        $project = Project::with(['property', 'investorDocuments', 'updates' => function ($query) {
            $query->where('category', 3)->latest()->limit(5);
        }])
            ->where('project_id', $projectId)
            ->firstOrFail();

        return view('projects.show', compact('project'));
    }
}


