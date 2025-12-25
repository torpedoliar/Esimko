<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AngsuranBelanja extends Model
{
    protected $table      = "angsuran_belanja";
    public $timestamps    = false;
    protected $fillable = [
        'fid_penjualan',
        'angsuran_ke',
        'total_angsuran',
        'fid_payroll',
        'fid_status'
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'fid_penjualan');
    }
}
