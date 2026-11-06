<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    protected $table = 'device_tokens';
    protected $fillable = ['fid_anggota', 'token', 'platform'];
}