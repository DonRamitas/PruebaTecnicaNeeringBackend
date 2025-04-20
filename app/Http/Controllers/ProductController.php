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

        if (!$request->hasFile('image') && !$request->has(['name', 'price', 'category_id', 'description'])) {
            return response()->json(['message' => 'No se enviaron datos para actualizar'], 422);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|integer|max:100000000',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string|max:300',
            'image' => 'nullable|image|max:10240',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $imagePath = $request->file('image')->store('products', 'public');
            $product->image = $imagePath;
        }

        $product->update($request->only(['name', 'price', 'category_id', 'description']));

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
