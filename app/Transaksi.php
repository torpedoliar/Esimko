<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table      = "transaksi";
    public $timestamps    = false;
    protected $fillable = [
        'fid_anggota',
        'tanggal',
        'nominal',
        'bunga',
        'tenor',
        'fid_jenis_transaksi',
        'fid_metode_transaksi',
        'fid_status',
        'keterangan',
        'fid_payroll',
        'fid_angsuran',
        'fid_pinjaman',
        'status_pinjaman',
        'bukti_transaksi',
        'created_by',
        'saldo_awal'
    ];

    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'fid_anggota', 'no_anggota');
    }

    public function operator()
    {
        return $this->belongsTo(Anggota::class, 'created_by', 'no_anggota');
    }

    public function jenis_transaksi()
    {
        return $this->belongsTo(JenisTransaksi::class, 'fid_jenis_transaksi');
    }

    public function status()
    {
        return $this->belongsTo(StatusTransaksi::class, 'fid_status');
    }

    public function getStatus2Attribute()
    {
        return ($this->nominal * -1) - $this->angsuran_pokok->sum('nominal');
    }

    public function angsuran2()
    {
        return $this->belongsTo(Angsuran::class, 'fid_angsuran');
    }

    public function angsuran3()
    {
        return $this->hasMany(Transaksi::class, 'fid_pinjaman')->whereIn('fid_jenis_transaksi', [12, 14]);
    }

    public function angsuran()
    {
        return $this->hasMany(Angsuran::class, 'fid_transaksi');
    }

    public function angsuran_pokok()
    {
        return $this->hasMany(Transaksi::class, 'fid_pinjaman')
            ->whereIn('fid_jenis_transaksi', [12, 14]);
    }

    public function angsuran_bunga()
    {
        return $this->hasMany(Transaksi::class, 'fid_pinjaman')
            ->where('fid_jenis_transaksi', 13);
    }

    public function angsuran_terakhir()
    {
        return $this->hasOne(Angsuran::class, 'fid_transaksi')
            ->whereNotNull('fid_payroll')
            ->orderBy('angsuran_ke', 'desc');
    }

    public function angsuran_akan_datang()
    {
        return $this->hasOne(Angsuran::class, 'fid_transaksi')
            ->whereNull('fid_payroll')
            ->orderBy('angsuran_ke', 'asc');
    }

}
