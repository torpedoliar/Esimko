<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReturPenjualan extends Model
{
    protected $table      = "retur_penjualan";

    public $timestamps    = false;
    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'fid_anggota', 'no_anggota');
    }

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'fid_penjualan');
    }
}
