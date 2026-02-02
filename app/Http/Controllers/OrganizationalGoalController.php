<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrganizationalGoalController extends Controller
{
    public function index()
    {
        $goals = \App\Models\OrganizationalGoal::orderBy('created_at', 'desc')->get();
        return view('modules.hr.organizational-goals.index', compact('goals'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        \App\Models\OrganizationalGoal::create($request->all());

        return redirect()->back()->with('success', 'Organizational Goal created successfully.');
    }

    public function destroy($id)
    {
        $goal = \App\Models\OrganizationalGoal::findOrFail($id);
        $goal->delete();
        return redirect()->back()->with('success', 'Organizational Goal deleted successfully.');
    }
}
