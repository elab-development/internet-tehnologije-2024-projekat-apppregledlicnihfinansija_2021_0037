<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class BudgetController extends Controller
{
   public function index(Request $request)
{
     $perPage = max(1, min((int)$request->query('per_page', 15), 100));

    $q = Budget::with('category')->where('user_id', $request->user()->id);

    
    if ($request->filled('category_id')) {
        $q->where('category_id', (int)$request->query('category_id'));
    }
    if ($request->filled('month')) {
        $q->where('month', (int)$request->query('month')); // 1–12
    }
    if ($request->filled('year')) {
        $q->where('year', (int)$request->query('year'));
    }
    if ($request->filled('amount_min')) {
        $q->where('amount', '>=', (float)$request->query('amount_min'));
    }
    if ($request->filled('amount_max')) {
        $q->where('amount', '<=', (float)$request->query('amount_max'));
    }

   
    $sort = $request->get('sort', '-created_at');
    $dir  = str_starts_with($sort, '-') ? 'desc' : 'asc';
    $col  = ltrim($sort, '-');
    if (!in_array($col, ['amount','month','year','created_at','updated_at'])) {
        $col = 'created_at';
    }

    return response()->json(
        $q->orderBy($col, $dir)->paginate($perPage)
    );
}

    public function store(Request $request)
    {       
        $data = $request->validate([
                'category_id' => ['required','exists:categories,id',
             Rule::unique('budgets')->where(fn($q) =>
            $q->where('user_id', $request->user()->id)
              ->where('month', $request->input('month'))
              ->where('year', $request->input('year'))
        )
    ], //nije moguce uneti dupli budzet za istu kategoriju i isti mesec i godinu
                'amount'      => ['required','numeric','min:0.01'],
                'month'       => ['required','integer','between:1,12'],
                'year'        => ['required','integer','between:2000,2100'],
                'description' => ['nullable','string','max:1000'], 
        ]);

        $data['user_id'] = $request->user()->id;

        $model = Budget::create($data);

        return response()->json(['data' => $model->load('category')], 201);
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

            'category_id' => ['sometimes','required','exists:categories,id', 
            Rule::unique('budgets')->ignore($budget->id)->where(fn($q) =>
                $q  ->where('user_id', $request->user()->id)
                    ->where('month',   $request->input('month', $budget->month))
                    ->where('year',    $request->input('year',  $budget->year))
),],
            'amount'      => ['sometimes','required','numeric','min:0.01'],
            'month'       => ['sometimes','required','integer','between:1,12'],
            'year'        => ['sometimes','required','integer','between:2000,2100'],
            'description' => ['nullable','string','max:1000'], 

        ]);

        $budget->update($data);
        return response()->json(['data' => $budget->load('category')]);
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
