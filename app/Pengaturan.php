<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    protected $table = "pengaturan";
    protected $fillable = ['kode', 'nama', 'nilai', 'tipe', 'keterangan'];

    public function logs()
    {
        return $this->hasMany(PengaturanLog::class, 'fid_pengaturan');
    }

    /**
     * Get setting value by code
     * @param string $kode
     * @param mixed $default
     * @return mixed
     */
    public static function getValue($kode, $default = null)
    {
        $setting = self::where('kode', $kode)->first();
        return $setting ? $setting->nilai : $default;
    }
}
