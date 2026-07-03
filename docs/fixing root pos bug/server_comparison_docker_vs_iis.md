# Perbandingan Server Production: Docker (Lama) vs IIS (Baru)

## Ringkasan Perbedaan Kritis

| Aspek | Docker (Lama) ✅ | IIS (Baru) ⚠️ | Status |
|-------|-----------------|---------------|--------|
| PHP Version | 7.4 (Ubuntu) | 7.4.33 (Windows) | ✅ OK |
| MySQL Version | 8.0 | **9.6.0** | ⚠️ Lebih baru |
| Web Server | Nginx | IIS 10 | ✅ OK |
| max_execution_time | **Unlimited** (0) | ~~30~~ → **300** (fixed) | ✅ Fixed |
| memory_limit | 128M (default) | **512M** | ✅ Lebih besar |
| upload_max_filesize | 2M (default) | **50M** | ✅ Lebih besar |
| post_max_size | 8M (default) | **50M** | ✅ Lebih besar |
| date.timezone | Asia/Jakarta (likely) | **(kosong!)** | ❌ Perlu fix |
| URL Rewriting | Nginx config | **Tidak ada web.config** | ⚠️ Cek |
| SQL Mode | Default 8.0 | **ONLY_FULL_GROUP_BY** (active) | ⚠️ Laravel handles |

---

## 1. PHP Extensions yang Hilang ❌

Docker memasang beberapa extensions yang **TIDAK ADA** di server IIS baru:

| Extension | Fungsi | Dampak jika Hilang |
|-----------|--------|-------------------|
| **imagick** | Image processing | Error saat resize/crop gambar |
| **redis** | Cache/session driver | Error jika app pakai Redis |
| **ssh2** | SSH connections | Error jika app pakai SSH |
| **apcu** | OpCode cache | Performa lebih lambat |
| **xmlrpc** | XML-RPC protocol | Error jika app pakai XMLRPC |
| **ldap** | LDAP authentication | Error jika app pakai LDAP |

> [!IMPORTANT]
> Cek apakah aplikasi Esimko **menggunakan** extension di atas. Jika tidak, maka tidak masalah.

---

## 2. Database Columns yang Hilang ❌

Kolom berikut dipakai oleh kode `PenjualanBaruController::delete` tapi **BELUM ADA** di tabel `penjualan`:

| Kolom | Tipe Disarankan | Dipakai Di |
|-------|----------------|------------|
| `alasan_batal` | TEXT NULL | PenjualanBaruController::delete |
| `dibatalkan_oleh` | VARCHAR(200) NULL | PenjualanBaruController::delete |
| `tanggal_batal` | DATETIME NULL | PenjualanBaruController::delete |

**SQL Fix:**
```sql
ALTER TABLE penjualan 
  ADD COLUMN alasan_batal TEXT NULL,
  ADD COLUMN dibatalkan_oleh VARCHAR(200) NULL,
  ADD COLUMN tanggal_batal DATETIME NULL;
```

---

## 3. PHP Configuration (php.ini) ⚠️

| Setting | Docker Default | IIS Sekarang | Rekomendasi |
|---------|---------------|-------------|-------------|
| max_execution_time | 0 (unlimited) | **300** (sudah fix) | ✅ OK |
| date.timezone | (set di container) | **(kosong)** | Set `Asia/Jakarta` |
| max_input_vars | 1000 | 1000 | ✅ OK |

**Fix `date.timezone`** di `C:\php\php.ini`:
```ini
date.timezone = Asia/Jakarta
```

---

## 4. IIS-Specific: web.config ⚠️

Server **tidak memiliki** `web.config`. Ini berarti:
- URL rewriting mungkin tidak bekerja optimal (Pretty URLs)
- Error pages mungkin menampilkan IIS default (bukan Laravel)

**Rekomendasi** buat `C:\IIS\Esimko\public\web.config`:
```xml
<configuration>
  <system.webServer>
    <rewrite>
      <rules>
        <rule name="Laravel" stopProcessing="true">
          <match url="^" ignoreCase="false" />
          <conditions logicalGrouping="MatchAll">
            <add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
            <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
          </conditions>
          <action type="Rewrite" url="index.php" />
        </rule>
      </rules>
    </rewrite>
    <security>
      <requestFiltering>
        <requestLimits maxAllowedContentLength="52428800" />
      </requestFiltering>
    </security>
  </system.webServer>
</configuration>
```

---

## 5. Prioritas Fix

1. ✅ **max_execution_time** → Sudah fixed (30→300)
2. ❌ **Kolom penjualan** → `alasan_batal`, `dibatalkan_oleh`, `tanggal_batal` (Crash saat batal transaksi)
3. ⚠️ **date.timezone** → Set `Asia/Jakarta` (bisa cause warning)
4. ⚠️ **web.config** → Buat untuk URL rewriting (mungkin sudah dihandle IIS UI)
5. 🔍 **PHP Extensions** → Cek apakah app butuh imagick/redis/ldap
