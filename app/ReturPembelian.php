<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReturPembelian extends Model
{
    protected $table      = "retur_pembelian";
    public $timestamps    = false;

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'fid_supplier');
    }
}
