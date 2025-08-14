<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SavingsGoal;
use Illuminate\Http\Request;

class SavingsGoalController extends Controller
{
    public function index(Request $request)
    {
        // Paginacija: per_page (1–100), podrazumevano 15
        $perPage = max(1, min((int) $request->query('per_page', 15), 100));
    
        $query = SavingsGoal::query()
            ->where('user_id', $request->user()->id)
            ->select('savings_goals.*')
            // izračunamo progress (0–1) da možemo da filtriramo/sortiramo po njemu
            ->selectRaw('CASE WHEN target_amount > 0 THEN (current_amount / target_amount) ELSE 0 END AS progress_ratio');
    
        // Pretraga po nazivu/opisu (?q=auto)
        if ($request->filled('q')) {
            $q = $request->query('q');
            $query->where(function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                   ->orWhere('description', 'like', "%{$q}%");
            });
        }
    
        // Filtri po ciljanoj sumi
        if ($request->filled('min_target')) {
            $query->where('target_amount', '>=', (float) $request->query('min_target'));
        }
        if ($request->filled('max_target')) {
            $query->where('target_amount', '<=', (float) $request->query('max_target'));
        }
    
        // Filtri po progresu (kao 0–1 ili 0–100%)
        if ($request->filled('min_progress')) {
            $min = (float) $request->query('min_progress');
            if ($min > 1) { $min /= 100; }
            $query->having('progress_ratio', '>=', $min);
        }
        if ($request->filled('max_progress')) {
            $max = (float) $request->query('max_progress');
            if ($max > 1) { $max /= 100; }
            $query->having('progress_ratio', '<=', $max);
        }
    
        // Rok (deadline)
        if ($request->filled('due_before')) {
            $query->whereDate('deadline', '<=', $request->date('due_before'));
        }
        if ($request->filled('due_after')) {
            $query->whereDate('deadline', '>=', $request->date('due_after'));
        }
    
       //samo ostvarenii ciljevi
        if ($request->boolean('achieved')) {
            $query->whereColumn('current_amount', '>=', 'target_amount');
        }
    
        
        $sort = $request->get('sort', 'deadline');
        $dir  = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $col  = ltrim($sort, '-');
    
        $map = [
            'name'           => 'name',
            'deadline'       => 'deadline',
            'target_amount'  => 'target_amount',
            'current_amount' => 'current_amount',
            'created_at'     => 'created_at',
            'progress'       => 'progress_ratio',
        ];
        $col = $map[$col] ?? 'deadline';
    
        $query->orderBy($col, $dir)->orderBy('id', 'desc');
    
        return response()->json($query->paginate($perPage));
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
