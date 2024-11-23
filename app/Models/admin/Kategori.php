<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $fillable = [
        'kategori',
    ];

    public function menus()
    {
        return $this->hasMany(Menu::class, 'kat_id');
    }

    public $timestamps = true;
}
