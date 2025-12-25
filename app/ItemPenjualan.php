<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemPenjualan extends Model
{
    protected $table      = "item_penjualan";
    public $timestamps    = false;
    protected $fillable = [
        'fid_penjualan',
        'fid_produk',
        'nama_barang',
        'nama_supplier',
        'satuan',
        'harga_beli',
        'margin',
        'margin_nominal',
        'harga',
        'jumlah',
        'total',
        'diskon'
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'fid_produk')->with(['satuan_barang'])->withTrashed();
    }

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'fid_penjualan');
    }

}
