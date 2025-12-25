<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PengaturanLog extends Model
{
    protected $table = "pengaturan_log";
    public $timestamps = false;
    protected $fillable = ['fid_pengaturan', 'nilai_lama', 'nilai_baru', 'created_by', 'keterangan', 'created_at'];

    public function pengaturan()
    {
        return $this->belongsTo(Pengaturan::class, 'fid_pengaturan');
    }

    public function admin()
    {
        return $this->belongsTo(Anggota::class, 'created_by', 'no_anggota');
    }
}
