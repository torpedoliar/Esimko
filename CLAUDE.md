# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Stack

- **Web app:** Laravel 7 (PHP 7.4), Blade + Laravel Mix/Webpack frontend. Koperasi accounting app (jurnal, buku besar, simpanan, pinjaman, penjualan, pembelian, payroll, dsb).
- **Mobile API:** REST backend in `app/Http/Controllers/MobileController.php` (~1000 lines, all mobile endpoints in one controller).
- **Mobile app:** native Kotlin Android app in `EsimkoMobile/` (Gradle, Hilt, KSP).
- **Infra:** Docker on Ubuntu 20.04 base — single container runs nginx + MySQL + php7.4-fpm + SSH. Redis also in compose.

## Commands

```bash
# Local web dev (Laravel dev server)
php artisan serve

# Run backend tests (Laravel 7 phpunit)
vendor/bin/phpunit
vendor/bin/phpunit --filter=SomeTest

# Frontend assets (Laravel Mix / webpack)
npm run dev         # development build
npm run watch       # watch for changes
npm run production  # minified production build

# Docker (single app container + redis)
docker compose up -d --build     # http://localhost:8080

# Android APK build
cd EsimkoMobile && ./gradlew assembleDebug   # app-debug.apk

# Database backup (PowerShell, in repo root)
.\db-export.ps1                  # export local -> backups/
.\db-export.ps1 -Production      # export from production
.\db-import.ps1 [-Production]    # import backup
```

## Mobile API architecture

- **Routes:** all under `routes/api.php` → `Route::group(['prefix'=>'mobile'])`. Public: `auth/login`, `auth/register`. Everything else behind `mobile.auth` middleware.
- **Auth flow:** login sets `token = Str::random(32)` on `anggota` row (column `anggota.token`). Client sends it as `Authorization: Bearer <token>`. No JWT, no expiry — token persists until overwritten.
- **`MobileAuth` middleware** (`app/Http/Middleware/MobileAuth.php`): reads token from header/bearer/`token` input, looks up `Anggota::where('token',$token)`, then `$request->merge(['no_anggota' => ...])` so controllers never trust client-supplied `no_anggota`.
- **Response helper:** `app/Support/ApiResponse.php` — `ApiResponse::success($data,$msg,$meta,$code)` / `ApiResponse::error($msg,$code,$errors)`. Uniform JSON shape `{success, message, data[, meta]}`. Use it, don't return raw `response()->json()`.
- **Helpers:** `app/Helpers/GlobalHelper.php`, `app/Helpers/MobileHelper.php`.
- **Auth middleware `mobile.auth`** is registered in `app/Http/Kernel.php` route middleware.

## Key models (koperasi domain)

`Anggota` (the auth principal, holds token), `Transaksi`, `Angsuran`, `Simpanan`, `Pinjaman`, `Produk`/`FotoProduk`, `Penjualan`/`ItemPenjualan`, `Pembelian`, `KeranjangBelanja`, `VerifikasiTransaksi`, `Berita`/`AttachmentBerita`, `GajiPokok`, `AngsuranBelanja`. Eloquent models in `app/`.

## Gotchas

- `.env`, `.env.production` are gitignored; `.env.docker` is committed. Don't commit real env secrets.
- `app-debug.apk` / `app-debug-final.apk` committed at repo root (~62MB each) — build artifacts from the Android pipe.
- Mobile API has no rate limiting / token expiry on `anggota.token` — token rotates only on next login.
- Password stored via `decrypt($anggota->password)` in `MobileController::login` (Laravel `encrypt`), not bcrypt.
