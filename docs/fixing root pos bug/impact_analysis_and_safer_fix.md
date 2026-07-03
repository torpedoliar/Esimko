# Impact Analysis, K 1667 Debug & Fix Plan v2

## 1. MySQL Kompatibilitas — TIDAK PERLU Downgrade

Laravel `config/database.php` line 59:
```php
'strict' => false,  // ← Sudah disable ONLY_FULL_GROUP_BY!
```

GROUP BY trick **SUDAH BEKERJA** di MySQL 8 via Laravel. Yang gagal hanya di MySQL CLI langsung. Jadi **tidak perlu downgrade MySQL**.

---

## 2. Analisis K 1667 (Limit = 17,890, Expected ~800,000)

### Data dari GROUP BY (kode original):

| Penjualan | Jenis | total_angsuran | Masalah |
|---|---|---|---|
| **94906** | konsinyasi | **175,010** | ❌ Harusnya 17,501 (= 175,010/10 tenor) |
| **99473** | toko | **502,000** | ❌ GHOST: angsuran_ke=1 ada 2× (paid + pending) |
| 100966 | toko | 283,000 | ✅ Pending valid |
| 101058 | toko | 522,100 | ✅ Baru hari ini |

```
Kode original: 1,500,000 - 1,482,110 = 17,890 ← SALAH

Jika 94906 diperbaiki dan 99473 ghost dihapus:
1,500,000 - (17,501 + 0 + 283,000 + 522,100) = 677,399
```

### Detail Masalah Per Penjualan

**94906 (konsinyasi, tenor=10):**
- `penjualan.angsuran = 175,010` ← ini `total_pembayaran`, bukan per bulan!
- `angsuran_belanja.total_angsuran = 175,010` untuk semua 10 entries
- **Fix:** angsuran harusnya `175,010 / 10 = 17,501`

**99473 (toko, 2026-01-14):**
- Ada **2 entry** `angsuran_ke=1`: satu `fid_status=6` (paid), satu `fid_status=3` (pending)
- Entry pending adalah **GHOST** dari bug PenjualanBaruController
- **Fix:** hapus entry ghost (fid_status=3)

---

## 3. Scope Data Fix yang Diperlukan

### A. Fix angsuran_belanja.total_angsuran yang salah
```sql
-- Penjualan 94906: total_angsuran 175010 → 17501
UPDATE angsuran_belanja SET total_angsuran=17501 WHERE fid_penjualan=94906;
-- Juga fix penjualan.angsuran
UPDATE penjualan SET angsuran=17501 WHERE id=94906;
```

### B. Hapus ghost angsuran_belanja
```sql
-- 99473: hapus ghost entry (pending duplicate)
DELETE FROM angsuran_belanja 
WHERE fid_penjualan=99473 AND angsuran_ke=1 AND fid_status=3;
```

### C. Scan dan fix kasus serupa lainnya
1. Penjualan konsinyasi di mana `angsuran = total_pembayaran` (harusnya `/tenor`)
2. angsuran_belanja duplicate entries (same fid_penjualan + angsuran_ke, multiple rows)

---

## 4. Code Fix — Apakah Perlu?

Kode original GROUP BY trick **sudah benar secara bisnis logic** untuk kasus mayoritas:
- Mengambil 1 entry per penjualan dari angsuran_belanja pending
- Sum total_angsuran = total hutang yang belum dibayar per bulan

**Masalahnya bukan di kode, tapi di DATA:**
- Import konsinyasi menyimpan angsuran = total_pembayaran (bukan per bulan)
- PenjualanBaruController membuat ghost angsuran_belanja

> [!IMPORTANT]
> Opsi 1: Fix DATA saja, biarkan kode original  
> Opsi 2: Fix DATA + Fix kode (pakai `whereRaw EXISTS` yang lebih robust)
> 
> **Rekomendasi: Opsi 1 dulu** — fix data yang salah, verify hasilnya, baru pertimbangkan fix kode.

## 5. Verification Plan

Setelah data fix:
```
K 1215: expected 796,110
K 1741: expected 1,155,920
K 1454: expected 1,500,000
K 1667: expected ~677,399 (perlu konfirmasi user)
```
