<?php

namespace App\Models\admin;

use App\Models\User\Pesanan;
use App\Models\User\PesananItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paket extends Model
{
    use HasFactory;

    protected $fillable = ['paket', 'image', 'harga', 'deskripsi'];

    // Relasi many-to-many dengan Menu
    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menu_paket');
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
}
