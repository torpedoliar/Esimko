# PRD — Mobile API & Tech Stack eSIMKO

<aside>
📘

**Product Requirements Document (PRD)** untuk Mobile API aplikasi **eSIMKO** (Elektronik Sistem Informasi & Manajemen Koperasi) — acuan pengembangan aplikasi Android. Berbasis kode terbaru repo `torpedoliar/Esimko` @ commit `84a4529fa` (branch `main`). Envelope response, auth token, dan endpoint sudah tervalidasi terhadap kode aktual.

</aside>

## 1. Ringkasan Produk

eSIMKO adalah sistem manajemen koperasi. Mobile API menyediakan akses untuk anggota koperasi via aplikasi mobile terhadap fitur: autentikasi, profil & saldo, simpan-pinjam, angsuran, belanja/POS koperasi, retur, berita, dan pengecekan versi aplikasi.

**Tujuan PRD ini:** menjadi sumber kebenaran (single source of truth) kontrak Mobile API + tech stack untuk tim yang membangun aplikasi Android.

| Aspek | Nilai |
| --- | --- |
| Base path API | `/api` (Laravel default) |
| Prefix mobile | `/api/mobile/*` |
| Versi rilis backend | 1.1.0 |
| Auth mobile | Token (Bearer) via middleware `mobile.auth` |
| Format response | Envelope standar `ApiResponse` |
| Status | Fondasi siap; beberapa isu keamanan masih terbuka (lihat §10) |

## 2. Tech Stack

### 2.1 Backend (server API)

| Komponen | Teknologi |
| --- | --- |
| Framework | Laravel 7.x |
| Bahasa | PHP `^7.2.5 \ |
| Database | MySQL |
| Arsitektur | Monolitik MVC + lapisan mobile terpisah (`MobileController` → `MobileHelper` → `ApiResponse`) |
| Dependency kunci | `laravel/framework ^7.29`, `maatwebsite/excel ^3.1` (export/import Excel), `guzzlehttp/guzzle`, `fideloper/proxy`, `fruitcake/laravel-cors` |
| Autentikasi mobile | Token acak 32 char (`Str::random(32)`) disimpan di kolom `anggota.token`, divalidasi middleware `MobileAuth` |
| Helper inti | `GlobalHelper` (bersama web+mobile), `MobileHelper` (khusus mobile, null-safe wrapper) |

### 2.2 Frontend web (admin) & Deployment

| Komponen | Teknologi |
| --- | --- |
| UI web admin | Blade templates + Less/CSS (bukan bagian mobile) |
| Kontainerisasi | Docker + Docker Compose (`docker-compose.yml`, `.dev`, `.prod`, `.npm`) |
| Reverse proxy | Nginx / NGINX Proxy Manager (NPM) |
| Deploy scripts | PowerShell (`deploy.ps1`, `deploy-prod.ps1`), Batch (`deploy.bat`), Shell (`deploy-ubuntu.sh`, `update.sh`) |
| Versioning | `version.json`  • endpoint `version` & `version/check` |
| Storage file | Disk `public` (`php artisan storage:link` diperlukan) |

### 2.3 Rekomendasi stack klien Android

- Bahasa: Kotlin
- HTTP: Retrofit + OkHttp (interceptor untuk header `Authorization`)
- Serialisasi: Moshi/Gson mengikuti envelope `{success, message, data, meta}`
- Penyimpanan token: EncryptedSharedPreferences / DataStore

## 3. Arsitektur & Alur Request

```
Android App
  → HTTPS /api/mobile/*  (header: Authorization: Bearer <token>)
    → Middleware mobile.auth (MobileAuth): validasi token → set no_anggota
      → MobileController (endpoint logic)
        → MobileHelper (wrapper null-safe atas GlobalHelper) / GlobalHelper (core)
          → MySQL
      ← ApiResponse::success|error  (envelope + HTTP status)
```

## 4. Autentikasi & Otorisasi

### 4.1 Model token

- Login mengembalikan `token` (32 char). Klien menyimpan & mengirim di setiap request terproteksi.
- Header utama: `Authorization: Bearer <token>`. Middleware `MobileAuth` juga menerima fallback `?token=` atau field body `token` (tidak direkomendasikan untuk produksi).
- Middleware me-resolve anggota dari token lalu meng-inject `no_anggota` ke request (klien tidak perlu lagi mengirim `no_anggota`).
- Logout meng-null-kan token di server.

### 4.2 Endpoint publik vs terproteksi

| Sifat | Endpoint |
| --- | --- |
| Publik (tanpa token) | `mobile/auth/login`, `mobile/auth/register`, `version`, `version/check` |
| Terproteksi (`mobile.auth`) | Semua endpoint `mobile/*` lainnya |

## 5. Standar Response (Envelope)

Semua endpoint mobile mengembalikan envelope konsisten dari kelas `App\Support\ApiResponse`.

**Sukses:**

```json
{
  "success": true,
  "message": "OK",
  "data": { },
  "meta": { "page": 1, "per_page": 20, "total": 135, "last_page": 7 }
}
```

`meta` hanya muncul pada endpoint list ter-paginate.

**Gagal:**

```json
{
  "success": false,
  "message": "Pesan error (Bahasa Indonesia)",
  "errors": { }
}
```

### 5.1 Konvensi HTTP status

| Kondisi | Status |
| --- | --- |
| Sukses | 200 |
| Data tidak ditemukan | 404 |
| Validasi gagal | 422 (bila diterapkan) |
| Token hilang/invalid | 401 |
| Error umum | 400 |

### 5.2 Pagination

- Query param: `page` (nomor halaman), `per_page` (default 20).
- Metadata di `meta`: `page`, `per_page`, `total`, `last_page`.
- Berlaku pada endpoint list: `produk`, `berita`, `belanja/riwayat`, `retur`, `belanja/angsuran`; `transaksi` mendukung mode paginate saat `page` dikirim.

## 6. Katalog Endpoint Mobile API

Base: `/api/mobile`. Semua terproteksi kecuali ditandai (PUBLIK).

### 6.1 Autentikasi & Akun

| Method | Path | Auth | Deskripsi | Param utama |
| --- | --- | --- | --- | --- |
| POST | `auth/login` | PUBLIK | Login anggota, keluarkan token | `username` (no_anggota), `password` |
| POST | `auth/register` | PUBLIK | Registrasi anggota baru (status awal 1) | data profil (nama, ktp, dll) |
| POST | `auth/logout` | Token | Revoke token aktif | — |
| GET | `anggota/profil` | Token | Profil + ringkasan saldo, pinjaman, angsuran | — |
| POST | `anggota/ubah_password` | Token | Ganti password | `password_lama`, `password_baru`, `ulangi_password_baru` |
| POST | `upload_avatar` | Token | Upload foto profil (disk public) | file `avatar` |

### 6.2 Master data & Berita

| Method | Path | Auth | Deskripsi | Param |
| --- | --- | --- | --- | --- |
| GET | `master/jenis_transaksi/{modul}` | Token | Daftar jenis transaksi (`simpanan` → id 1-8, lainnya → 9-11) | path `modul` |
| GET | `master/status_transaksi/{modul}` | Token | Daftar status transaksi | path `modul` |
| GET | `berita` | Token | List berita (paginate) | `search`, `page`, `per_page` |
| GET | `berita/detail` | Token | Detail berita + lampiran | `id` |

### 6.3 Simpan-Pinjam (Transaksi)

| Method | Path | Auth | Deskripsi | Param |
| --- | --- | --- | --- | --- |
| GET | `transaksi/{modul}` | Token | List transaksi (`simpanan`/`pinjaman`), pinjaman menyertakan sisa angsuran/tenor | `jenis`, `status`, `tanggal_mulai`, `tanggal_akhir`, `page`, `per_page` |
| GET | `transaksi/{modul}/detail` | Token | Detail transaksi + status label | `id` |
| POST | `transaksi/{jenis}/proses` | Token | Buat/ubah transaksi (`setoran`/`penarikan`/`pinjaman`) + generate angsuran | `action`, `nominal`, `tenor`, `jenis_pinjaman`, `gaji_pokok`, dll |
| POST | `transaksi/upload_bukti_transaksi` | Token | Upload bukti transaksi | `id`, file `bukti_transaksi` |
| POST | `transaksi/batalkan` | Token | Batalkan transaksi (status 5) | `id` |
| GET | `riwayat_transaksi` | Token | Riwayat verifikasi/aktivitas transaksi | `id`, `jenis` (`transaksi`/`penjualan`) |
| GET | `angsuran` | Token | Jadwal/daftar angsuran pinjaman | `id` atau default anggota |
| GET | `gaji_pokok` | Token | Data gaji pokok anggota | — |

### 6.4 Belanja / POS Koperasi

| Method | Path | Auth | Deskripsi | Param |
| --- | --- | --- | --- | --- |
| GET | `produk` | Token | List produk + foto + kategori (paginate, batch foto) | `search`, `page`, `per_page` |
| GET | `produk/detail` | Token | Detail produk + stok (terjual/sisa) | `id` (kode produk) |
| GET | `belanja/keranjang` | Token | Isi keranjang anggota | `search` |
| POST | `belanja/keranjang/proses` | Token | Tambah/ubah/hapus item keranjang (cek stok) | `id`, `jumlah`, `action` |
| POST | `belanja/keranjang/checkout` | Token | Checkout keranjang → penjualan; `failed_items` bila stok kurang | `barang[]`, `jumlah[]` |
| POST | `belanja/batalkan` | Token | Batalkan belanja | `id`, `jenis` |
| GET | `belanja/riwayat/{jenis?}` | Token | Riwayat belanja (`toko`/`konsinyasi`/`online`) (paginate) | `page`, `per_page` |
| GET | `belanja/riwayat/{jenis?}/detail` | Token | Detail belanja + item + status | `id` |
| GET | `belanja/angsuran` | Token | Angsuran belanja kredit (paginate) | `id` atau default |
| GET | `belanja/retur` | Token | Daftar retur barang (paginate) | `search`, `page`, `per_page` |

### 6.5 Versi Aplikasi

| Method | Path | Auth | Deskripsi |
| --- | --- | --- | --- |
| GET | `version` | PUBLIK | Info versi dari `version.json` |
| GET | `version/check` | PUBLIK | Cek update: `updateAvailable` & `forceUpdate` via `version_compare` (param `version` = versi app klien) |

## 7. Contoh Kontrak Endpoint Kunci

### 7.1 POST `mobile/auth/login`

Request:

```json
{ "username": "K 0001", "password": "rahasia" }
```

Response sukses:

```json
{ "success": true, "message": "OK", "data": { "token": "<32char>", "no_anggota": "K 0001" } }
```

### 7.2 GET `mobile/produk?page=1&per_page=20`

Response (ringkas):

```json
{
  "success": true,
  "message": "OK",
  "data": [ { "id": 1, "nama_produk": "...", "foto": "https://.../storage/...", "kelompok": "...", "kategori": "..." } ],
  "meta": { "page": 1, "per_page": 20, "total": 135, "last_page": 7 }
}
```

### 7.3 POST `mobile/belanja/keranjang/checkout`

Response:

```json
{ "success": true, "message": "Checkout berhasil", "data": { "failed_items": [] } }
```

## 8. Glosarium Domain (untuk model data Android)

| Konsep | Nilai |
| --- | --- |
| Jenis transaksi simpanan | id 1–8 |
| Jenis transaksi pinjaman | 9 (jangka panjang), 10 (jangka pendek), 11 (barang) |
| Setoran / Penarikan | jenis 4 (setoran), 6 (penarikan) |
| Grouping saldo | Total Simpanan (1–8), Sukarela (3,5,6), Hari Raya (4,7), Pokok (1), Wajib (2) |
| Tenor maksimal | pinjaman 9 = 50 bulan; 10 & 11 = 18 bulan |
| Status transaksi khusus | 5 = dibatalkan; angsuran status 6 = lunas |
| Jenis belanja | `toko`, `konsinyasi`, `online` |
| Metode pembayaran kredit | id 3 |

## 9. Kebutuhan Non-Fungsional

- **Performa:** endpoint list wajib paginate; `produk` sudah batch foto (hindari N+1). Endpoint lain (`keranjang`) masih ada N+1 minor.
- **Konsistensi:** semua response via `ApiResponse`; pesan error Bahasa Indonesia.
- **Kompatibilitas:** payload lama diletakkan di `data` agar app existing tidak breaking.
- **File & media:** URL file dikembalikan absolut (`asset('storage/...')`); butuh `storage:link`.

## 10. Isu Terbuka & Risiko (harus ditangani)

<aside>
⚠️

Berikut item keamanan/kualitas yang MASIH terbuka dan direkomendasikan masuk backlog sebelum rilis produksi.

</aside>

- 🔴 **Password disimpan reversible** (`encrypt()`/`decrypt()`), bukan `bcrypt`/`Hash`. Perlu migrasi hashing.
- 🔴 **Backdoor password `sembarang`** masih ada di jalur auth web (`GlobalHelper::verifyAdminPassword`) — audit terpisah.
- 🟡 **Token sederhana** (string di kolom `anggota.token`), belum expiry/rotation. Pertimbangkan Laravel Sanctum + masa berlaku.
- 🟡 **Middleware menerima token via query/body** sebagai fallback — sebaiknya batasi ke header saja di produksi.
- 🟢 `upload_avatar` cabang "anggota tidak ditemukan" masih perlu dipastikan pakai `ApiResponse::error` (cek akhir).
- 🟢 Beberapa nilai bisnis di-hardcode (mis. `setoran_simpanan_anggota = 350000`, ID jenis/status).

## 11. Referensi Sumber (repo)

- `routes/api.php` — definisi endpoint & middleware `mobile.auth`
- `app/Http/Controllers/MobileController.php` — logika endpoint
- `app/Http/Controllers/Api/VersionController.php` — versi
- `app/Http/Middleware/MobileAuth.php` — validasi token
- `app/Support/ApiResponse.php` — envelope response
- `app/Helpers/MobileHelper.php` — wrapper mobile (saldo, angsuran, stok)
- `composer.json`, `version.json` — dependensi & versi

<aside>
📌

Dokumen ini mengacu commit `84a4529fa` (main). Perbarui bila ada perubahan endpoint atau envelope.

</aside>