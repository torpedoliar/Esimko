<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemPembelian extends Model
{
    protected $table      = "item_pembelian";
    protected $fillable = [
        'fid_pembelian',
        'fid_produk',
        'harga',
        'margin',
        'margin_nominal',
        'harga_jual',
        'jumlah',
        'total'
    ];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'fid_pembelian');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'fid_produk')->withTrashed();
    }
}
