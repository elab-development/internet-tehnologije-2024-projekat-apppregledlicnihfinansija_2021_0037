<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index()
    {
        // Prikaži sve budžete (bez filtriranja po korisniku)
        $budgets = Budget::with('category')->get();
        return view('budgets.index', compact('budgets'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('budgets.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0',
            'month'       => ['required','integer','between:1,12'],
            'year'        => ['required','integer','between:2000,2100'],
            'description' => ['nullable','string','max:1000'],  
        ]);

        $data = $request->only('category_id', 'amount', 'month');
        
        // Fiksni user_id, promeni ako želiš na null (ako dozvoliš nullable u migraciji)
        $data['user_id'] = 1; 

        // Provera da li već postoji budžet za datu kategoriju i mesec (bez korisnika)
        $exists = Budget::where('category_id', $data['category_id'])
                        ->where('month', $data['month'])
                        ->exists();

        if ($exists) {
            return redirect()->back()->withErrors(['Budžet za ovu kategoriju i mesec već postoji.']);
        }

        Budget::create($data);

        return redirect()->route('budgets.index')->with('success', 'Budžet je uspešno dodat.');
    }

    public function edit(Budget $budget)
    {
        $categories = Category::all();
        return view('budgets.edit', compact('budget', 'categories'));
    }

    public function update(Request $request, Budget $budget)
    {
        $request->validate([
            'category_id' => ['sometimes','required','exists:categories,id'],
            'amount'      => ['sometimes','required','numeric','min:0.01'],
            'month'       => ['sometimes','required','integer','between:1,12'],
            'year'        => ['sometimes','required','integer','between:2000,2100'],
            'description' => ['nullable','string','max:1000'], 
        ]);

        $budget->update($request->only('category_id', 'amount', 'month'));

        return redirect()->route('budgets.index')->with('success', 'Budžet je uspešno ažuriran.');
    }

    public function destroy(Budget $budget)
    {
        $budget->delete();

        return redirect()->route('budgets.index')->with('success', 'Budžet je obrisan.');
    }
}
