<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:30',
        ]);

        $category = Category::create([
            'name' => $request->name
        ]);

        return response()->json($category, 201);
    }

    public function show($id)
    {
        $category = Category::find($id);
        if (!$category) return response()->json(['error' => 'Categoría no encontrada'], 404);
        return $category;
    }

    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) return response()->json(['error' => 'Categoría no encontrada'], 404);

        $request->validate([
            'name' => 'required|string|max:30',
        ]);

        $category->name = $request->name;
        $category->save();

        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) return response()->json(['error' => 'Categoría no encontrada'], 404);

        $category->delete();
        return response()->json(['message' => 'Categoría eliminada']);
    }
}
