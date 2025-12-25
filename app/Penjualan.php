<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    protected $table      = "penjualan";
    protected $fillable = [
        'tanggal',
        'no_transaksi',
        'jenis_belanja',
        'fid_anggota',
        'fid_metode_pembayaran',
        'total_pembayaran',
        'diskon',
        'tenor',
        'angsuran',
        'keterangan',
        'marketplace',
        'tunai',
        'kembali',
        'no_debit_card',
        'account_number',
        'tipe_voucher',
        'voucher_nominal',
        'voucher_persen',
        'kode_voucher',
        'attachment',
        'fid_status',
        'created_by',
        'kasir'
    ];

    public function user_kasir()
    {
        return $this->belongsTo(Anggota::class, 'kasir', 'no_anggota');
    }

    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'fid_anggota', 'no_anggota');
    }

    public function data_kasir()
    {
        return $this->belongsTo(Anggota::class, 'kasir', 'no_anggota');
    }

    public function metode()
    {
        return $this->belongsTo(MetodePembayaran::class, 'fid_metode_pembayaran');
    }

    public function items()
    {
        return $this->hasMany(ItemPenjualan::class, 'fid_penjualan');
    }

    public function metode_pembayaran()
    {
        return $this->belongsTo(RekeningPembayaran::class, 'fid_metode_pembayaran');
    }
}
