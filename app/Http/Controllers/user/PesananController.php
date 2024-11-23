<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\Pesanan;
use App\Models\User\PesananItem;
use App\Models\admin\Menu;
use App\Models\admin\Paket;
use Illuminate\Http\Request;

class PesananController extends Controller
{
    /**
     * Display a listing of the user's pesanan.
     */
    public function index()
    {
        // Mengambil semua pesanan untuk user yang sedang login dengan relasi terkait
        $pesanans = auth()->user()->pesanans()->with(['pesananItems.menu', 'pesananItems.paket'])->get();

        // Format data untuk respons
        $data = $pesanans->map(function ($pesanan) {
            return [
                'id' => $pesanan->id,
                'tipe_pesanan' => $pesanan->tipe_pesanan,
                'total_harga' => $pesanan->total_harga,
                'alamat_pesanan' => $pesanan->alamat_pesanan,
                'status' => $pesanan->status,
                'catatan' => $pesanan->catatan,
                'nasi' => $pesanan->nasi,  // Mengambil kolom nasi
                'metode_pembayaran' => $pesanan->metode_pembayaran,  // Mengambil metode pembayaran
                'created_at' => $pesanan->created_at,
                'updated_at' => $pesanan->updated_at,
                'items' => $pesanan->pesananItems->map(function ($item) {
                    return [
                        'menu_id' => $item->menu_id,
                        'paket_id' => $item->paket_id,
                        'jumlah' => $item->jumlah,
                        'harga_total' => $item->harga_total,
                        'menu' => $item->menu ? [
                            'nama' => $item->menu->menu,
                            'gambar' => $item->menu->image,
                        ] : null,
                        'paket' => $item->paket ? [
                            'nama' => $item->paket->paket,
                            'gambar' => $item->paket->image,
                        ] : null,
                    ];
                }),
            ];
        });

        return response()->json($data, 200);
    }

    /**
     * Show the form for creating a new pesanan.
     */
    public function create()
    {
        // Tampilkan daftar menu dan paket yang dapat dipesan
        $menus = Menu::all();
        $pakets = Paket::all();

        return response()->json(compact('menus', 'pakets'));
    }

    /**
     * Store a newly created pesanan in storage.
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'tipe_pesanan' => 'required|in:menu,paket',
            'total_harga' => 'required|numeric',
            'alamat_pesanan' => 'required|string',
            'status' => 'required|in:dipesan,diantar,selesai',
            'catatan' => 'nullable|string',
            'nasi' => 'required|boolean',  // Validasi untuk nasi (0 atau 1)
            'metode_pembayaran' => 'required|string',  // Validasi untuk metode pembayaran
            'items' => 'required|array', // Daftar pesanan items
            'items.*.menu_id' => 'nullable|exists:menus,id',
            'items.*.paket_id' => 'nullable|exists:pakets,id',
            'items.*.jumlah' => 'required|integer|min:1',
        ]);

        // Membuat pesanan
        $pesanan = auth()->user()->pesanans()->create([
            'tipe_pesanan' => $request->tipe_pesanan,
            'total_harga' => $request->total_harga,
            'alamat_pesanan' => $request->alamat_pesanan,
            'status' => $request->status,
            'catatan' => $request->catatan,
            'nasi' => $request->nasi,  // Menyimpan nilai nasi
            'metode_pembayaran' => $request->metode_pembayaran,  // Menyimpan metode pembayaran
        ]);

        // Menyimpan pesanan items dan mengurangi stok
        foreach ($request->items as $item) {
            // Buat PesananItem
            PesananItem::create([
                'pesanan_id' => $pesanan->id,
                'menu_id' => $item['menu_id'] ?? null,
                'paket_id' => $item['paket_id'] ?? null,
                'jumlah' => $item['jumlah'],
                'harga_total' => $item['harga_total'], // Pastikan menghitung harga_total di frontend atau backend
            ]);

            // Kurangi stok menu jika menu_id tersedia
            if (isset($item['menu_id'])) {
                $menu = Menu::find($item['menu_id']);
                if ($menu) {
                    $menu->stok -= $item['jumlah']; // Kurangi stok
                    if ($menu->stok < 0) {
                        return response()->json(['message' => 'Stok tidak mencukupi untuk salah satu item'], 400);
                    }
                    $menu->save(); // Simpan perubahan stok
                }
            }
        }

        // Ambil data pesanan lengkap dengan menu atau paket
        $pesanan->load(['pesananItems.menu', 'pesananItems.paket']);

        // Format respons
        $data = [
            'id' => $pesanan->id,
            'tipe_pesanan' => $pesanan->tipe_pesanan,
            'total_harga' => $pesanan->total_harga,
            'alamat_pesanan' => $pesanan->alamat_pesanan,
            'status' => $pesanan->status,
            'catatan' => $pesanan->catatan,
            'nasi' => $pesanan->nasi,  // Mengambil kolom nasi
            'metode_pembayaran' => $pesanan->metode_pembayaran,  // Mengambil metode pembayaran
            'items' => $pesanan->pesananItems->map(function ($item) {
                return [
                    'menu_id' => $item->menu_id,
                    'paket_id' => $item->paket_id,
                    'jumlah' => $item->jumlah,
                    'harga_total' => $item->harga_total,
                    'menu' => $item->menu ? [
                        'nama' => $item->menu->menu,
                        'gambar' => $item->menu->image,
                    ] : null,
                    'paket' => $item->paket ? [
                        'nama' => $item->paket->paket,
                        'gambar' => $item->paket->image,
                    ] : null,
                ];
            }),
        ];

        return response()->json(['message' => 'Pesanan berhasil dibuat', 'data' => $data], 201);
    }


    /**
     * Display the specified pesanan.
     */
    public function show($id)
    {
        $pesanan = Pesanan::with(['pesananItems.menu', 'pesananItems.paket'])->find($id);

        if (!$pesanan) {
            return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
        }

        return response()->json($pesanan);
    }

    /**
     * Update the specified pesanan in storage.
     */
    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'status' => 'required|in:dipesan,diantar,selesai,ditolak',
            'balasan' => 'nullable|string|required_if:status,ditolak', // Balasan wajib jika status "ditolak"
        ]);

        $pesanan = Pesanan::find($id);

        if (!$pesanan) {
            return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
        }

        // Update status dan balasan jika diperlukan
        $pesanan->update([
            'status' => $request->status,
            'balasan' => $request->status === 'ditolak' ? $request->balasan : null, // Set balasan hanya jika status ditolak
        ]);

        return response()->json(['message' => 'Status pesanan diperbarui', 'data' => $pesanan]);
    }

    /**
     * Remove the specified pesanan from storage.
     */
    public function destroy($id)
    {
        $pesanan = Pesanan::find($id);

        if (!$pesanan) {
            return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
        }

        // Hapus semua pesanan items terkait
        $pesanan->pesananItems()->delete();

        // Hapus pesanan
        $pesanan->delete();

        return response()->json(['message' => 'Pesanan berhasil dihapus']);
    }
    public function adminIndex()
    {
        // Fetch all orders with user info and their related items
        $pesanans = Pesanan::with(['pesananItems.menu', 'pesananItems.paket', 'user'])->get();

        // Format data for the response
        $data = $pesanans->map(function ($pesanan) {
            return [
                'id' => $pesanan->id,
                'tipe_pesanan' => $pesanan->tipe_pesanan,
                'total_harga' => $pesanan->total_harga,
                'alamat_pesanan' => $pesanan->alamat_pesanan,
                'status' => $pesanan->status,
                'catatan' => $pesanan->catatan,
                'nasi' => $pesanan->nasi,
                'metode_pembayaran' => $pesanan->metode_pembayaran,
                'created_at' => $pesanan->created_at,
                'updated_at' => $pesanan->updated_at,
                'items' => $pesanan->pesananItems->map(function ($item) {
                    return [
                        'menu_id' => $item->menu_id,
                        'paket_id' => $item->paket_id,
                        'jumlah' => $item->jumlah,
                        'harga_total' => $item->harga_total,
                        'menu' => $item->menu ? [
                            'nama' => $item->menu->menu,
                            'gambar' => $item->menu->image,
                        ] : null,
                        'paket' => $item->paket ? [
                            'nama' => $item->paket->paket,
                            'gambar' => $item->paket->image,
                        ] : null,
                    ];
                }),
                'user_name' => $pesanan->user ? $pesanan->user->name : 'Unknown User', // Include the user name
            ];
        });

        return response()->json($data, 200);
    }
}
