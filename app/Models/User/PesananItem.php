<?php

namespace App\Models\User;

use App\Models\admin\Menu;
use App\Models\admin\Paket;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesananItem extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika berbeda dengan nama model
    protected $table = 'pesanan_items';

    // Kolom yang bisa diisi massal
    protected $fillable = [
        'pesanan_id',
        'menu_id',
        'paket_id',
        'jumlah',
        'harga_total',
    ];

    // Relasi dengan model Pesanan
    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class);
    }

    // Relasi dengan model Menu (jika menu dipesan)
    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    // Relasi dengan model Paket (jika paket dipesan)
    public function paket()
    {
        return $this->belongsTo(Paket::class);
    }

    public $timestamps = true;
}
