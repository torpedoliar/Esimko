# [AGENT TASK v2.1 / HOTFIX] Perbaiki Regresi produk() + Cleanup Mobile API

<aside>
🤖

AGENT-TO-AGENT HOTFIX SPEC. Ini kelanjutan dari [AGENT TASK v2]. Wiring run sebelumnya LULUS gate §9, TAPI memasukkan regresi yang membuat `GET mobile/produk` error 500. Baca seluruh dokumen. Format: TARGET → FIND → REPLACE → VERIFY. FIND harus match byte-exact. Jangan menambah scope di luar file yang disebut.

</aside>

## 0. CONTEXT

```yaml
repo: torpedoliar/Esimko
branch_base: main
stack: Laravel 7.x / PHP 7.x
prior_run: "AGENT TASK v2 (wiring)"
prior_result: GATE_PASSED_BUT_SMOKE_TEST_6_FAILS
severity: P0 (endpoint produk 500) + minor cleanup
```

## 1. ROOT CAUSE (verified by re-read)

Di `app/Http/Controllers/MobileController.php` method `produk()`, run sebelumnya mencoba memperbaiki N+1 tapi meninggalkan 2 variabel undefined:

```php
$p = $query->orderBy('produk.nama_produk', 'DESC')->paginate($request->input('per_page', 20)); $items = $p->items(); foreach ($items as $key => $value) {
  $foto = $fotos->get($value->id);   // $fotos TIDAK PERNAH didefinisikan  => Call to a member function get() on null
  $result[$key]->foto = ...;         // $result TIDAK PERNAH didefinisikan
  ...
}
return isset($p) ? ApiResponse::success($result, ...) ...   // $result undefined
```

Efek runtime: `GET mobile/produk` → HTTP 500. Gate `grep` §9 tidak menangkap ini karena tidak memvalidasi variabel undefined. Smoke test §8 langkah 6 GAGAL.

## 2. PRIME DIRECTIVE

<aside>
⛔

Definisi SELESAI hotfix ini = (a) `produk()` mengembalikan data + meta tanpa error, (b) TIDAK ada lagi variabel `$result`/`$fotos` undefined di seluruh file, (c) `php -l` lulus, (d) semua VERIFY di §5 sesuai. DILARANG lapor DONE jika salah satu gate gagal.

</aside>

Mandat: **FIX-IN-PLACE, MINIMAL DIFF.** Hanya sentuh method yang disebut. Jangan refactor hal lain. Jangan ubah nama endpoint/route. Jangan ubah `MobileHelper`, `ApiResponse`, `MobileAuth`, `GlobalHelper`.

## 3. INVARIANTS

```yaml
ALLOWED_EDIT:
  - app/Http/Controllers/MobileController.php    # HANYA method: produk, transaksi, upload_avatar, + method2 minor di §5
DO_NOT_MODIFY:
  - app/Helpers/GlobalHelper.php
  - app/Helpers/GlobalHelper2.php
  - app/Helpers/MobileHelper.php
  - app/Support/ApiResponse.php
  - app/Http/Middleware/MobileAuth.php
  - routes/api.php
  - app/Http/Kernel.php
  - routes/web.php ; resources/views/** ; migrations
RULE: jika perbaikan menuntut edit file DO_NOT_MODIFY, STOP + laporkan BLOCKER.
```

---

## 4. PRECONDITION CHECK

```bash
php -l app/Http/Controllers/MobileController.php    # harus "No syntax errors" SEBELUM diedit; jika sudah error, tetap lanjut
grep -n "\$fotos->get" app/Http/Controllers/MobileController.php    # harus menemukan baris di produk()
```

---

## 5. TASK LIST (berurutan)

### HFIX-1 — [P0] Perbaiki method `produk()`

**TARGET:** `app/Http/Controllers/MobileController.php` → `public function produk(Request $request)`

**FIND (blok rusak, byte-exact termasuk baris gabungan):**

```php
$p = $query->orderBy('produk.nama_produk', 'DESC')->paginate($request->input('per_page', 20)); $items = $p->items(); foreach ($items as $key => $value) {
  $foto = $fotos->get($value->id);
  $result[$key]->foto = (!empty($foto) ? asset('storage/' . $foto->foto) : asset('assets/images/produk-default.jpg'));
  $kategori = explode('.', $value->kode_kategori);
  if ($kategori[0] == 0) {
    $result[$key]->kelompok = GlobalHelper::detail_kategori_produk($kategori[1]);
    $result[$key]->kategori = GlobalHelper::detail_kategori_produk($kategori[2]);
    $result[$key]->sub_kategori = '';
  } else {
    $result[$key]->kelompok = GlobalHelper::detail_kategori_produk($kategori[0]);
    $result[$key]->kategori = GlobalHelper::detail_kategori_produk($kategori[1]);
    $result[$key]->sub_kategori = GlobalHelper::detail_kategori_produk($kategori[2]);
  }
}
return isset($p) ? ApiResponse::success($result, 'OK', ['page'=>$p->currentPage(),'per_page'=>$p->perPage(),'total'=>$p->total(),'last_page'=>$p->lastPage()]) : ApiResponse::success($result);
```

**REPLACE:**

```php
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
return ApiResponse::success($items, 'OK', ['page'=>$p->currentPage(),'per_page'=>$p->perPage(),'total'=>$p->total(),'last_page'=>$p->lastPage()]);
```

**RATIONALE:** definisikan `$fotos` (batch, sekaligus fix N+1), gunakan `$value` (bukan `$result` undefined), buang ternary `isset($p)` yang tak perlu karena `$p` pasti terdefinisi di sini.

**VERIFY:**

```bash
grep -c "\$fotos->get" app/Http/Controllers/MobileController.php        # >= 1 (tetap dipakai, tapi kini terdefinisi)
grep -c "FotoProduk::whereIn('fid_produk', \$produk_ids)" app/Http/Controllers/MobileController.php   # == 1
grep -c "\$result\[\$key\]" app/Http/Controllers/MobileController.php   # == 0  (tidak ada lagi referensi $result)
```

### HFIX-2 — [P1] Rapikan `transaksi()` agar meta konsisten

**TARGET:** method `transaksi()` — saat ini return pakai `isset($p)` padahal variabel bernama `$result`, sehingga `meta` tak pernah terkirim & bentuk response tidak konsisten.

**FIND:**

```php
$limit = $request->limit ?: 10;
if ($request->has('page')) {
    $result = $query->orderBy('transaksi.tanggal', 'DESC')->orderBy('transaksi.created_at', 'DESC')->paginate($limit);
    $items = $result->items();
} else {
    $result = $query->orderBy('transaksi.tanggal', 'DESC')->orderBy('transaksi.created_at', 'DESC')->limit($limit)->get();
    $items = $result;
}
```

**REPLACE:**

```php
$limit = $request->limit ?: 10;
$paginated = $request->has('page');
if ($paginated) {
    $p = $query->orderBy('transaksi.tanggal', 'DESC')->orderBy('transaksi.created_at', 'DESC')->paginate($limit);
    $items = $p->items();
} else {
    $items = $query->orderBy('transaksi.tanggal', 'DESC')->orderBy('transaksi.created_at', 'DESC')->limit($limit)->get();
}
```

**AND FIND (return method transaksi):**

```php
return isset($p) ? ApiResponse::success($result, 'OK', ['page'=>$p->currentPage(),'per_page'=>$p->perPage(),'total'=>$p->total(),'last_page'=>$p->lastPage()]) : ApiResponse::success($result);
```

**REPLACE:**

```php
return $paginated ? ApiResponse::success($items, 'OK', ['page'=>$p->currentPage(),'per_page'=>$p->perPage(),'total'=>$p->total(),'last_page'=>$p->lastPage()]) : ApiResponse::success($items);
```

**NOTE:** loop di antara keduanya memakai `$items` — JANGAN diubah; hanya pastikan tidak ada lagi referensi `$result` di method ini.

**VERIFY:** `grep -n "\$result" app/Http/Controllers/MobileController.php` — pastikan TIDAK ada kemunculan di dalam method `transaksi()` (boleh ada di method lain hanya jika memang terdefinisi lokal).

### HFIX-3 — [P2] Buang ternary `isset($p)` mati (dead code) di method non-paginated

**TARGET:** method: `jenis_transaksi`, `status_transaksi`, `detail_transaksi`, `detail_produk`, `angsuran_pinjaman`, `keranjang`.

**ACTION:** di method-method ini `$p` tidak pernah didefinisikan; sederhanakan return menjadi hanya cabang tanpa meta.

**PATTERN FIND (generik, per method):**

```php
return isset($p) ? ApiResponse::success($<var>, 'OK', ['page'=>$p->currentPage(),'per_page'=>$p->perPage(),'total'=>$p->total(),'last_page'=>$p->lastPage()]) : ApiResponse::success($<var>);
```

**PATTERN REPLACE:**

```php
return ApiResponse::success($<var>);
```

(`$<var>` = variabel data existing di method itu: `$data` untuk jenis/status/detail_transaksi/detail_produk/angsuran_pinjaman; `$result` untuk keranjang — di `keranjang()` `$result` MEMANG terdefinisi via `->get()`, jadi aman.)

**CONSTRAINT:** JANGAN sentuh method yang benar-benar paginated (`produk`, `berita`, `belanja`, `retur_barang`, `angsuran_belanja`) — di sana `$p` valid.

**VERIFY:** `grep -c "isset(\$p)" app/Http/Controllers/MobileController.php`  # hasil akhir HANYA menghitung method paginated yang sengaja dibiarkan; target ideal == 0 (karena method paginated pun sudah pakai bentuk langsung setelah HFIX-1). Jika masih >0, pastikan semuanya berada di method dengan `$p` terdefinisi.

### HFIX-4 — [P2] Standarkan return terakhir `upload_avatar()`

**TARGET:** method `upload_avatar()` — cabang "anggota tidak ditemukan" masih pakai array mentah.

**FIND:**

```php
return array('msg' => 'Anggota tidak ditemukan');
```

**REPLACE:**

```php
return ApiResponse::error('Anggota tidak ditemukan', 404);
```

**VERIFY:** `grep -c "return array('msg'" app/Http/Controllers/MobileController.php`  # == 0

### HFIX-5 — [P2] Konfirmasi storage disk `public` konsisten

**TARGET:** VERIFIKASI SAJA (bukan edit kode). Run sebelumnya mengubah upload ke disk `public` (`store('...', 'public')` + path `app/public/...`).

**ACTION:**

- Pastikan symlink storage ada: `php artisan storage:link` (idempotent; aman diulang).
- JANGAN mengubah kode disk. Jika kebijakan penyimpanan lama (disk `local`) wajib dipertahankan, JANGAN putuskan sendiri → catat sebagai OPEN QUESTION di laporan, biarkan human decide.

**VERIFY:** `test -L public/storage && echo OK_SYMLINK`

---

## 6. NO-REGRESSION GUARD

```bash
git diff --name-only main
```

HARUS hanya:

```
app/Http/Controllers/MobileController.php
```

(Jika `HFIX-5` menjalankan `storage:link`, itu membuat symlink `public/storage`, bukan perubahan file tracked — abaikan dari diff.) Jika ada file lain berubah → REVERT.

## 7. BUILD / SANITY

```bash
php -l app/Http/Controllers/MobileController.php     # WAJIB: No syntax errors detected
php artisan route:list --path=mobile/produk         # route tampil, middleware mobile.auth
```

## 8. SMOKE TEST (WAJIB lulus, fokus regresi)

```
1. GET mobile/produk (Bearer token)              => 200; data array; meta.page ada; TIDAK 500
2. GET mobile/produk?per_page=5&page=2 (Bearer)  => 200; meta.page=2; meta.per_page=5
3. GET mobile/produk?search=<kata> (Bearer)      => 200; hasil terfilter
4. GET mobile/transaksi/simpanan?page=1 (Bearer)  => 200; meta terisi; TIDAK error $result
5. GET mobile/anggota/profil (Bearer)             => 200 (regression check, tak ikut berubah)
```

## 9. FINAL VERIFICATION GATE (semua HARUS true)

```bash
grep -c "\$result\[\$key\]" app/Http/Controllers/MobileController.php   # == 0
grep -c "FotoProduk::whereIn('fid_produk', \$produk_ids)" app/Http/Controllers/MobileController.php   # == 1
grep -c "return array('msg'" app/Http/Controllers/MobileController.php  # == 0
php -l app/Http/Controllers/MobileController.php                        # No syntax errors detected
```

Jika ADA yang tidak sesuai → BELUM SELESAI.

## 10. OUTPUT CONTRACT (laporan balik)

```yaml
status: DONE | BLOCKED
files_changed: [ app/Http/Controllers/MobileController.php ]
verify_gate:
  result_index_refs: <n>        # target 0
  fotos_batch_defined: <n>      # target 1
  raw_msg_array: <n>            # target 0
  php_lint: pass|fail
smoke_test: [ pass/fail per langkah §8 ]
open_questions: [ ... ]         # mis. kebijakan disk public vs local (HFIX-5)
blockers: [ ... ]
```

<aside>
✅

DONE hanya jika: §9 semua sesuai + §8 langkah 1–4 lulus + §6 hanya `MobileController.php` berubah. Jika `php -l` gagal atau `mobile/produk` masih 500 → status = BLOCKED beserta pesan error mentah.

</aside>