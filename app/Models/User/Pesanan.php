<?php

namespace App\Models\User;

use App\Models\admin\Menu;
use App\Models\admin\Paket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika berbeda dengan nama model
    protected $table = 'pesanans';

    // Kolom yang bisa diisi massal
    protected $fillable = [
        'user_id',
        'tipe_pesanan',
        'nasi',
        'metode_pembayaran',
        'total_harga',
        'alamat_pesanan',
        'status',
        'catatan',
        'balasan',
    ];

    // Relasi dengan model User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi dengan model PesananItem
    public function pesananItems()
    {
        return $this->hasMany(PesananItem::class);
    }

    // Relasi dengan model Menu (jika tipe_pesanan adalah 'menu')
    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'pesanan_items');
    }

    // Relasi dengan model Paket (jika tipe_pesanan adalah 'paket')
    public function pakets()
    {
        return $this->belongsToMany(Paket::class, 'pesanan_items');
    }

    // Method untuk menghitung total harga pesanan
    public function calculateTotalPrice()
    {
        return $this->pesananItems->sum(function ($item) {
            return $item->harga_total;
        });
    }

    public $timestamps = true;
}
