# Implementation Plan (Subagent) — Perbaikan Backend agar Mobile API Berfungsi

<aside>
🤖

**Dokumen kerja untuk subagent.** Tujuan: memperbaiki backend web (Laravel `torpedoliar/Esimko`) **hanya pada ruang lingkup yang terkait pengembangan aplikasi mobile ke depan**, agar seluruh endpoint `mobile/*` berfungsi benar, konsisten, dan aman dipakai app Android. Referensi analisis: dokumen "Audit Fungsional Mobile API Esimko" (halaman induk).

</aside>

## 1. Tujuan

1. Menstabilkan Mobile API (hilangkan crash 500) tanpa mengganggu fitur core web (akuntansi, POS, payroll).
2. Menstandarkan kontrak API (format response + HTTP status) agar mudah dikonsumsi Retrofit/Kotlin.
3. Menambahkan autentikasi berbasis token yang benar.
4. Menyediakan pagination & memperbaiki performa list untuk kebutuhan mobile.

## 2. Ruang Lingkup

### ✅ In-scope (boleh dikerjakan)

- `routes/api.php` — hanya blok **MOBILE API** dan **VERSION API**.
- `app/Http/Controllers/MobileController.php`
- `app/Http/Controllers/Api/VersionController.php`
- **File baru** di lapisan mobile: `app/Helpers/MobileHelper.php` (atau `app/Services/MobileService.php`), `app/Http/Middleware/MobileAuth.php`, `app/Http/Resources/*` (opsional), `app/Support/ApiResponse.php`.
- Konfigurasi yang diperlukan untuk token mobile (mis. Sanctum) selama tidak mengubah alur login web.

### ⛔ Out-of-scope (JANGAN disentuh)

- `app/Helpers/GlobalHelper.php` dan `GlobalHelper2.php` — **helper inti bersama**. Tidak boleh diubah. Bila butuh perilaku berbeda, **bungkus** di `MobileHelper`.
- Controller web/admin lain, blok **WEB API** (`find_*`, `get_*`) di `routes/api.php`.
- Skema/ migrasi tabel core, view Blade, alur autentikasi web (`AuthController` web session).
- Modul akuntansi, POS backoffice, payroll, jurnal.

<aside>
🚧

**Aturan emas:** jika sebuah perbaikan mengharuskan mengubah file out-of-scope, JANGAN lakukan. Buat pembungkus di lapisan mobile, atau catat sebagai blocker di bagian "Catatan untuk Human Review".

</aside>

## 3. Guardrails untuk Subagent

- Kerjakan **satu task = satu commit/PR** sesuai ID task (lihat §Branching).
- Sebelum mengubah file, baca dulu file terkait dan pastikan masih in-scope.
- Jangan menghapus/menonaktifkan fitur web yang sudah ada.
- Jangan mengubah nama/route endpoint mobile yang sudah dipakai (kompatibilitas app). Endpoint baru boleh ditambah.
- Setiap perubahan wajib punya **acceptance criteria terpenuhi** + langkah verifikasi manual.
- Bahasa pesan error untuk user: **Bahasa Indonesia**.
- Jangan pernah mengembalikan field sensitif (`password`, token orang lain) di response.

## 4. Arsitektur Target (Lapisan Mobile)

```
Request mobile/*
   → Middleware MobileAuth (validasi token → set anggota aktif)
   → MobileController (tipis: validasi input + panggil service)
   → MobileHelper/MobileService (logika mobile + wrapper null-safe atas GlobalHelper)
   → ApiResponse (bungkus {success, message, data, meta} + HTTP status)
```

Prinsip: `MobileController` tidak memanggil `GlobalHelper` core secara langsung untuk kasus yang berisiko (BUG-03/09/12); semua lewat `MobileHelper` yang menambahkan null-safety & normalisasi.

## 5. Task Breakdown

<aside>
📌

Setiap task berdiri sendiri, punya Acceptance Criteria (AC) dan Verifikasi. Kerjakan sesuai urutan di §6 kecuali ada instruksi lain.

</aside>

### T0 — Fondasi lapisan mobile

- **Tujuan:** menyiapkan `ApiResponse` dan `MobileHelper` sebagai fondasi task lain.
- **File:** `app/Support/ApiResponse.php` (baru), `app/Helpers/MobileHelper.php` (baru).
- **Langkah:**
    - Buat `ApiResponse::success($data, $message = 'OK', $meta = null, $code = 200)` dan `ApiResponse::error($message, $code = 400, $errors = null)` yang mengembalikan `response()->json(...)` bentuk `{success, message, data, meta}`.
    - Buat kelas `MobileHelper` kosong sebagai tempat wrapper.
- **AC:** helper bisa dipanggil; belum mengubah endpoint apa pun.
- **Verifikasi:** unit kecil / `php artisan tinker` memanggil `ApiResponse::success([])`.

### T1 — Fix crash null-pointer di MobileController (BUG-01, BUG-02, BUG-04)

- **Tujuan:** hilangkan error 500 pada riwayat & detail belanja, dan riwayat transaksi.
- **File:** `MobileController.php` (in-scope).
- **Langkah:**
    - `belanja()` cabang non-`toko`: bungkus akses `$items->foto` dengan `if(!empty($items))` (samakan dengan cabang toko).
    - `detail_belanja()`: cek `!empty($keterangan)` sebelum akses `->label`/`->keterangan`; beri default string kosong.
    - `riwayat_transaksi()`: inisialisasi `$transaksi = collect()` di awal agar `jenis` tak dikenal tidak error.
- **AC:** ketiga endpoint tidak error saat data kosong/parsial; mengembalikan data valid atau list kosong.
- **Verifikasi:** panggil endpoint dengan penjualan tanpa item, status tak ada di tabel keterangan, dan `jenis` kosong.

### T2 — Wrapper null-safe untuk helper core (BUG-03, BUG-09, BUG-12)

- **Tujuan:** amankan pemakaian helper core dari sisi mobile TANPA mengubah `GlobalHelper`.
- **File:** `MobileHelper.php` (baru), `MobileController.php` (ganti pemanggilan).
- **Langkah:**
    - `MobileHelper::angsuranPinjamanSafe($anggota, $jenis)`: replikasi kebutuhan angsuran dengan null-check (mis. lewati transaksi tanpa baris angsuran) alih-alih memanggil `GlobalHelper::angsuran_pinjaman` yang bisa null-pointer. (BUG-03)
    - `MobileHelper::saldoTabungan($anggota, $jenis)`: tetapkan **satu** definisi grouping resmi untuk mobile (dokumentasikan di komentar) dengan memanggil `GlobalHelper::saldo_tabungan` per-jenis yang eksplisit, sehingga tidak bergantung perbedaan `GlobalHelper` vs `GlobalHelper2`. (BUG-09)
    - `MobileHelper::stokBarang($id)`: panggil `GlobalHelper::stok_barang` lalu normalisasi array output agar key `retur_penjualan`/`sisa`/`terjual` selalu ada. (BUG-12)
    - Ganti pemanggilan terkait di `profil_anggota`, `produk`, `detail_produk`, `keranjang` menjadi lewat `MobileHelper`.
- **AC:** endpoint profil tidak error walau ada pinjaman tanpa angsuran; nilai saldo konsisten; output stok selalu punya key lengkap. `GlobalHelper` tidak berubah.
- **Verifikasi:** buat data pinjaman tanpa angsuran → profil sukses; bandingkan nilai saldo sebelum/sesudah.

### T3 — Standarisasi format response & HTTP status (BUG-06, BUG-07)

- **Tujuan:** semua endpoint mobile balik `{success, message, data, meta}` + status code benar.
- **File:** `MobileController.php`, `VersionController.php`, pakai `ApiResponse`.
- **Langkah:**
    - Bungkus seluruh return endpoint mobile dengan `ApiResponse::success/error`.
    - Petakan kondisi gagal ke status: tidak ditemukan → 404, validasi → 422, auth → 401, sukses → 200/201.
    - Pertahankan **key data** yang sudah dipakai app di dalam `data` agar tidak breaking (atau sediakan versi terdokumentasi).
- **AC:** setiap endpoint konsisten formatnya; kode status sesuai kondisi.
- **Verifikasi:** cek beberapa endpoint sukses & gagal via Postman; pastikan status code tepat.

### T4 — Autentikasi token mobile (BUG-08)

- **Tujuan:** identitas user diambil dari token, bukan `no_anggota` dari client.
- **File:** `app/Http/Middleware/MobileAuth.php` (baru), `routes/api.php` (grup mobile), `MobileController@login`, konfig token.
- **Langkah:**
    - Gunakan token yang sudah digenerate saat `login` (kolom `token` pada anggota) ATAU pasang Laravel Sanctum (kompatibel Laravel 7) bila disepakati.
    - Buat middleware `MobileAuth`: baca header `Authorization: Bearer <token>`, resolve anggota, tolak 401 bila invalid.
    - Bungkus semua route `mobile/*` (kecuali `login`, `register`, `version`) dengan middleware ini.
    - Ganti seluruh pemakaian `$request->no_anggota` menjadi anggota hasil resolusi token.
    - Tambah endpoint `mobile/auth/logout` (revoke token).
- **AC:** endpoint terproteksi menolak request tanpa token valid; `no_anggota` tidak lagi dipercaya dari input.
- **Verifikasi:** akses endpoint tanpa/dengan token; pastikan 401 vs 200.
- **Catatan:** JANGAN mengubah alur login web/session admin.

### T5 — Pagination untuk semua list (BUG-05)

- **Tujuan:** dukung infinite scroll / load-more di Android.
- **File:** `MobileController.php` (endpoint list), `ApiResponse` (isi `meta`).
- **Langkah:**
    - Ganti `->limit($request->limit)->get()` menjadi `->paginate($perPage)` (default `per_page=20`, terima param `page`).
    - Isi `meta` dengan `page`, `per_page`, `total`, `last_page`.
    - Terapkan pada: `transaksi`, `berita`, `produk`, `keranjang`, `belanja`, `retur_barang`, `angsuran*`.
- **AC:** endpoint list mengembalikan data ter-paginate + `meta`; halaman berikutnya berbeda.
- **Verifikasi:** panggil `?page=1` dan `?page=2`, pastikan hasil bergeser.

### T6 — Perbaikan performa N+1 (dukungan skala mobile)

- **Tujuan:** kurangi beban query pada list produk/keranjang/retur.
- **File:** `MobileController.php`, `MobileHelper.php`.
- **Langkah:**
    - Eager-load / batch ambil `FotoProduk` untuk sekumpulan produk sekaligus (hindari query per item).
    - Batch hitung stok bila memungkinkan (atau cache ringan per request).
- **AC:** jumlah query per request list turun signifikan; hasil tetap sama.
- **Verifikasi:** aktifkan query log/Debugbar pada endpoint `produk`, bandingkan jumlah query sebelum/sesudah.

### T7 — Kelengkapan fitur (BUG-10, BUG-11, version/check)

- **Tujuan:** rapikan sisa isu fungsional area mobile.
- **File:** `MobileController.php`, `VersionController.php`.
- **Langkah:**
    - `checkout_keranjang`: kumpulkan item yang gagal (melebihi stok) dan kembalikan dalam `data.failed_items`. (BUG-10)
    - `upload_avatar` & `upload_bukti_transaksi`: cek `file_exists()` sebelum `unlink()`. (BUG-11)
    - `version/check`: implementasikan perbandingan versi nyata (mis. versi minimum dari `version.json`/konfig) sehingga `updateAvailable` akurat untuk fitur force-update.
- **AC:** checkout memberi tahu item gagal; tidak ada warning unlink; `version/check` mengembalikan status update yang benar.
- **Verifikasi:** checkout dgn qty > stok; upload saat file lama tidak ada; panggil `version/check` dengan versi berbeda.

### T8 — Dokumentasi kontrak API (acuan Android)

- **Tujuan:** hasil final jadi sumber kebenaran model data app.
- **File:** koleksi Postman/Insomnia + dokumen kontrak (bisa halaman Notion baru).
- **Langkah:** untuk tiap endpoint mobile, catat method, path, header, request body/params, contoh response sukses & gagal.
- **AC:** semua endpoint mobile terdokumentasi request/response final.
- **Verifikasi:** review silang dengan implementasi.

## 6. Urutan Eksekusi & Dependensi

| Urutan | Task | Bergantung pada |
| --- | --- | --- |
| 1 | T0 Fondasi | — |
| 2 | T1 Fix crash | — (bisa paralel dgn T0) |
| 3 | T2 Wrapper helper | T0 |
| 4 | T3 Standarisasi response | T0 |
| 5 | T4 Auth token | T3 |
| 6 | T5 Pagination | T3 |
| 7 | T6 Performa | T5 |
| 8 | T7 Kelengkapan fitur | T3 |
| 9 | T8 Dokumentasi kontrak | T1–T7 |

## 7. Strategi Testing & Verifikasi

- **Regression web:** setelah tiap task, pastikan tidak ada file out-of-scope yang berubah (`git diff --name-only` hanya berisi file in-scope).
- **Smoke test mobile:** jalankan alur `login → profil → transaksi → produk → keranjang → checkout` end-to-end.
- **Edge cases:** data kosong, pinjaman tanpa angsuran, penjualan tanpa item, status tak ada di tabel keterangan, token invalid.
- Sertakan koleksi Postman untuk reproduksibilitas.

## 8. Konvensi Branch & PR

- Branch: `feat/mobile-api/T<nomor>-<slug>` (mis. `feat/mobile-api/T1-fix-null-crash`).
- Satu PR per task; judul PR menyertakan ID task dan ringkasan.
- Deskripsi PR wajib memuat: ringkasan perubahan, AC yang terpenuhi, langkah verifikasi, dan konfirmasi "tidak menyentuh file out-of-scope".

## 9. Definition of Done (per task)

- [ ]  Acceptance Criteria terpenuhi.
- [ ]  Hanya file in-scope yang berubah.
- [ ]  `GlobalHelper`/core tidak diubah.
- [ ]  Endpoint mobile terkait diuji manual (sukses + gagal).
- [ ]  Tidak ada regresi pada fitur web.
- [ ]  Response konsisten `{success, message, data, meta}` (untuk task yang menyentuh response).

## 10. Catatan untuk Human Review

<aside>
🗣️

Keputusan yang butuh persetujuan manusia sebelum dieksekusi subagent:

</aside>

- Pilihan mekanisme token: **kolom `token` existing** vs **Laravel Sanctum**.
- Apakah boleh menambah field baru pada `data` response (kompatibilitas app versi lama).
- Penanganan isu keamanan (backdoor password, `encrypt()` vs bcrypt) — di luar scope fungsional ini, perlu jadwal terpisah.
- Perbedaan grouping `saldo_tabungan`: konfirmasi definisi resmi (jenis mana masuk Sukarela vs Hari Raya).