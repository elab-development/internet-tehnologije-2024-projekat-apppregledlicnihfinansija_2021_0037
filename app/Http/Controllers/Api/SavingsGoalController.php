<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SavingsGoal;
use Illuminate\Http\Request;

class SavingsGoalController extends Controller
{
    public function index(Request $request)
    {
        $page = SavingsGoal::where('user_id', $request->user()->id)
            ->latest()->paginate(15);

        return response()->json($page);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => ['required','string','max:255'],
            'target_amount'  => ['required','numeric','min:0'],
            'current_amount' => ['nullable','numeric','min:0'],
            'deadline'       => ['nullable','date'],
            'description'    => ['nullable','string','max:1000'],
        ]);

        $data['current_amount'] = $data['current_amount'] ?? 0;
        $data['user_id'] = $request->user()->id;

        $goal = SavingsGoal::create($data);
        return response()->json(['data' => $goal], 201);
    }

    public function show(Request $request, SavingsGoal $savings_goal)
    {
        if ($savings_goal->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json(['data' => $savings_goal]);
    }

    public function update(Request $request, SavingsGoal $savings_goal)
    {
        if ($savings_goal->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $data = $request->validate([
            'name'           => ['sometimes','required','string','max:255'],
            'target_amount'  => ['sometimes','required','numeric','min:0'],
            'current_amount' => ['nullable','numeric','min:0'],
            'deadline'       => ['nullable','date'],
            'description'    => ['nullable','string','max:1000'],
        ]);

        $savings_goal->update($data);
        return response()->json(['data' => $savings_goal]);
    }

    public function destroy(Request $request, SavingsGoal $savings_goal)
    {
        if ($savings_goal->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $savings_goal->delete();
        return response()->noContent();
    }
}
