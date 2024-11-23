<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $menus = Menu::with('category')->get();
        return response()->json(['data' => $menus], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kat_id' => 'required|exists:kategoris,id',
            'menu' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'harga' => 'required|integer|min:0',
            'deskripsi' => 'required|string',
            'stok' => 'required|integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('menus', 'public');
        }

        $menu = Menu::create($validated);
        $menu->load('category'); // Load kategori terkait untuk respons
        return response()->json(['message' => 'Menu created successfully', 'data' => $menu], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Menu $menu)
    {
        $menu->load('category'); // Pastikan kategori disertakan
        return response()->json(['data' => $menu], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'kat_id' => 'nullable|exists:kategoris,id',
            'menu' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
            'harga' => 'nullable|integer|min:0',
            'deskripsi' => 'nullable|string',
            'stok' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            if ($menu->image) {
                Storage::disk('public')->delete($menu->image);
            }
            $validated['image'] = $request->file('image')->store('menus', 'public');
        }

        $menu->update($validated);
        $menu->load('category'); // Load kategori terkait setelah pembaruan
        return response()->json(['message' => 'Menu updated successfully', 'data' => $menu], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Menu $menu)
    {
        if ($menu->image) {
            Storage::disk('public')->delete($menu->image);
        }
        $menu->delete();
        return response()->json(['message' => 'Menu deleted successfully'], 200);
    }
}
