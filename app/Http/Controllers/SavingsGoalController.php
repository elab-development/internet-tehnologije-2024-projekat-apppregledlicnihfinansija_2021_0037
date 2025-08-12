<?php

namespace App\Http\Controllers;

use App\Models\SavingsGoal;
use Illuminate\Http\Request;

class SavingsGoalController extends Controller
{
    public function index()
    {
        $savingsGoals = SavingsGoal::all();
        return view('savings_goals.index', compact('savingsGoals'));
    }

    public function create()
    {
        return view('savings_goals.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0',
            'current_amount' => 'nullable|numeric|min:0',
            'deadline' => 'nullable|date',
        ]);

        $data = $request->only('name', 'target_amount', 'current_amount', 'deadline');
        // Za sad stavimo fiksni user_id (npr. 1)
        $data['user_id'] = 1;

        SavingsGoal::create($data);

        return redirect()->route('savings_goals.index')->with('success', 'Cilj štednje je uspešno dodat.');
    }

    public function show(SavingsGoal $savingsGoal)
    {
        return view('savings_goals.show', compact('savingsGoal'));
    }

    public function edit(SavingsGoal $savingsGoal)
    {
        return view('savings_goals.edit', compact('savingsGoal'));
    }

    public function update(Request $request, SavingsGoal $savingsGoal)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0',
            'current_amount' => 'nullable|numeric|min:0',
            'deadline' => 'nullable|date',
        ]);

        $savingsGoal->update($request->only('name', 'target_amount', 'current_amount', 'deadline'));

        return redirect()->route('savings_goals.index')->with('success', 'Cilj štednje je uspešno ažuriran.');
    }

    public function destroy(SavingsGoal $savingsGoal)
    {
        $savingsGoal->delete();

        return redirect()->route('savings_goals.index')->with('success', 'Cilj štednje je obrisan.');
    }
}
