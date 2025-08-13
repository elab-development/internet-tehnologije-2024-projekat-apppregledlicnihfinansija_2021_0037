<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $page = Budget::where('user_id', $request->user()->id)
            ->latest()->paginate(15);

        return response()->json($page);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required','string','max:255'],
            'amount'      => ['required','numeric','min:0.01'],
            'description' => ['nullable','string','max:1000'],
            // dodaj po potrebi: 'period' => ['nullable','in:monthly,yearly']
        ]);

        $data['user_id'] = $request->user()->id;

        $model = Budget::create($data);
        return response()->json(['data' => $model], 201);
    }

    public function show(Request $request, Budget $budget)
    {
        if ($budget->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json(['data' => $budget]);
    }

    public function update(Request $request, Budget $budget)
    {
        if ($budget->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $data = $request->validate([
            'name'        => ['sometimes','required','string','max:255'],
            'amount'      => ['sometimes','required','numeric','min:0.01'],
            'description' => ['nullable','string','max:1000'],
        ]);

        $budget->update($data);
        return response()->json(['data' => $budget]);
    }

    public function destroy(Request $request, Budget $budget)
    {
        if ($budget->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $budget->delete();
        return response()->noContent();
    }
}
