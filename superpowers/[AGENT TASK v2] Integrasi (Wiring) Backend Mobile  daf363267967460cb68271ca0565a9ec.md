# [AGENT TASK v2] Integrasi (Wiring) Backend Mobile API Esimko

<aside>
🤖

AGENT-TO-AGENT SPEC. Baca seluruh dokumen sebelum menulis kode. Format: instruksi imperatif + FIND/REPLACE eksplisit + VERIFY gate. Jangan menafsirkan ulang. Jangan menambah scope.

</aside>

## 0. CONTEXT

```yaml
repo: torpedoliar/Esimko
branch_base: main
stack: Laravel 7.x / PHP 7.x
prior_commit: "feat: perbaikan backend mobile API (T0-T7)"
prior_state: FILES_CREATED_BUT_NOT_WIRED
```

## 1. ROOT CAUSE (kenapa run sebelumnya GAGAL)

Run sebelumnya membuat file baru (`MobileHelper.php`, `MobileAuth.php`, `ApiResponse.php`) TETAPI tidak mengintegrasikannya. Bukti terverifikasi:

- `routes/api.php` = UNCHANGED → `MobileAuth` tidak pernah dipasang.
- `app/Http/Controllers/MobileController.php` = BYTE-IDENTICAL dengan versi lama → `MobileHelper`/`ApiResponse` tidak pernah dipanggil.
- `app/Http/Controllers/Api/VersionController.php` = UNCHANGED.

=> Semua wrapper menjadi DEAD CODE. Runtime tidak berubah. Semua BUG masih hidup.

## 2. PRIME DIRECTIVE

<aside>
⛔

**Membuat file baru TANPA memanggilnya dari kode runtime = TASK FAILED.** Definisi "selesai" = kode runtime (`MobileController`, `routes/api.php`, `Kernel.php`, `VersionController`) benar-benar MEMANGGIL wrapper, DAN grep verification lulus (lihat §9).

</aside>

Mandat: **INTEGRATION-FIRST.** File wrapper sudah ada. Tugas run ini = MENYAMBUNGKAN, bukan membuat ulang.

## 3. INVARIANTS (JANGAN dilanggar)

```yaml
DO_NOT_MODIFY:
  - app/Helpers/GlobalHelper.php
  - app/Helpers/GlobalHelper2.php
  - app/Http/Controllers/*   # kecuali MobileController.php & Api/VersionController.php
  - routes/web.php
  - resources/views/**
  - database migrations / schema
ALLOWED_EDIT:
  - routes/api.php                              # HANYA blok MOBILE API + VERSION API
  - app/Http/Controllers/MobileController.php
  - app/Http/Controllers/Api/VersionController.php
  - app/Http/Kernel.php                         # HANYA menambah 1 baris alias middleware
ALLOWED_EXISTING_HELPERS (panggil, jangan ubah):
  - app/Helpers/MobileHelper.php
  - app/Http/Middleware/MobileAuth.php
  - app/Support/ApiResponse.php
RULE: jika perbaikan menuntut edit file di DO_NOT_MODIFY, STOP dan laporkan sebagai BLOCKER.
```

## 4. PRECONDITION CHECK (jalankan dulu)

```bash
test -f app/Helpers/MobileHelper.php && echo OK_MOBILEHELPER
test -f app/Http/Middleware/MobileAuth.php && echo OK_MIDDLEWARE
test -f app/Support/ApiResponse.php && echo OK_APIRESPONSE
```

Jika salah satu tidak OK → STOP, laporkan. Jangan lanjut.

---

## 5. TASK LIST (kerjakan berurutan)

<aside>
📌

Setiap TASK: TARGET → FIND → REPLACE → VERIFY. FIND harus match persis. Setelah edit, jalankan VERIFY sebelum lanjut.

</aside>

### TASK-INT-1 — Import wrapper di MobileController

**TARGET:** `app/Http/Controllers/MobileController.php`

**FIND:**

```php
use App\Helpers\GlobalHelper;
```

**REPLACE:**

```php
use App\Helpers\GlobalHelper;
use App\Helpers\MobileHelper;
use App\Support\ApiResponse;
```

**VERIFY:** `grep -c "use App\\\\Helpers\\\\MobileHelper;" app/Http/Controllers/MobileController.php` == 1

### TASK-INT-2 — Daftarkan alias middleware

**TARGET:** `app/Http/Kernel.php`

**ACTION:** di dalam array `$routeMiddleware`, tambahkan satu baris:

```php
'mobile.auth' => \App\Http\Middleware\MobileAuth::class,
```

**VERIFY:** `grep -c "mobile.auth" app/Http/Kernel.php` >= 1

### TASK-INT-3 — Pasang middleware + logout di routes

**TARGET:** `routes/api.php`

**ACTION:** ganti SELURUH blok `//--- MOBILE API ---//` (dari `Route::post('mobile/auth/login'...` s/d sebelum `//--- VERSION API ---//`) dengan blok berikut. Endpoint publik (login/register) di luar group; sisanya di dalam `mobile.auth`. Tambah `logout`.

```php
//----------------------------------------MOBILE API--------------------------------------------//

Route::post('mobile/auth/login', 'MobileController@login');
Route::post('mobile/auth/register', 'MobileController@register');

Route::group(['middleware' => 'mobile.auth'], function () {
    Route::post('mobile/auth/logout', 'MobileController@logout');

    Route::get('mobile/anggota/profil', 'MobileController@profil_anggota');
    Route::post('mobile/anggota/ubah_password', 'MobileController@ubah_password');

    Route::get('mobile/master/jenis_transaksi/{modul}', 'MobileController@jenis_transaksi');
    Route::get('mobile/master/status_transaksi/{modul}', 'MobileController@status_transaksi');

    Route::get('mobile/berita', 'MobileController@berita');
    Route::get('mobile/berita/detail', 'MobileController@detail_berita');

    Route::get('mobile/riwayat_transaksi', 'MobileController@riwayat_transaksi');

    Route::get('mobile/transaksi/{modul}', 'MobileController@transaksi');
    Route::get('mobile/transaksi/{modul}/detail', 'MobileController@detail_transaksi');

    Route::get('mobile/gaji_pokok', 'MobileController@gaji_pokok');

    Route::post('mobile/transaksi/{jenis}/proses', 'MobileController@proses_transaksi');
    Route::post('mobile/transaksi/upload_bukti_transaksi', 'MobileController@upload_bukti_transaksi');
    Route::post('mobile/transaksi/batalkan', 'MobileController@batalkan_transaksi');

    Route::get('mobile/angsuran', 'MobileController@angsuran_pinjaman');

    Route::get('mobile/produk', 'MobileController@produk');
    Route::get('mobile/produk/detail', 'MobileController@detail_produk');

    Route::get('mobile/belanja/keranjang', 'MobileController@keranjang');
    Route::post('mobile/belanja/keranjang/proses', 'MobileController@proses_keranjang');
    Route::post('mobile/belanja/keranjang/checkout', 'MobileController@checkout_keranjang');
    Route::post('mobile/belanja/batalkan', 'MobileController@batalkan_belanja');

    Route::get('mobile/belanja/riwayat/{jenis?}', 'MobileController@belanja');
    Route::get('mobile/belanja/riwayat/{jenis?}/detail', 'MobileController@detail_belanja');

    Route::get('mobile/belanja/angsuran', 'MobileController@angsuran_belanja');
    Route::get('mobile/belanja/retur', 'MobileController@retur_barang');

    Route::post('mobile/upload_avatar', 'MobileController@upload_avatar');
});
```

**CONSTRAINT:** JANGAN sentuh blok WEB API (`find_*`, `get_*`). JANGAN ubah nama/path endpoint mobile yang sudah ada.

**VERIFY:** `grep -c "mobile.auth" routes/api.php` == 1 DAN `grep -c "mobile/auth/logout" routes/api.php` == 1

### TASK-INT-4 — Tambah method logout

**TARGET:** `MobileController.php`

**ACTION:** tambahkan method di dalam class (mis. setelah `login()`):

```php
public function logout(Request $request)
{
  $anggota = Anggota::where('no_anggota', $request->no_anggota)->first();
  if (!empty($anggota)) {
    $anggota->token = null;
    $anggota->save();
  }
  return ApiResponse::success(null, 'Logout berhasil');
}
```

**VERIFY:** `grep -c "public function logout" app/Http/Controllers/MobileController.php` == 1

### TASK-INT-5 — Wire MobileHelper (BUG-03, BUG-09, BUG-12)

**TARGET:** `MobileController.php`

**EDIT 5a (BUG-09 saldo):** ganti SEMUA kemunculan `GlobalHelper::saldo_tabungan(` menjadi `MobileHelper::saldoTabungan(` (replaceAll).

**EDIT 5b (BUG-03 angsuran):**

FIND: `$anggota->total_angsuran_pinjaman = GlobalHelper::angsuran_pinjaman($anggota->no_anggota, 'all');`

REPLACE: `$anggota->total_angsuran_pinjaman = MobileHelper::angsuranPinjamanSafe($anggota->no_anggota, 'all');`

**EDIT 5c (BUG-12 stok):** ganti SEMUA kemunculan `GlobalHelper::stok_barang(` menjadi `MobileHelper::stokBarang(` (replaceAll).

**VERIFY:**

```bash
grep -c "GlobalHelper::saldo_tabungan" app/Http/Controllers/MobileController.php   # == 0
grep -c "GlobalHelper::stok_barang" app/Http/Controllers/MobileController.php      # == 0
grep -c "MobileHelper::angsuranPinjamanSafe" app/Http/Controllers/MobileController.php # == 1
```

NOTE: `validasi_transaksi()` boleh tetap memakai `GlobalHelper::angsuran_pinjaman` (logika web sama); yang WAJIB diganti hanya pemakaian di `profil_anggota`.

### TASK-INT-6 — Fix crash null-pointer (BUG-01, BUG-02, BUG-04)

**TARGET:** `MobileController.php`

**6a BUG-01 — belanja() non-toko:**

FIND:

```php
$items = ItemPenjualan::select('item_penjualan.*')->where('item_penjualan.fid_penjualan', $value->id)->first();
$items->foto = asset('assets/images/produk-default.jpg');
```

REPLACE:

```php
$items = ItemPenjualan::select('item_penjualan.*')->where('item_penjualan.fid_penjualan', $value->id)->first();
if (!empty($items)) {
  $items->foto = asset('assets/images/produk-default.jpg');
}
```

**6b BUG-02 — detail_belanja():**

FIND:

```php
$penjualan->label_status = str_replace('Konsinyasi', ucfirst($jenis), $keterangan->label);
$penjualan->keterangan_status = str_replace('Konsinyasi', ucfirst($jenis), $keterangan->keterangan);
```

REPLACE:

```php
$penjualan->label_status = (!empty($keterangan) ? str_replace('Konsinyasi', ucfirst($jenis), $keterangan->label) : '');
$penjualan->keterangan_status = (!empty($keterangan) ? str_replace('Konsinyasi', ucfirst($jenis), $keterangan->keterangan) : '');
```

**6c BUG-04 — riwayat_transaksi():**

FIND:

```php
public function riwayat_transaksi(Request $request)
{
  if ($request->jenis == 'transaksi') {
```

REPLACE:

```php
public function riwayat_transaksi(Request $request)
{
  $transaksi = collect();
  if ($request->jenis == 'transaksi') {
```

**VERIFY:** `grep -c "if (!empty(\$items)) {" app/Http/Controllers/MobileController.php` >= 1 DAN `grep -c "\$transaksi = collect();" app/Http/Controllers/MobileController.php` == 1

### TASK-INT-7 — Guard unlink (BUG-11)

**TARGET:** `MobileController.php`

**7a:** FIND `unlink(storage_path('app/' . $field->bukti_transaksi));`

REPLACE:

```php
if (file_exists(storage_path('app/' . $field->bukti_transaksi))) { unlink(storage_path('app/' . $field->bukti_transaksi)); }
```

**7b:** FIND `unlink(storage_path('app/' . $field->avatar));`

REPLACE:

```php
if (file_exists(storage_path('app/' . $field->avatar))) { unlink(storage_path('app/' . $field->avatar)); }
```

**VERIFY:** `grep -c "file_exists(storage_path" app/Http/Controllers/MobileController.php` == 2

### TASK-INT-8 — Checkout report item gagal (BUG-10)

**TARGET:** `MobileController.php` `checkout_keranjang()`

**ACTION:** kumpulkan item yang `jumlah > stok['sisa']` ke array `$failed_items` (isi `fid_produk` + `nama`), lalu ubah return akhir:

FIND:     `return array('msg' => 'success');\n  }\n\n  public function update_total_pembayaran`

REPLACE return dengan:     `return ApiResponse::success(['failed_items' => $failed_items], count($failed_items) ? 'Sebagian item gagal (melebihi stok)' : 'Checkout berhasil');`

**NOTE:** deklarasikan `$failed_items = [];` sebelum loop; di cabang `$field->jumlah > $stok['sisa']` push item alih-alih skip diam-diam.

**VERIFY:** `grep -c "failed_items" app/Http/Controllers/MobileController.php` >= 2

### TASK-INT-9 — Standarisasi response + HTTP status (BUG-06, BUG-07)

**TARGET:** `MobileController.php` (semua endpoint publik) + `VersionController.php`

**PATTERN (WAJIB diterapkan ke SETIAP endpoint yang direturn ke client):**

- Sukses: `return ApiResponse::success($data, $message, $meta);`
- Tidak ditemukan: `return ApiResponse::error('...', 404);`
- Validasi gagal: `return ApiResponse::error('...', 422);`
- Auth gagal: `return ApiResponse::error('...', 401);`

**COMPAT RULE:** letakkan payload lama di dalam `data` (JANGAN ubah nama field di dalamnya) agar app existing tidak breaking.

**CONTOH login:**

FIND:         `$return = array('token' => $anggota->token, 'no_anggota' => $anggota->no_anggota);`

REPLACE:         `return ApiResponse::success(['token' => $anggota->token, 'no_anggota' => $anggota->no_anggota], 'Login berhasil');`

(dan konversikan cabang gagal ke `ApiResponse::error(..., 401)`).

**VERIFY:** `grep -c "ApiResponse::" app/Http/Controllers/MobileController.php` >= 10 (target: seluruh endpoint terbungkus)

### TASK-INT-10 — Pagination list (BUG-05)

**TARGET:** `MobileController.php` method: `transaksi`, `berita`, `produk`, `keranjang`, `belanja`, `retur_barang`, `angsuran_belanja`.

**ACTION:** ganti pola `->limit($request->limit)->get()` menjadi `->paginate($request->input('per_page', 20))`. Ambil `page` otomatis dari query Laravel. Bungkus di `ApiResponse::success($p->items(), 'OK', ['page'=>$p->currentPage(),'per_page'=>$p->perPage(),'total'=>$p->total(),'last_page'=>$p->lastPage()])`.

**VERIFY:** `grep -c "->limit(\$request->limit)" app/Http/Controllers/MobileController.php` == 0

### TASK-INT-11 — version/check real (T7)

**TARGET:** `Api/VersionController.php` `checkUpdate()`

**ACTION:** terima param `?current=<versi_app>`; bandingkan dengan `version` di `version.json` via `version_compare()`. Set `updateAvailable = version_compare($current, $latest, '<')`. Sertakan `minSupported` dari `minDatabaseVersion` bila relevan. Return `response()->json([...], 200)`.

**VERIFY:** `grep -c "version_compare" app/Http/Controllers/Api/VersionController.php` >= 1

---

## 6. NO-REGRESSION GUARD (core)

Setelah semua TASK:

```bash
git diff --name-only main
```

Daftar file yang berubah HARUS subset dari:

```
routes/api.php
app/Http/Kernel.php
app/Http/Controllers/MobileController.php
app/Http/Controllers/Api/VersionController.php
```

Jika ada file lain (khususnya `GlobalHelper*.php`, `routes/web.php`, view) → REVERT perubahan itu.

## 7. BUILD/SANITY

```bash
php -l app/Http/Controllers/MobileController.php
php -l app/Http/Controllers/Api/VersionController.php
php -l routes/api.php
php artisan route:list --path=mobile     # semua route mobile (kecuali login/register) harus tampil dgn middleware mobile.auth
```

## 8. SMOKE TEST (wajib lulus)

```
1. POST mobile/auth/login (valid)      => 200, data.token ada
2. GET  mobile/anggota/profil (tanpa token) => 401
3. GET  mobile/anggota/profil (Bearer token) => 200, tidak 500 walau pinjaman tanpa angsuran
4. GET  mobile/belanja/riwayat/online (tanpa item) => 200, bukan 500
5. GET  mobile/belanja/riwayat/online/detail (status tak ada di keterangan) => 200, bukan 500
6. GET  mobile/produk?per_page=5&page=2 => 200, meta.page=2
7. POST mobile/auth/logout (Bearer) => 200; token di DB null
```

## 9. FINAL VERIFICATION GATE (semua HARUS true)

```bash
grep -c "mobile.auth" routes/api.php                                   # == 1
grep -c "mobile.auth" app/Http/Kernel.php                              # >= 1
grep -c "GlobalHelper::saldo_tabungan" app/Http/Controllers/MobileController.php  # == 0
grep -c "GlobalHelper::stok_barang" app/Http/Controllers/MobileController.php     # == 0
grep -c "MobileHelper::" app/Http/Controllers/MobileController.php     # >= 3
grep -c "ApiResponse::" app/Http/Controllers/MobileController.php      # >= 10
grep -c "->limit(\$request->limit)" app/Http/Controllers/MobileController.php     # == 0
grep -c "version_compare" app/Http/Controllers/Api/VersionController.php          # >= 1
```

Jika ADA yang tidak sesuai → TASK BELUM SELESAI. Jangan klaim selesai.

## 10. OUTPUT CONTRACT (format laporan balik)

```yaml
status: DONE | BLOCKED
files_changed: [ ... ]        # harus subset §6
verify_gate:                  # hasil aktual tiap perintah §9
  routes_mobile_auth: <n>
  kernel_alias: <n>
  globalhelper_saldo: <n>
  globalhelper_stok: <n>
  mobilehelper_calls: <n>
  apiresponse_calls: <n>
  limit_request: <n>
  version_compare: <n>
smoke_test: [ pass/fail per langkah §8 ]
blockers: [ ... ]             # jika menyentuh DO_NOT_MODIFY
```

<aside>
✅

DEFINISI SELESAI: §9 semua sesuai + §8 smoke test lulus + §6 tidak ada file core berubah. Jika tidak, status = BLOCKED beserta alasan. DILARANG melaporkan DONE hanya karena file wrapper ada.

</aside>