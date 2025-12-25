<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function save_file(Request $request, $column_name = 'file', $name = '')
    {
        if ($request->hasFile($column_name)) {
            $file = $request->file($column_name);
            if ($name == '') $name = $column_name;
            $filename = $name . Str::random(4) . '.'. $file->extension();
            return Storage::putFileAs($column_name, $file, $filename);
        }
        return '';
    }

    public function delete_file($nama_file)
    {
        try {
            Storage::delete($nama_file);
        } catch (\Exception $e) {}
    }
}
