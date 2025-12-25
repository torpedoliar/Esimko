<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JurnalDetail extends Model
{
    protected $table = 'jurnal_detail';
    protected $fillable = [
        'jurnal_id',
        'akun_id',
        'nominal',
        'no_urut'
    ];
    protected $with = ['akun'];

    public function jurnal()
    {
        return $this->belongsTo(Jurnal::class);
    }

    public function akun()
    {
        return $this->belongsTo(Akun::class);
    }

    public function getDebitAttribute()
    {
        if ($this->nominal > 0) return $this->nominal;
        return 0;
    }

    public function getKreditAttribute()
    {
        if ($this->nominal < 0) return $this->nominal * -1;
        return 0;
    }
}
