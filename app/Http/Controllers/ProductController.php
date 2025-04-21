<?php

namespace App\Http\Controllers;//

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->paginate(10);

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|integer|max:100000000',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string|max:300',
            'image' => 'nullable|image|max:10240',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'image' => $imagePath,
        ]);

        return response()->json($product, 201);
    }

    public function show(Product $product)
    {
        return response()->json($product->load('category'));
    }

    public function update(Request $request, Product $product)
    {
        // Primero, verifica qué valores llegan
        \Log::info('Datos recibidos:', $request->all());
        
        // Validación
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'price' => 'sometimes|required|integer|max:100000000',
            'category_id' => 'sometimes|nullable|exists:categories,id',
            'description' => 'sometimes|nullable|string|max:300',
            'image' => 'sometimes|image|max:10240',
        ]);
        
        \Log::info('Datos validados:', $validated);

        // Procesar imagen si viene una nueva
        if ($request->hasFile('image')) {
            // Eliminar imagen anterior
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            // Guardar nueva imagen
            $imagePath = $request->file('image')->store('products', 'public');
            $validated['image'] = $imagePath; // Añadir al array validado
        }

        // Actualizar cada campo explícitamente
        foreach ($validated as $field => $value) {
            $product->{$field} = $value;
        }

        // Guardar cambios
        $product->save();
        
        \Log::info('Producto después de guardar:', $product->toArray());

        return response()->json($product);
    }



    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json(['message' => 'Producto eliminado con éxito']);
    }
}
