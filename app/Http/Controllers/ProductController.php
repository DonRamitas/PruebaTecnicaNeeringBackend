<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

// Gestiona el CRUD de los productos
class ProductController extends Controller
{
    // Retorna un array de productos según un término de búsqueda y una categoría específica
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

    // Valida y almacena un producto específico
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

    // Obtiene un producto en específico
    public function show(Product $product)
    {
        return response()->json($product->load('category'));
    }

    // Actualiza un producto
    public function update(Request $request, Product $product)
    {
        // Valida los datos
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'price' => 'sometimes|required|integer|max:100000000',
            'category_id' => 'sometimes|nullable|exists:categories,id',
            'description' => 'sometimes|nullable|string|max:300',
            'image' => 'sometimes|nullable|image|max:10240',
        ]);

        // Elimina una imagen si trae el flag
        if ($request->has('remove_image') && $request->remove_image === 'true') {
            if ($product->image) {
                Storage::delete($product->image);
                $product->image = null;
            }
        }

        // Procesa imagen si viene una nueva y la almacena
        if ($request->hasFile('image')) {

            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $imagePath = $request->file('image')->store('products', 'public');
            $validated['image'] = $imagePath;
        }

        // Actualizar cada campo modificado
        foreach ($validated as $field => $value) {
            $product->{$field} = $value;
        }

        // Guardar cambios
        $product->save();

        // Retorna el producto ya modificado
        return response()->json($product);
    }

    // Elimina un producto
    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json(['message' => 'Producto eliminado con éxito']);
    }
}
