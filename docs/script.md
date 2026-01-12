# Dokumentasi Script Deployment

## Overview

Dokumentasi ini menjelaskan semua script yang tersedia untuk deployment dan sinkronisasi aplikasi eSIMKO.

---

## Daftar Script

| Script | Fungsi | Penggunaan |
|--------|--------|------------|
| `deploy.ps1` | Deploy ke Docker (development) | `.\deploy.ps1` |
| `deploy.bat` | Deploy ke Docker (batch version) | Double-click |
| `sync-production.ps1` | Download database dari production | `.\sync-production.ps1` |
| `sync-production.bat` | Download database (batch version) | Double-click |
| `full-sync-deploy.ps1` | Sync + Deploy (development) | `.\full-sync-deploy.ps1` |
| `full-sync-deploy-npm.ps1` | Sync + Deploy dengan NPM | `.\full-sync-deploy-npm.ps1` |
| `full-sync-deploy-npm.bat` | Sync + Deploy NPM (batch) | Double-click |

---

## Detail Script

### 1. deploy.ps1

**Fungsi:** Deploy aplikasi ke Docker container untuk development.

**Yang Dilakukan:**
1. Check Docker running
2. Export database dari container yang ada (jika ada)
3. Stop existing containers
4. Build dan start containers baru
5. Wait for database ready
6. Import database dari backup
7. Run Laravel optimizations

**Penggunaan:**
```powershell
# Development mode
.\deploy.ps1

# Production mode (dengan NPM)
.\deploy.ps1 -Production

# Skip database export
.\deploy.ps1 -SkipDbExport

# Skip database import
.\deploy.ps1 -SkipDbImport

# Fresh install (hapus semua data)
.\deploy.ps1 -Fresh
```

**Docker Compose:** `docker-compose.yml`

**Output:**
- Aplikasi berjalan di `http://localhost:8080`

---

### 2. deploy.bat

**Fungsi:** Versi batch dari `deploy.ps1` untuk kemudahan penggunaan.

**Yang Dilakukan:**
- Sama dengan `deploy.ps1`
- Meminta pilihan mode (Development/Production)

**Penggunaan:**
- Double-click file `deploy.bat`
- Pilih mode: 1 = Development, 2 = Production

---

### 3. sync-production.ps1

**Fungsi:** Download database terbaru dari server production.

**Yang Dilakukan:**
1. Check SSH tersedia
2. Connect ke production server via SSH
3. Export database dengan `mysqldump`
4. Download ke folder `backups/`
5. Copy ke `esimko_latest_backup.sql`
6. **Sync APP_KEY dari production** (untuk password encryption)
7. Import ke Docker container (optional)

**Penggunaan:**
```powershell
# Full sync dengan import
.\sync-production.ps1

# Download saja tanpa import
.\sync-production.ps1 -SkipImport

# Backup only mode
.\sync-production.ps1 -BackupOnly
```

**Server Production:**
```
Host: 104.248.150.30
User: root
Password: ESIMKO4rt1s4n
Database: esimko
```

**Output:**
- `backups/esimko_prod_YYYYMMDD_HHMMSS.sql` - Backup dengan timestamp
- `esimko_latest_backup.sql` - Backup terbaru (copy)
- `.env.production.key` - APP_KEY dari production

---

### 4. sync-production.bat

**Fungsi:** Versi batch dari `sync-production.ps1`.

**Yang Dilakukan:**
- Sama dengan `sync-production.ps1`
- Prompt password SSH secara manual

**Penggunaan:**
- Double-click file `sync-production.bat`
- Masukkan password SSH: `ESIMKO4rt1s4n`

---

### 5. full-sync-deploy.ps1

**Fungsi:** Kombinasi sync dari production + deploy ke Docker (development).

**Yang Dilakukan:**
1. **Phase 1: Sync**
   - Download database dari production
   - Sync APP_KEY
2. **Phase 2: Deploy**
   - Stop containers
   - Build dan start
   - Import database
   - Laravel optimization

**Penggunaan:**
```powershell
# Full sync + deploy
.\full-sync-deploy.ps1

# Skip sync (gunakan backup yang ada)
.\full-sync-deploy.ps1 -SkipSync

# Fresh install
.\full-sync-deploy.ps1 -Fresh

# Production mode dengan NPM
.\full-sync-deploy.ps1 -Production
```

**Docker Compose:** `docker-compose.yml`

**Output:**
- Aplikasi berjalan di `http://localhost:8080`

---

### 6. full-sync-deploy-npm.ps1

**Fungsi:** Kombinasi sync + deploy dengan NGINX Proxy Manager.

**Yang Dilakukan:**
1. **Phase 1: Sync**
   - Download database dari production
   - Sync APP_KEY dari production
   - Update local `.env` dengan APP_KEY
2. **Phase 2: Deploy with NPM**
   - Stop containers
   - Build dan start (dengan NPM)
   - Wait for database
   - Import database
   - Laravel optimization

**Penggunaan:**
```powershell
# Full sync + deploy dengan NPM
.\full-sync-deploy-npm.ps1

# Skip sync
.\full-sync-deploy-npm.ps1 -SkipSync

# Fresh install
.\full-sync-deploy-npm.ps1 -Fresh
```

**Docker Compose:** `docker-compose.npm.yml`

**Output:**
- NPM Admin: `http://localhost:81`
- Aplikasi (setelah config NPM): `http://esimko.com`

---

### 7. full-sync-deploy-npm.bat

**Fungsi:** Versi batch dari `full-sync-deploy-npm.ps1`.

**Yang Dilakukan:**
- Sama dengan `full-sync-deploy-npm.ps1`

**Penggunaan:**
- Double-click file `full-sync-deploy-npm.bat`
- Masukkan password SSH: `ESIMKO4rt1s4n`

---

## Perbandingan Script

| Script | Sync DB | Sync APP_KEY | Deploy | NPM |
|--------|---------|--------------|--------|-----|
| `deploy.ps1` | ❌ | ❌ | ✅ | Optional |
| `sync-production.ps1` | ✅ | ✅ | Optional | ❌ |
| `full-sync-deploy.ps1` | ✅ | ✅ | ✅ | ❌ |
| `full-sync-deploy-npm.ps1` | ✅ | ✅ | ✅ | ✅ |

---

## Folder dan File yang Dibuat

| Path | Keterangan |
|------|------------|
| `backups/` | Folder untuk menyimpan backup database |
| `backups/esimko_prod_*.sql` | Backup database dengan timestamp |
| `esimko_latest_backup.sql` | Backup terbaru (untuk deploy) |
| `.env.production.key` | APP_KEY dari production |

---

## Troubleshooting

### Error: SSH Password Required

**Masalah:** Script berhenti menunggu password SSH.

**Solusi:** Ketik password `ESIMKO4rt1s4n` dan tekan Enter.

### Error: Docker is not running

**Masalah:** Docker Desktop tidak berjalan.

**Solusi:** Start Docker Desktop terlebih dahulu.

### Error: Login gagal setelah sync

**Masalah:** APP_KEY berbeda antara local dan production.

**Solusi:** 
1. Jalankan ulang sync script
2. Pastikan ada output "APP_KEY synced"
3. Clear cache: `docker exec esimko-app php artisan config:clear`

### Error: Database import too slow

**Masalah:** Database besar, import memakan waktu lama.

**Solusi:** Tunggu sampai selesai, atau gunakan `-SkipDbImport` lalu import manual.

### Error: Port already in use

**Masalah:** Port 80, 8080, atau 81 sudah digunakan.

**Solusi:** 
1. Stop aplikasi lain yang menggunakan port tersebut
2. Atau modifikasi port di `docker-compose.yml`

---

## Rekomendasi Penggunaan

### Development (Local Testing)
```powershell
# Pertama kali atau update dari production
.\full-sync-deploy.ps1

# Deploy ulang tanpa sync
.\deploy.ps1
```

### Production Testing dengan NPM
```powershell
# Pertama kali
.\full-sync-deploy-npm.ps1

# Deploy ulang tanpa sync
.\full-sync-deploy-npm.ps1 -SkipSync
```

### Backup Database dari Production
```powershell
# Backup saja tanpa deploy
.\sync-production.ps1 -BackupOnly
```

---

## Environment Variables

### Development (docker-compose.yml)
```env
APP_ENV=local
APP_DEBUG=true
DB_HOST=localhost (internal)
CACHE_DRIVER=redis
SESSION_DRIVER=file
```

### Production with NPM (docker-compose.npm.yml)
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://esimko.com
DB_HOST=db
CACHE_DRIVER=redis
TRUSTED_PROXIES=*
```

---

## Catatan Penting

1. **APP_KEY Penting!**
   - Password di Laravel di-encrypt menggunakan APP_KEY
   - Jika APP_KEY berbeda, login akan gagal
   - Script sync otomatis copy APP_KEY dari production

2. **Backup Selalu Tersimpan**
   - Setiap sync membuat file baru di `backups/`
   - File lama tidak dihapus
   - `esimko_latest_backup.sql` selalu yang terbaru

3. **SSH Password Manual**
   - Script tidak bisa auto-input password
   - Harus diketik manual saat prompt muncul

4. **NPM Perlu Konfigurasi**
   - Setelah deploy dengan NPM, perlu setup Proxy Host manual
   - Lihat `docs/NPM Documents.md` untuk panduan lengkap
