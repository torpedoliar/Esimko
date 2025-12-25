<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produk extends Model
{
    use SoftDeletes;
    protected $table      = "produk";
    public $timestamps    = false;
    protected $fillable = [
        'harga_beli',
        'margin',
        'margin_nominal',
        'harga_jual'
    ];

    public function kategori_produk()
    {
        return $this->belongsTo(KategoriProduk::class, 'fid_kategori');
    }

    public function satuan_barang()
    {
        return $this->belongsTo(SatuanBarang::class, 'fid_satuan');
    }

    public function getFotoUrlAttribute()
    {
        if (!empty($this->foto)) return asset('storage/' . $this->foto->foto);
        return asset('assets/images/produk-default.jpg');
    }

    public function foto()
    {
        return $this->hasOne(FotoProduk::class, 'fid_produk');
    }
}
