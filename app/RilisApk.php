<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RilisApk extends Model
{
    protected $table = 'rilis_apk';

    protected $fillable = ['versi', 'version_code', 'changelog', 'nama_file', 'ukuran', 'sha256', 'dibuat_oleh'];

    public static function terbaru()
    {
        return self::orderByDesc('version_code')->first();
    }
}
