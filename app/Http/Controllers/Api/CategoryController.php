<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Pretpostavka: kategorije su globalne (bez user_id)
    public function index()
    {
        return response()->json(
            Category::orderBy('name')->paginate(50)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255','unique:categories,name'],
        ]);

        $cat = Category::create($data);
        return response()->json(['data' => $cat], 201);
    }

    public function show(Category $category)
    {
        return response()->json(['data' => $category]);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255','unique:categories,name,'.$category->id],
        ]);

        $category->update($data);
        return response()->json(['data' => $category]);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->noContent();
    }
}
