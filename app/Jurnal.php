<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Jurnal extends Model
{
    protected $table = 'jurnal';
    protected $fillable = [
        'no_jurnal',
        'tanggal',
        'keterangan'
    ];

    public function details()
    {
        return $this->hasMany(JurnalDetail::class)->orderBy('no_urut');
    }

    public function details_debit()
    {
        return $this->hasMany(JurnalDetail::class)->orderBy('no_urut')->where('nominal', '>', 0);
    }

    public function details_kredit()
    {
        return $this->hasMany(JurnalDetail::class)->orderBy('no_urut')->where('nominal', '<', 0);
    }

    public function getTotalDebitAttribute()
    {
        return $this->details_debit->sum('nominal');
    }

    public function getTotalKreditAttribute()
    {
        return $this->details_kredit->sum('nominal') * -1;
    }

    public function getBalanceAttribute()
    {
        return $this->total_debit == $this->total_kredit;
    }
}
