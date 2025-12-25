<?php

namespace App\Http\Controllers;

use App\Akun;
use Illuminate\Http\Request;

class AkunController extends Controller
{
    public function __construct()
    {
        view()->share(['tipes' => ['Neraca', 'Laba/Rugi']]);
    }

    public function index()
    {
        return view('keuangan.akun.index');
    }

    public function search()
    {
        $akun = Akun::orderBy('kode_tampil')->get();
        foreach ($akun as $value) {
            $value->parent = ($value->parent_id == null) ? '#' : $value->parent_id;
            $value->text = $value->kode_tampil . ' - ' . $value->nama;
        }
        return $akun;
    }

    public function create(Request $request)
    {
        $parent_kode = $request->input('parent_kode') ?? '#';
        $parent = Akun::where('kode', $parent_kode)->first();
        $kode = $this->auto_kode($parent_kode);
        $parent_id = $parent->id ?? '';
        return view('keuangan.akun._info', compact('parent_kode', 'kode', 'parent_id'));
    }

    public function store(Request $request)
    {
        return Akun::create($request->all());
    }

    public function edit(Akun $akun)
    {
        $parent_kode = $akun->parent_kode;
        $kode = $akun->kode;
        $parent_id = $akun->parent_id;
        return view('keuangan.akun._info', compact('akun', 'parent_kode', 'kode', 'parent_id'));
    }

    public function update(Request $request, Akun $akun)
    {
        $akun->update($request->all());
        return $akun;
    }

    public function destroy(Akun $akun)
    {
        $akun->delete();
        return $akun;
    }

    private function auto_kode($parent_kode = '#')
    {
        $last = Akun::where('parent_kode', $parent_kode)->orderBy('kode', 'desc')->first();
        $kode = !empty($last) ? last(explode('.', $last->kode))+1 : 1;
        if (strlen($kode) === 1) $kode = '00' . $kode;
        if (strlen($kode) === 2) $kode = '0' . $kode;
        return $parent_kode === '#' ? $kode : $parent_kode . '.' . $kode;
    }
}
