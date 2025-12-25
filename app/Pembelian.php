<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    protected $table      = "pembelian";
    public $timestamps    = false;
    protected $fillable = [
        'no_pembelian',
        'tanggal',
        'fid_supplier',
        'keterangan',
        'diskon_persen',
        'diskon_nominal',
        'ppn_persen',
        'ppn_nominal',
        'biaya_tambahan',
        'total',
        'created_at',
        'created_by',
        'file'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'fid_supplier');
    }

    public function details()
    {
        return $this->hasMany(ItemPembelian::class, 'fid_pembelian');
    }

    public function kasir()
    {
        return $this->belongsTo(Anggota::class, 'created_by', 'no_anggota');
    }
}
