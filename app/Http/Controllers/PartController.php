<?php

namespace App\Http\Controllers;

use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PartController extends Controller
{
    public function index(Request $request)
    {
        $query = Part::with('category');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $parts = $query->paginate(10);

        return response()->json($parts);
    }

    // Crear nuevo recurso
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|integer|max:100000000',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|max:1000',
            'image' => 'nullable|image|max:10240', // 10MB = 10240KB
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('parts', 'public');
        }

        $part = Part::create([
            'name' => $request->name,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'image' => $imagePath,
        ]);

        return response()->json($part, 201);
    }

    // Ver detalle de un recurso
    public function show(Part $part)
    {
        return response()->json($part->load('category'));
    }

    // Actualizar un recurso
    public function update(Request $request, Part $part)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|integer|max:100000000',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|max:1000',
            'image' => 'nullable|image|max:10240', // 10MB = 10240KB
        ]);

        if ($request->hasFile('image')) {
            // Elimina la imagen anterior si existe
            if ($part->image) {
                Storage::disk('public')->delete($part->image);
            }

            $imagePath = $request->file('image')->store('parts', 'public');
            $part->image = $imagePath;
        }

        $part->update($request->only(['name', 'price', 'category_id', 'description']));

        return response()->json($part);
    }

    // Eliminar un recurso
    public function destroy(Part $part)
    {
        if ($part->image) {
            Storage::disk('public')->delete($part->image);
        }

        $part->delete();

        return response()->json(['message' => 'Parte eliminada con éxito']);
    }
}
