<?php

namespace App\Http\Controllers;
use App\Models\Category;
use Illuminate\Http\Request;

// Gestiona el CRUD de las categorías
class CategoryController extends Controller
{
    // Retorna un array de categorías según un término de búsqueda (opcional)
    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%$search%");
        }

        return response()->json($query->paginate(10));
    }

    // Retorna todas las categorías
    public function all()
    {
        return response()->json(Category::all());
    }

    // Almacena una categoría
    public function store(Request $request)
    {
        // Valida el nombre de la categoría
        $request->validate([
            'name' => 'required|string|max:30',
        ]);

        // Si es válida la crea
        $category = Category::create([
            'name' => $request->name
        ]);

        return response()->json($category, 201);
    }

    // Retorna una categoría en específico
    public function show($id)
    {
        $category = Category::find($id);
        if (!$category) return response()->json(['error' => 'Categoría no encontrada'], 404);
        return $category;
    }

    // Actualiza el nombre de una categoría
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

    // Elimina una categoría
    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) return response()->json(['error' => 'Categoría no encontrada'], 404);

        $category->delete();
        return response()->json(['message' => 'Categoría eliminada']);
    }
}
