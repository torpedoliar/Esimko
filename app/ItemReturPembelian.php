<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemReturPembelian extends Model
{
    protected $table      = "item_retur_pembelian";
    public $timestamps    = false;

    public function retur_pembelian()
    {
        return $this->belongsTo(ReturPembelian::class, 'fid_retur_pembelian');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'fid_produk')->with(['satuan_barang'])->withTrashed();
    }
}
