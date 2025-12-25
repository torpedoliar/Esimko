<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StokOpname extends Model
{
    protected $table = "stok_opname";
    public $timestamps = false;
    protected $fillable = [
        'fid_produk',
        'tanggal',
        'jumlah',
        'jenis',
        'keterangan',
        'jumlah_fisik',
        'hpp'
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'fid_produk')->withTrashed();
    }
}
