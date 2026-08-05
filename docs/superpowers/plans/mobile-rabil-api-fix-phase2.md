# [IMPLEMENTATION PLAN — Phase 2] eSIMKO Mobile API + Android fix semua phase

## 0. CONTEXT

Audit (PRD `superpowers/PRD — Mobile API & Tech Stack eSIMKO ...md` vs code) menemukan 3 lapis masalah:

- **A. Backend 500**: 4 endpoint list di `app/Http/Controllers/MobileController.php` melempar exception undefined-var (`$paginated`/`$items`/`$p` tak pernah didefinisikan): `keranjang()`, `belanja()`, `retur_barang()`, `berita()`.
- **B. Kontrak client ↔ backend mismatch**: DTO/request di `EsimkoMobile/` pakai nama field rekaan, backend punya field asli (source of truth = PRD).
- **C. Fitur UI belum ada**: register, setoran/penarikan, angsuran, berita, cart/checkout, history, settings, version-check.

Lokasi kerja: **repo root main** (persetujuan user). Backend tracked; `EsimkoMobile/` untracked (0 file di git) — biarkan sampai akhir.

## 1. PRIME DIRECTIVE

Fix semua phase sampai masalah selesai. Source of truth kontrak = PRD. Backend = authoritative. Fix backend dulu (Fase 1-2), lalu align client DTO (Fase 3), lalu fitur UI (Fase 4-6), lalu security (Fase 7), lalu prod config (Fase 8).

## 2. GUARDRAILS

- Semua response mobile lewat `App\Support\ApiResponse::success|error` — jangan return raw JSON.
- Envelope: `{success, message, data[, meta]}`. Pesan error Bahasa Indonesia.
- Jangan tambah dependency baru tanpa kebutuhan.
- `EsimkoMobile/` tetap untracked selama proses; jangan force-add.
- Perubahan backend harus tetap compatible dgn payload yang app kirim setelah Fase 3.
- Setiap endpoint list return `meta` `{page, per_page, total, last_page}`.
- Tidak boleh lakukan breaking change pada `profil_anggota` (app existing sudah konsumsi).

## 3. TASKS

### Task 1 — Fix 4 endpoint 500 (backend)
File: `app/Http/Controllers/MobileController.php`
Ganti baris return copy-paste (`return $paginated ? ... : ApiResponse::success($items)`) pada:
- `keranjang()` ~baris 630 → `ApiResponse::success($result)` (berita, keranjang sendiri list non-paginate)
- `belanja()` ~baris 777 → pakai `$result` + meta pagination (`$p`)
- `retur_barang()` ~baris 933 → pakai `$result` + meta pagination (`$p`)
- `berita()` ~baris 960 → pakai `$result` + meta pagination (`$p`)

Non-paginate endpoint (`keranjang`) cukup `ApiResponse::success($result)`. Endpoint yang sudah paginate (`$p` ada) return meta lengkap. Pastikan variabel yang direfer sudah benar (hasil query ada di `$result`, bukan `$items`).

### Task 2 — Koreksi kontrak backend (source of truth PRD)
File: `app/Http/Controllers/MobileController.php`
- `login()`: `'avatar' => $anggota->foto ?? null` → kolom aslinya `avatar`, bungkus `asset('storage/'.$anggota->avatar)` (fallback placeholder bila kosong).
- `login()` failed: `ApiResponse::success(['msg'=>...])` → `ApiResponse::error($msg, 401)` untuk password salah, 404 untuk anggota tak ditemukan.
- `register()`: hilangkan double-nesting — return object langsung di `data` (bukan `['data'=>$field]`); jangan ekspos kolom `password`/`token` (pilih kolom profil saja).
- `gaji_pokok()`: hilangkan double-nesting.
- `transaksi()`: ganti `$limit = $request->limit ?: 10` → pakai `$request->input('per_page', 20)`; jadikan paginate default 20, konsisten endpoint lain.
- `detail_produk()` & `detail_belanja()`: saat `$data`/`$penjualan` null → `ApiResponse::error(..., 404)` (bukan 200 + `data:null`).
- `ubah_password()` & `login()`: bungkus `decrypt()` dalam `try/catch` → `ApiResponse::error(..., 400)` bila `DecryptException`.
- `belanja()` & `detail_belanja()`: beri default `$jenis = 'toko'` (route param `{jenis?}` optional).

### Task 3 — Align kontrak client DTO ke backend
File: `EsimkoMobile/app/src/main/java/com/esimko/mobile/data/remote/dto/*.kt` + `data/remote/api/*.kt`
Sesuaikan kutub client biar cocok backend yang sudah dibetulkan:
- `TransactionApi.getTransactions`: `@Query("tanggal_awal")` → `@Query("tanggal_mulai")`.
- `ApiResponse.MetaResponse`: `current_page` → `page` (backend emit `page`).
- `CartRequest`: `{produk_id, qty}` → `{id, jumlah, action}`. Perlu tambah field `action` (add/delete).
- `ShoppingApi.checkout()`: tambah `@Body` payload `barang[]` + `jumlah[]` (subscribe cart items).
- `CheckoutResponse`: ganti field jadi `failed_items`.
- `ChangePasswordRequest`: tambah `ulangi_password_baru`.
- `LoginResponse`: avatar key sudah ada — pastikan parsing nama `avatar` (backend fix Task 2).
- `ProductResponse`/`ProductDetailResponse`: `nama`→`nama_produk`, `gambar`→`foto`, tambah `kelompok`/`kategori`/`satuan`; id sesuaikan tipe (kode produk string? backend `where('produk.kode', $request->id)`).
- Verifikasi ulang `InstallmentResponse`, `PurchaseHistoryResponse`, `PurchaseDetailResponse`, `ReturnResponse`, `TransactionResponse` field names terhadap return backend.

### Task 4 — Auth flow (logout + session restore)
File: app + backend
- `ProfileRepositoryImpl.logout()`: panggil `AuthApi.logout()` server (POST `mobile/auth/logout`) dulu, lalu `tokenStore.clear()`.
- `ProfileTab`/`ProfileScreen`: `onLogout` → navigate kembali ke route `login` (bukan kosong).
- `EsimkoNavHost`: cek `isLoggedIn()`; kalau sudah login start ke `home`, kalau tidak `login`.

### Task 5 — Backend write-path crash guard
File: `app/Http/Controllers/MobileController.php`
- `validasi_transaksi()`: guard `$tenor[$request->jenis_pinjaman]` (bila key tak ada → error 400); pakai `MobileHelper::angsuranPinjamanSafe` bukan `GlobalHelper::angsuran_pinjaman` unguarded.
- `proses_transaksi()`: bila `action != 'add'` dan `Transaksi::find($request->id)` null → `ApiResponse::error(..., 404)` sebelum update/delete.
- `checkout_keranjang()`: guard `$request->jumlah[$key]` — bila `jumlah[]` tak dikirim lengkap → error 400, bukan undefined-offset 500.

### Task 6 — Fitur UI baru
File: `EsimkoMobile/app/src/main/java/com/esimko/mobile/ui/**`
- **Register screen**: `ui/auth/register/RegisterScreen.kt` + `RegisterViewModel` + route di `EsimkoNavHost` + `AuthApi.register()` + DTO + repo. Data: no_anggota, no_ktp, telepon, password, confirm-password. Integrasi login link.
- **Savings lengkap**: `SavingsTab` tambah header saldo (dari profil), form setoran/penarikan (panggil `processTransaction`), upload bukti, cancel. Wired ke `TransactionApi` yang sudah dipakai.
- **Angsuran pinjaman**: `ui/installment/` screen list angsuran (panggil `InstallmentApi.getLoanInstallments`) + tab/nav entry.
- **Berita**: `ui/news/NewsScreen` list + detail (panggil `NewsApi`), wire `NewsViewModel` (sekarang orphan); ganti placeholder lorem-ipsum di `DashboardTab`.
- **Shopping lengkap**: cart, checkout, purchase history, retur, angsuran belanja — re-use 9 method `ShoppingRepository` yang sudah ada tapi belum ada UI caller.
- **History fix**: navigasi ke `HistoryTab` dengan `transactionId` dari item detail transaksi (bukan `transactionId=0L` yang tak pernah terisi).
- **Settings**: reachable dari nav; baca versi/build dari `BuildConfig`; wire version-check.

### Task 7 — Security (PRD §10)
File: backend
- Migrasi password `encrypt/decrypt` → `Hash::make`/`Hash::check` pada `login()`, `ubah_password()`, `register()`.
- Token: tambah expiry/rotation (pakai `login_at` — bila lebih dari N hari → invalid) ATAU catat keterbatasan. Pilih yang minimal: cek `login_at` tidak lebih dari 30 hari.
- `MobileAuth`: batasi penerimaan token ke header `Authorization` saja (hapus fallback `?token=`/body).
- Audit backdoor `sembarang` di `GlobalHelper::verifyAdminPassword` — hapus/neutral.
- `version.json`: tambah `mobile_version` + `min_mobile_version` key biar `version/check` berfungsi.

### Task 8 — Production config
File: `EsimkoMobile/app/build.gradle.kts`
- BASE_URL per buildType: debug → `http://10.10.6.9:8080/api/` (test local), release → `https://esimko.com/api/`.
- Batasi cleartext (`usesCleartextTraffic`) ke build debug saja (manifest network security config).

## 4. VERIFY
- `php -l app/Http/Controllers/MobileController.php` bersih.
- Backend: endpoint 500 jadi return valid. Cek pakai curl bila app jalan.
- App: `./gradlew assembleDebug` sukses di `EsimkoMobile/`.
- Semua respons mobile via `ApiResponse` envelope.
