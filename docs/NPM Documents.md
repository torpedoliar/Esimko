# NGINX Proxy Manager (NPM) Documentation

## Overview

Aplikasi eSIMKO menggunakan NGINX Proxy Manager sebagai reverse proxy untuk:
- SSL/TLS termination
- Domain management
- Load balancing (jika diperlukan)

---

## Arsitektur

```
┌─────────────────────────────────────────────────────────┐
│                      Internet                           │
│                   esimko.com:80/443                     │
└─────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────┐
│              NGINX Proxy Manager (esimko-npm)           │
│              Port 80 (HTTP) / 443 (HTTPS) / 81 (Admin)  │
└─────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────┐
│                    esimko-app (Laravel)                 │
│                        Port 80                          │
│  ┌─────────────┬─────────────┬─────────────────────┐   │
│  │  Frontend   │  Web API    │  Mobile API         │   │
│  │  /          │  /api/*     │  /api/mobile/*      │   │
│  ├─────────────┴─────────────┴─────────────────────┤   │
│  │              Storage Files (/storage/*)          │   │
│  └─────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────┘
                              │
              ┌───────────────┴───────────────┐
              ▼                               ▼
┌─────────────────────┐         ┌─────────────────────┐
│    esimko-db        │         │    esimko-redis     │
│    MySQL:3306       │         │    Redis:6379       │
│    (Internal)       │         │    (Internal)       │
└─────────────────────┘         └─────────────────────┘
```

---

## Container Services

| Container | Port | Fungsi | Akses |
|-----------|------|--------|-------|
| `esimko-npm` | 80, 443, 81 | Reverse proxy & SSL | Public |
| `esimko-app` | 80 (internal) | Laravel application | Via NPM |
| `esimko-db` | 3306 (internal) | MySQL database | Internal only |
| `esimko-redis` | 6379 (internal) | Redis cache | Internal only |

---

## Quick Start

### 1. Deploy dengan NPM

```powershell
# Full sync dari production + deploy
.\full-sync-deploy-npm.ps1

# Atau deploy saja (tanpa sync)
.\full-sync-deploy-npm.ps1 -SkipSync
```

### 2. Akses NPM Admin Panel

- **URL**: http://localhost:81
- **Email**: `admin@example.com`
- **Password**: `changeme`

### 3. Konfigurasi Proxy Host

Lihat bagian [Konfigurasi Proxy Host](#konfigurasi-proxy-host) di bawah.

---

## Konfigurasi Proxy Host

### Step 1: Login ke NPM

1. Buka http://localhost:81
2. Login dengan credentials default
3. Ganti password saat diminta

### Step 2: Tambah Proxy Host

Klik **Hosts** → **Proxy Hosts** → **Add Proxy Host**

#### Tab Details

| Field | Value |
|-------|-------|
| Domain Names | `esimko.com` |
| Scheme | `http` |
| Forward Hostname / IP | `esimko-app` |
| Forward Port | `80` |
| Cache Assets | ✅ |
| Block Common Exploits | ✅ |
| Websockets Support | ❌ |

#### Tab SSL (Optional - untuk HTTPS)

| Field | Value |
|-------|-------|
| SSL Certificate | Request a new SSL Certificate |
| Force SSL | ✅ |
| HTTP/2 Support | ✅ |
| HSTS Enabled | ✅ |

> **Note**: SSL hanya berfungsi dengan domain publik yang valid. Untuk local development, skip tab ini.

#### Tab Advanced

Tambahkan konfigurasi berikut:

```nginx
# Laravel specific settings
client_max_body_size 100M;

# Timeouts for long operations
proxy_connect_timeout 300;
proxy_send_timeout 300;
proxy_read_timeout 300;

# Headers
proxy_set_header Host $host;
proxy_set_header X-Real-IP $remote_addr;
proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
proxy_set_header X-Forwarded-Proto $scheme;
```

### Step 3: Save

Klik **Save** dan proxy host akan aktif.

---

## Local Development Setup

### Tambahkan ke Hosts File

1. Buka Notepad sebagai **Administrator**
2. Open file: `C:\Windows\System32\drivers\etc\hosts`
3. Tambahkan:

```
127.0.0.1    esimko.com
```

4. Save file

### Akses Aplikasi

- **Via NPM**: http://esimko.com
- **Direct**: http://localhost:8080 (jika development mode)
- **NPM Admin**: http://localhost:81

---

## URL Endpoints

Semua endpoint berada di satu container (`esimko-app`) dan hanya memerlukan **1 Proxy Host**:

### Frontend Web
```
https://esimko.com/
https://esimko.com/login
https://esimko.com/dashboard
https://esimko.com/pos/penjualan_baru
```

### Web API
```
https://esimko.com/api/find_anggota/{id}
https://esimko.com/api/find_produk/{id}
https://esimko.com/api/get_anggota
https://esimko.com/api/get_produk
```

### Mobile API
```
POST https://esimko.com/api/mobile/auth/login
POST https://esimko.com/api/mobile/auth/register
GET  https://esimko.com/api/mobile/anggota/profil
GET  https://esimko.com/api/mobile/berita
GET  https://esimko.com/api/mobile/produk
POST https://esimko.com/api/mobile/transaksi/{jenis}/proses
```

### Storage Files
```
https://esimko.com/storage/avatars/user123.jpg
https://esimko.com/storage/produk/product456.jpg
https://esimko.com/storage/dokumen/invoice.pdf
```

---

## Troubleshooting

### 502 Bad Gateway

**Penyebab**: Container `esimko-app` tidak running atau tidak healthy.

**Solusi**:
```bash
# Check container status
docker ps

# Check logs
docker logs esimko-app

# Restart container
docker-compose -f docker-compose.npm.yml restart app
```

### Connection Refused

**Penyebab**: Forward Host atau Port salah.

**Solusi**:
- Pastikan Forward Host = `esimko-app` (bukan IP)
- Pastikan Forward Port = `80`
- Pastikan container dalam network yang sama

### Domain Tidak Resolve

**Penyebab**: Hosts file belum diupdate.

**Solusi**:
```bash
# Test DNS resolution
ping esimko.com

# Jika tidak resolve, update hosts file
```

### SSL Certificate Error

**Penyebab**: Domain tidak valid atau tidak bisa diverifikasi.

**Solusi**:
- Untuk local: Skip SSL, gunakan HTTP saja
- Untuk production: Pastikan domain mengarah ke server IP

### File Upload Gagal (413 Error)

**Penyebab**: File terlalu besar.

**Solusi**: Tambahkan di Advanced config:
```nginx
client_max_body_size 100M;
```

---

## Production Deployment

### Requirements

1. VPS/Server dengan Docker & Docker Compose
2. Domain yang mengarah ke server IP
3. Port 80 dan 443 terbuka

### Steps

1. Clone repository ke server
2. Copy `esimko_latest_backup.sql` ke server
3. Jalankan:
   ```bash
   docker-compose -f docker-compose.npm.yml up -d --build
   ```
4. Akses NPM Admin di `http://your-ip:81`
5. Konfigurasi Proxy Host dengan domain production
6. Request SSL certificate

### SSL dengan Let's Encrypt

1. Di NPM, buat Proxy Host dengan domain production
2. Tab SSL → Request a new SSL Certificate
3. Centang "Force SSL" dan "HTTP/2 Support"
4. Klik Save

NPM akan otomatis request dan renew SSL certificate dari Let's Encrypt.

---

## Environment Variables

Aplikasi menggunakan environment variables berikut untuk NPM:

```env
# Di docker-compose.npm.yml
APP_URL=https://esimko.com
TRUSTED_PROXIES=*
```

`TRUSTED_PROXIES=*` memastikan Laravel menerima headers dari NPM dengan benar (X-Forwarded-For, X-Forwarded-Proto, dll).

---

## Maintenance

### Backup NPM Configuration

```bash
# Backup NPM data
docker run --rm -v esimko_npm_data:/data -v $(pwd):/backup alpine tar czf /backup/npm_backup.tar.gz /data
```

### Update NPM

```bash
# Pull latest image
docker-compose -f docker-compose.npm.yml pull npm

# Recreate container
docker-compose -f docker-compose.npm.yml up -d npm
```

### View NPM Logs

```bash
docker logs -f esimko-npm
```

---

## Summary

| Item | Konfigurasi |
|------|-------------|
| Proxy Hosts Needed | **1** |
| Domain | `esimko.com` |
| Forward To | `esimko-app:80` |
| SSL | Let's Encrypt (production) |
| Websockets | Tidak diperlukan |

Aplikasi eSIMKO adalah **monolith Laravel** yang tidak memerlukan konfigurasi proxy yang kompleks. Satu Proxy Host sudah cukup untuk menangani semua request termasuk API dan file storage.
