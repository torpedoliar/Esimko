# [AGENT TASK v2.2 / HOTFIX] Replace Full Method produk() + upload_avatar()

<aside>
🤖

AGENT-TO-AGENT HOTFIX. Pakai **replace full-method** (bukan FIND/REPLACE sebagian) supaya tidak gagal-match. File yang boleh diedit: HANYA `app/Http/Controllers/MobileController.php`.

</aside>

## 0. CONTEXT

```yaml
repo: torpedoliar/Esimko
branch: main
stack: Laravel 7.x / PHP 7.x
file_allowed_edit: app/Http/Controllers/MobileController.php
prior_result: "commit 'fix' dibuat TAPI kode tak berubah (FIND gagal match lalu di-skip)"
```

## 1. ROOT CAUSE

Method `produk()` MASIH rusak: `$fotos` dan `$result` tidak pernah didefinisikan → `GET mobile/produk` error 500. Patch sebagian gagal karena FIND tidak match byte-exact. Solusi run ini: **ganti seluruh method**.

## 2. PRIME DIRECTIVE

<aside>
⛔

JANGAN patch sebagian — GANTI SELURUH METHOD. JANGAN ubah file lain (`GlobalHelper*`, `MobileHelper`, `ApiResponse`, `MobileAuth`, `routes/api.php`, `Kernel.php`, views, migrations). DILARANG commit sebagai "fix/done" bila GATE gagal → lapor `status: BLOCKED` + tempel error mentah.

</aside>

## 3. TUGAS 1 — Ganti SELURUH method `produk()`

Cari `public function produk(Request $request)` lalu ganti seluruh isi method (dari `{` sampai `}` penutup) menjadi PERSIS:

```php
public function produk(Request $request)
{
    $query = Produk::select('produk.*', 'satuan_barang.satuan')
        ->join('satuan_barang', 'satuan_barang.id', '=', 'produk.fid_satuan');
    if (!empty($request->search)) {
        $search = $request->search;
        $query = $query->where(function ($i) use ($search) {
            $i->where('produk.nama_produk', 'like', "%{$search}%")
              ->orWhere('produk.kode', 'like', "%{$search}%");
        });
    }

    $p = $query->orderBy('produk.nama_produk', 'DESC')->paginate($request->input('per_page', 20));
    $items = $p->items();
    $produk_ids = collect($items)->pluck('id')->toArray();
    $fotos = FotoProduk::whereIn('fid_produk', $produk_ids)->get()->keyBy('fid_produk');

    foreach ($items as $value) {
        $foto = $fotos->get($value->id);
        $value->foto = (!empty($foto) ? asset('storage/' . $foto->foto) : asset('assets/images/produk-default.jpg'));
        $kategori = explode('.', $value->kode_kategori);
        if ($kategori[0] == 0) {
            $value->kelompok = GlobalHelper::detail_kategori_produk($kategori[1]);
            $value->kategori = GlobalHelper::detail_kategori_produk($kategori[2]);
            $value->sub_kategori = '';
        } else {
            $value->kelompok = GlobalHelper::detail_kategori_produk($kategori[0]);
            $value->kategori = GlobalHelper::detail_kategori_produk($kategori[1]);
            $value->sub_kategori = GlobalHelper::detail_kategori_produk($kategori[2]);
        }
    }

    return ApiResponse::success($items, 'OK', [
        'page' => $p->currentPage(),
        'per_page' => $p->perPage(),
        'total' => $p->total(),
        'last_page' => $p->lastPage(),
    ]);
}
```

## 4. TUGAS 2 — Ganti SELURUH method `upload_avatar()`

Cari `public function upload_avatar(Request $request)` lalu ganti seluruh isi method menjadi PERSIS (perbaikan: cabang terakhir pakai `ApiResponse`, bukan array mentah):

```php
public function upload_avatar(Request $request)
{
    $field = Anggota::where("no_anggota", $request->no_anggota)->first();

    if (!empty($field)) {
        if ($request->hasFile('avatar')) {
            if (!empty($field->avatar)) {
                if (file_exists(storage_path('app/public/' . $field->avatar))) {
                    unlink(storage_path('app/public/' . $field->avatar));
                }
            }
            $uploadedFile = $request->file('avatar');
            $path = $uploadedFile->store('avatar', 'public');
            $field->avatar = $path;
            $field->save();
            return ApiResponse::success(null, 'success');
        } else {
            return ApiResponse::error('File kosong');
        }
    } else {
        return ApiResponse::error('Anggota tidak ditemukan', 404);
    }
}
```

## 5. GATE (WAJIB; jika ADA yang tidak sesuai → status BLOCKED, JANGAN commit sebagai fix)

```bash
php -l app/Http/Controllers/MobileController.php
# harus: No syntax errors detected

grep -c "\$fotos->get" app/Http/Controllers/MobileController.php
# harus: 1

grep -c "FotoProduk::whereIn('fid_produk', \$produk_ids)" app/Http/Controllers/MobileController.php
# harus: 1

grep -c "\$result\[\$key\]" app/Http/Controllers/MobileController.php
# harus: 0

grep -c "return array('msg'" app/Http/Controllers/MobileController.php
# harus: 0

git diff --name-only main
# harus HANYA: app/Http/Controllers/MobileController.php
```

## 6. SMOKE TEST (wajib lulus)

```
1. GET mobile/produk (Bearer token)              => 200; TIDAK 500; ada data.meta.page
2. GET mobile/produk?per_page=5&page=2 (Bearer)  => 200; meta.page=2; meta.per_page=5
3. GET mobile/produk?search=<kata> (Bearer)      => 200; hasil terfilter
```

## 7. LAPORAN BALIK (format wajib)

```yaml
status: DONE | BLOCKED
files_changed: [ ... ]         # harus HANYA MobileController.php
gate:
  php_lint: pass|fail
  fotos_get: <n>               # target 1
  fotos_batch: <n>             # target 1
  result_index: <n>            # target 0
  raw_msg_array: <n>           # target 0
  git_files: [ ... ]
smoke_test: [ pass/fail per langkah ]
blockers: [ ... ]              # tempel error mentah bila ada
```

<aside>
✅

DONE hanya jika: `php -l` lulus + semua `grep` sesuai + smoke test 1–3 lulus + hanya `MobileController.php` berubah. Selain itu → BLOCKED. DILARANG mengklaim selesai tanpa gate lulus.

</aside>