<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Angsuran extends Model
{
    protected $table      = "angsuran";
    public $timestamps    = false;
    protected $fillable = [
        'sisa_hutang', 'bunga', 'angsuran_pokok', 'angsuran_bunga'
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'fid_transaksi');
    }

    public function payroll()
    {
        return $this->belongsTo(PayrollAngsuran::class);
    }
}
