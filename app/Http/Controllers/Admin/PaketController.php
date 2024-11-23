<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\admin\Paket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PaketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pakets = Paket::with('menus')->get();
        return response()->json(['data' => $pakets], 200);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'paket' => 'required|string|max:255',
                'image' => 'nullable|image|max:2048',
                'harga' => 'required|integer|min:0',
                'deskripsi' => 'required|string',
                'menus' => 'required|array',
                'menus.*' => 'exists:menus,id',
            ]);

            // Proses upload gambar jika ada
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('pakets', 'public');
            }

            // Simpan data paket
            $paket = Paket::create($validated);

            // Simpan relasi dengan menus di tabel pivot
            $paket->menus()->sync($request->menus);

            // Muat relasi menus untuk ditampilkan di response
            $paket->load('menus');

            return response()->json([
                'message' => 'Paket created successfully',
                'data' => $paket
            ], 201);
        } catch (\Exception $e) {
            // Log error jika ada masalah
            Log::error($e->getMessage());
            return response()->json([
                'error' => 'An error occurred while creating the paket.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $paket = Paket::with('menus')->find($id);

        if (!$paket) {
            return response()->json(['message' => 'Paket not found'], 404);
        }

        return response()->json(['data' => $paket], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Paket $paket)
    {
        $validated = $request->validate([
            'paket' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
            'harga' => 'nullable|integer|min:0',
            'deskripsi' => 'nullable|string',
            'menus' => 'nullable|array', // Validasi bahwa menus adalah array
            'menus.*' => 'exists:menus,id', // Validasi setiap item adalah id menu yang valid
        ]);

        if ($request->hasFile('image')) {
            if ($paket->image) {
                Storage::disk('public')->delete($paket->image);
            }
            $validated['image'] = $request->file('image')->store('pakets', 'public');
        }

        $paket->update($validated);

        // Perbarui relasi ke tabel pivot jika menus disertakan
        if (isset($validated['menus'])) {
            $paket->menus()->sync($validated['menus']);
        }

        return response()->json(['message' => 'Paket updated successfully', 'data' => $paket->load('menus')], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $paket = Paket::find($id);

        if (!$paket) {
            return response()->json(['message' => 'Paket not found'], 404);
        }

        // Hapus gambar jika ada
        if ($paket->image) {
            Storage::disk('public')->delete($paket->image);
        }

        // Hapus relasi dengan menus di tabel pivot
        $paket->menus()->detach();

        // Hapus paket
        $paket->delete();

        return response()->json(['message' => 'Paket deleted successfully'], 200);
    }
}
