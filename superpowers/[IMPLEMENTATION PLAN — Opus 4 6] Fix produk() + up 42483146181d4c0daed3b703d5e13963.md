# [IMPLEMENTATION PLAN — Opus 4.6] Fix produk() + upload_avatar() Mobile API

<aside>
🧠

Implementation plan untuk **Claude Opus 4.6** sebagai coding agent. Tujuan: memperbaiki 1 regresi P0 (`GET mobile/produk` → 500) + 1 cleanup, di repo `torpedoliar/Esimko`. Baca dokumen ini utuh sebelum mengedit. Eksekusi dengan disiplin verifikasi — 3 run agent sebelumnya GAGAL karena membuat commit tanpa benar-benar mengubah kode.

</aside>

## 0. Context Snapshot

```yaml
repo: torpedoliar/Esimko
branch: main
stack: Laravel 7.x / PHP 7.x
file_target: app/Http/Controllers/MobileController.php
scope: HANYA method produk() dan upload_avatar()
latest_commit: "fix: resolve produk N+1 regression and cleanup mobile API (12158a1)"
failure_history: "3x commit 'fix' dibuat TAPI kode tidak berubah (edit gagal/di-skip diam-diam)"
```

## 1. Kenapa run sebelumnya gagal (pelajari ini)

- Agent memakai **patch FIND/REPLACE sebagian**; string FIND tidak match byte-exact (ada baris yang tergabung: `...paginate(...); $items = $p->items(); foreach (...` dalam satu baris) → patch di-skip diam-diam.
- Agent lalu **tetap commit dengan pesan "fix"** meski `git diff` kosong → laporan menyesatkan.
- **Pelajaran untuk Opus 4.6:** JANGAN andalkan patch sebagian. **Baca isi method dulu, ganti SELURUH method**, lalu **buktikan lewat `git diff` nyata**. Jika diff kosong → status BLOCKED, bukan DONE.

## 2. Root Cause Teknis

Di `produk()`, upaya fix N+1 sebelumnya meninggalkan 2 variabel undefined:

```php
$foto = $fotos->get($value->id);   // $fotos TIDAK didefinisikan => Call to a member function get() on null
$result[$key]->foto = ...;         // $result TIDAK didefinisikan
```

Efek: `GET mobile/produk` → HTTP 500. Selain itu `upload_avatar()` cabang terakhir masih `return array('msg' => ...)` (tidak konsisten dengan standar `ApiResponse`).

## 3. Guardrails

```yaml
ALLOWED_EDIT: [ app/Http/Controllers/MobileController.php ]   # HANYA method produk() & upload_avatar()
DO_NOT_MODIFY: [ GlobalHelper.php, GlobalHelper2.php, MobileHelper.php, ApiResponse.php, MobileAuth.php, routes/api.php, Kernel.php, resources/views/**, database/migrations/** ]
RULES:
  - Ganti SELURUH isi method (bukan patch sebagian).
  - Jangan ubah signature/nama method, jangan ubah route.
  - Pertahankan jumlah kurung kurawal penutup class tetap benar.
  - DILARANG commit "fix/done" bila git diff kosong atau gate gagal.
```

## 4. Langkah Eksekusi (deterministik)

### Step 1 — Orientasi

```bash
git checkout main && git pull
php -l app/Http/Controllers/MobileController.php
grep -n "public function produk(Request" app/Http/Controllers/MobileController.php
grep -n "public function upload_avatar(Request" app/Http/Controllers/MobileController.php
```

Baca isi kedua method penuh (dari `{` sampai `}` penutupnya) sebelum menulis apa pun.

### Step 2 — Ganti SELURUH method `produk()`

Ganti isi method `public function produk(Request $request)` menjadi PERSIS:

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

### Step 3 — Ganti SELURUH method `upload_avatar()`

Ganti isi method `public function upload_avatar(Request $request)` menjadi PERSIS:

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

### Step 4 — Verifikasi lokal (WAJIB, tempel output mentah)

```bash
php -l app/Http/Controllers/MobileController.php
grep -c "\$fotos->get" app/Http/Controllers/MobileController.php                          # 1
grep -c "FotoProduk::whereIn('fid_produk', \$produk_ids)" app/Http/Controllers/MobileController.php   # 1
grep -c "\$result\[\$key\]" app/Http/Controllers/MobileController.php                     # 0
grep -c "return array('msg'" app/Http/Controllers/MobileController.php                    # 0
git --no-pager diff --stat                                                               # HARUS 1 file changed
```

Jika `git diff --stat` = 0 file changed → STOP, status BLOCKED (edit tidak masuk).

### Step 5 — Commit & push (hanya jika Step 4 lolos)

```bash
git add app/Http/Controllers/MobileController.php
git commit -m "fix: produk() undefined vars (fotos/result) + upload_avatar response"
git push origin main
```

## 5. Smoke Test (fungsional)

```
1. GET mobile/produk (Bearer token)              => 200; TIDAK 500; ada meta.page
2. GET mobile/produk?per_page=5&page=2 (Bearer)  => 200; meta.page=2; meta.per_page=5
3. GET mobile/produk?search=<kata> (Bearer)      => 200; hasil terfilter
4. POST mobile/upload_avatar tanpa anggota valid => 404 (ApiResponse.error), bukan array mentah
```

## 6. Definition of Done

- [ ]  `php -l` = No syntax errors detected
- [ ]  Gate grep Step 4 semua sesuai (1,1,0,0)
- [ ]  `git diff --stat` menunjukkan tepat 1 file berubah (`MobileController.php`)
- [ ]  Smoke test 1–4 lulus
- [ ]  Commit ter-push ke `main` dengan SHA baru

## 7. Output Contract (laporan balik, jujur)

```yaml
status: DONE | BLOCKED
php_lint: <output mentah>
grep: { fotos_get: <n>, fotos_batch: <n>, result_index: <n>, raw_msg_array: <n> }
git_diff_stat: <output mentah>
commit_sha: <sha atau '-'>
smoke_test: [ pass/fail per langkah ]
blockers: [ ... ]
```

<aside>
⛔

ATURAN FINAL: status = DONE HANYA jika `git diff` menunjukkan perubahan nyata + gate grep sesuai + `php -l` lulus + commit ter-push. Jika diff kosong / gate gagal → WAJIB BLOCKED. DILARANG mengarang keberhasilan.

</aside>