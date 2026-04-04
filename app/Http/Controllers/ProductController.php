<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::query()->get();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'duration' => 'required|string|max:255',
            'duration_in_min' => 'required',
            'description' => 'required',
            'price' => 'required',
        ]);

        $type = strtolower(str_replace(' ', '_', $request->title));
        Product::create([
            'title' => $request->title,
            'type' => $type,
            'duration' => $request->duration,
            'duration_in_min' => $request->duration_in_min,
            'description' => $request->description,
            'price' => $request->price,
            'is_gst' => $request->is_gst ?? 0,
            'gst_type' => $request->gst_type,
            'gst_amount' => $request->is_gst ? $request->gst_amount : null,
            'total_price' => $request->total_price,
            'status' => $request->status ?? 0,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);

        return view('products.edit', compact('product'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'duration' => 'required|string|max:255',
            'duration_in_min' => 'required',
            'description' => 'required',
            'price' => 'required',
        ]);
        $product = Product::findOrFail($id);
        $type = strtolower(str_replace(' ', '_', $request->title));
        $product->update([
            'title' => $request->title,
            'type' => $type,
            'duration' => $request->duration,
            'duration_in_min' => $request->duration_in_min,
            'description' => $request->description,
            'price' => $request->price,
            'is_gst' => $request->is_gst ?? 0,
            'gst_type' => $request->is_gst ? $request->gst_type : null,
            'gst_amount' => $request->is_gst ? $request->gst_amount : null,
            'total_price' => $request->is_gst ? $request->total_price : null,
            'status' => $request->status ?? 0,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully');
    }

    public function updateStatus(Request $request)
    {
        $product = Product::find($request->product_id);
        if ($product) {
            $product->status = $request->status;
            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'Product status updated successfully!',
            ]);
        }

        return response()->json(['success' => false], 404);
    }
}
