# Panduan Perbaikan Duplikat Angsuran Belanja

**Tanggal:** 13 Januari 2026  
**Database:** esimko (Production)

---

## Daftar Isi

1. [Koneksi ke Production](#koneksi-ke-production)
2. [Koneksi ke Docker Local](#koneksi-ke-docker-local-development)
3. [Deteksi Duplikat - Semua User](#deteksi-duplikat---semua-user)
4. [Deteksi Duplikat - User Tertentu](#deteksi-duplikat---user-tertentu)
5. [Hapus Duplikat - Semua User](#hapus-duplikat---semua-user)
6. [Hapus Duplikat - User Tertentu](#hapus-duplikat---user-tertentu)
7. [Hapus Duplikat - Jika Sudah Tahu ID](#hapus-duplikat---jika-sudah-tahu-id)
8. [Verifikasi Hasil](#verifikasi-hasil)

---

## Koneksi ke Production

### Via SSH (Windows PowerShell)

```powershell
ssh root@104.248.150.30
# Password: ESIMKO4rt1s4n
```

### Masuk ke MySQL

```bash
mysql -u esimko -p'esimko' esimko
```

---

## Koneksi ke Docker Local (Development)

### Setup Standard (docker-compose.yml)

Database ada di dalam container `esimko-app`:

```powershell
# Akses MySQL interaktif
docker exec -it esimko-app mysql -u root -pMYSQLp4ssw0rd7% esimko

# Jalankan query langsung
docker exec esimko-app mysql -u root -pMYSQLp4ssw0rd7% esimko -e "QUERY_DISINI"
```

### Setup NPM (docker-compose.npm.yml)

Database ada di container terpisah `esimko-db`:

```powershell
# Akses MySQL interaktif
docker exec -it esimko-db mysql -u root -proot_password_123 esimko

# Jalankan query langsung
docker exec esimko-db mysql -u root -proot_password_123 esimko -e "QUERY_DISINI"
```

### Tabel Kredensial

| Environment | Container | User | Password | Database |
|-------------|-----------|------|----------|----------|
| Production | - | esimko | esimko | esimko |
| Docker Standard | esimko-app | root | MYSQLp4ssw0rd7% | esimko |
| Docker NPM | esimko-db | root | root_password_123 | esimko |

### Cara Cek Setup yang Aktif

```powershell
docker ps --format "table {{.Names}}\t{{.Status}}"
```

- Ada `esimko-db` = Setup NPM
- Hanya `esimko-app` = Setup Standard

---

## Deteksi Duplikat - Semua User

### Query 1: List Semua Duplikat

```sql
SELECT 
    ab.fid_penjualan,
    ab.angsuran_ke,
    COUNT(*) as jumlah_duplikat,
    GROUP_CONCAT(ab.id ORDER BY ab.id) as duplicate_ids,
    GROUP_CONCAT(ab.fid_status ORDER BY ab.id) as statuses
FROM angsuran_belanja ab
GROUP BY ab.fid_penjualan, ab.angsuran_ke
HAVING COUNT(*) > 1
ORDER BY jumlah_duplikat DESC;
```

### Query 2: Total Duplikat

```sql
SELECT COUNT(*) as total_duplikat
FROM angsuran_belanja ab
WHERE (ab.fid_penjualan, ab.angsuran_ke) IN (
    SELECT fid_penjualan, angsuran_ke 
    FROM angsuran_belanja 
    GROUP BY fid_penjualan, angsuran_ke 
    HAVING COUNT(*) > 1
);
```

---

## Deteksi Duplikat - User Tertentu

> **Ganti `K 0863` dengan No. Anggota yang ingin dicek**

### Query 1: List Duplikat User

```sql
-- Ganti 'K 0863' dengan no_anggota yang diinginkan
SELECT 
    p.no_transaksi,
    p.fid_anggota,
    ab.id as angsuran_id,
    ab.angsuran_ke,
    ab.total_angsuran,
    ab.fid_status,
    CASE ab.fid_status 
        WHEN 3 THEN 'Belum Bayar'
        WHEN 6 THEN 'Lunas'
        ELSE 'Lainnya'
    END as status_name
FROM angsuran_belanja ab
JOIN penjualan p ON ab.fid_penjualan = p.id
WHERE p.fid_anggota = 'K 0863'  -- << GANTI INI
AND (ab.fid_penjualan, ab.angsuran_ke) IN (
    SELECT fid_penjualan, angsuran_ke 
    FROM angsuran_belanja 
    GROUP BY fid_penjualan, angsuran_ke 
    HAVING COUNT(*) > 1
)
ORDER BY ab.fid_penjualan, ab.angsuran_ke, ab.id;
```

### Query 2: Hitung Duplikat User

```sql
-- Ganti 'K 0863' dengan no_anggota yang diinginkan
SELECT 
    COUNT(*) as total_duplikat,
    SUM(ab.total_angsuran) as total_nilai_duplikat
FROM angsuran_belanja ab
JOIN penjualan p ON ab.fid_penjualan = p.id
WHERE p.fid_anggota = 'K 0863'  -- << GANTI INI
AND (ab.fid_penjualan, ab.angsuran_ke) IN (
    SELECT fid_penjualan, angsuran_ke 
    FROM angsuran_belanja 
    GROUP BY fid_penjualan, angsuran_ke 
    HAVING COUNT(*) > 1
);
```

---

## Hapus Duplikat - Semua User

> ⚠️ **BACKUP DATABASE SEBELUM MENJALANKAN!**

### Step 1: Buat Temporary Table

```sql
CREATE TEMPORARY TABLE keep_ids AS
SELECT 
    COALESCE(
        MIN(CASE WHEN fid_status = 6 THEN id END),
        MIN(id)
    ) as keep_id,
    fid_penjualan,
    angsuran_ke
FROM angsuran_belanja
GROUP BY fid_penjualan, angsuran_ke
HAVING COUNT(*) > 1;
```

### Step 2: Preview yang akan Dihapus

```sql
SELECT 
    k.fid_penjualan,
    k.angsuran_ke,
    k.keep_id as id_disimpan,
    (SELECT COUNT(*) FROM angsuran_belanja ab2 
     WHERE ab2.fid_penjualan = k.fid_penjualan 
     AND ab2.angsuran_ke = k.angsuran_ke) - 1 as akan_dihapus
FROM keep_ids k;
```

### Step 3: Hapus Duplikat

```sql
DELETE ab FROM angsuran_belanja ab
INNER JOIN keep_ids k 
    ON ab.fid_penjualan = k.fid_penjualan 
    AND ab.angsuran_ke = k.angsuran_ke
WHERE ab.id != k.keep_id;

-- Lihat jumlah yang terhapus
SELECT ROW_COUNT() as records_deleted;
```

### Step 4: Cleanup

```sql
DROP TEMPORARY TABLE IF EXISTS keep_ids;
```

---

## Hapus Duplikat - User Tertentu

> **Ganti `K 0863` dengan No. Anggota yang ingin diperbaiki**

### Step 1: Buat Temporary Table untuk User

```sql
-- Ganti 'K 0863' dengan no_anggota yang diinginkan
CREATE TEMPORARY TABLE keep_ids_user AS
SELECT 
    COALESCE(
        MIN(CASE WHEN ab.fid_status = 6 THEN ab.id END),
        MIN(ab.id)
    ) as keep_id,
    ab.fid_penjualan,
    ab.angsuran_ke
FROM angsuran_belanja ab
JOIN penjualan p ON ab.fid_penjualan = p.id
WHERE p.fid_anggota = 'K 0863'  -- << GANTI INI
GROUP BY ab.fid_penjualan, ab.angsuran_ke
HAVING COUNT(*) > 1;
```

### Step 2: Preview

```sql
SELECT * FROM keep_ids_user;
```

### Step 3: Hapus Duplikat User

```sql
DELETE ab FROM angsuran_belanja ab
INNER JOIN keep_ids_user k 
    ON ab.fid_penjualan = k.fid_penjualan 
    AND ab.angsuran_ke = k.angsuran_ke
WHERE ab.id != k.keep_id;

SELECT ROW_COUNT() as records_deleted;
```

### Step 4: Cleanup

```sql
DROP TEMPORARY TABLE IF EXISTS keep_ids_user;
```

---

## Hapus Duplikat - Jika Sudah Tahu ID

> **Gunakan ini jika Anda sudah tahu persis ID angsuran yang harus dihapus**

### Step 1: Cek Data Sebelum Hapus

```sql
-- Ganti ID sesuai yang ingin dihapus
SELECT * FROM angsuran_belanja WHERE id IN (278671, 278672, 278673);
```

### Step 2: Hapus Duplikat

```sql
-- Ganti ID sesuai yang ingin dihapus
DELETE FROM angsuran_belanja WHERE id IN (278671, 278672, 278673);

-- Lihat jumlah yang terhapus
SELECT ROW_COUNT() as records_deleted;
```

### Contoh Kasus: K 0863

Anggota K 0863 punya 4 record angsuran untuk transaksi yang sama:
- ID 278670 = ✅ Lunas (SIMPAN)
- ID 278671 = ❌ Duplikat (HAPUS)
- ID 278672 = ❌ Duplikat (HAPUS)
- ID 278673 = ❌ Duplikat (HAPUS)

```sql
-- Cek dulu
SELECT * FROM angsuran_belanja WHERE id IN (278671, 278672, 278673);

-- Hapus duplikat
DELETE FROM angsuran_belanja WHERE id IN (278671, 278672, 278673);
```

---

## Verifikasi Hasil

### Cek Tidak Ada Duplikat Lagi

```sql
-- Untuk semua user
SELECT fid_penjualan, angsuran_ke, COUNT(*) 
FROM angsuran_belanja 
GROUP BY fid_penjualan, angsuran_ke 
HAVING COUNT(*) > 1;
```

```sql
-- Untuk user tertentu (ganti K 0863)
SELECT ab.fid_penjualan, ab.angsuran_ke, COUNT(*) 
FROM angsuran_belanja ab
JOIN penjualan p ON ab.fid_penjualan = p.id
WHERE p.fid_anggota = 'K 0863'  -- << GANTI INI
GROUP BY ab.fid_penjualan, ab.angsuran_ke 
HAVING COUNT(*) > 1;
```

> ✅ Jika query tidak mengembalikan hasil = **Tidak ada duplikat**

---

## Quick Reference

| No Anggota | Ubah Ini |
|------------|----------|
| Contoh | `WHERE p.fid_anggota = 'K 0863'` |
| Format | `K XXXX` atau `AK XXXX` |

---

## Troubleshooting

### Error: Table 'keep_ids' already exists

```sql
DROP TEMPORARY TABLE IF EXISTS keep_ids;
DROP TEMPORARY TABLE IF EXISTS keep_ids_user;
```

### Ingin Rollback?

Tidak bisa rollback setelah DELETE. Pastikan sudah backup!

---

*Dokumentasi dibuat: 13 Januari 2026*
