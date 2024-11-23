<?php

namespace App\Models\admin;

use App\Models\admin\Kategori;
use App\Models\User\Pesanan;
use App\Models\User\PesananItem;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'kat_id',
        'menu',
        'image',
        'harga',
        'deskripsi',
        'stok',
    ];

    /**
     * Get the category associated with the menu.
     */
    public function category()
    {
        return $this->belongsTo(Kategori::class, 'kat_id');
    }

    public function pakets()
    {
        return $this->belongsToMany(Paket::class, 'menu_paket');
    }

    // Relasi dengan pesanan item
    public function pesananItems()
    {
        return $this->hasMany(PesananItem::class);
    }

    // Relasi dengan pesanan
    public function pesanan()
    {
        return $this->belongsToMany(Pesanan::class, 'pesanan_items');
    }

    public $timestamps = true;
}
