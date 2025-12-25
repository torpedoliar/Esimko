<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemReturPenjualan extends Model
{
    protected $table      = "item_retur_penjualan";
    public $timestamps    = false;

    public function retur_penjualan()
    {
        return $this->belongsTo(ReturPenjualan::class, 'fid_retur_penjualan');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'fid_produk')->with(['satuan_barang'])->withTrashed();
    }
}
