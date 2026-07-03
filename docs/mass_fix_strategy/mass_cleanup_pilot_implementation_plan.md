# Implementation Plan: Mass Duplicate Cleanup (524 Anggota)

Dokumen ini adalah panduan teknis mendetail untuk mengeksekusi penghapusan data duplikat ("Ghost Installments") yang membebani limit 524 anggota.

## 1. Lingkup Pekerjaan
- **Target**: 524 Anggota.
- **Data Target**: 1.364 Baris pada tabel `angsuran_belanja`.
- **Kondisi Data**: Baris dengan `fid_status = 3` (Belum Lunas) yang **MEMILIKI DUPLIKAT** `fid_status = 6` (Lunas) pada `fid_penjualan` yang sama.
- **Tujuan**: Menghapus baris "Hantu" (Status 3) sehingga limit anggota kembali pulih.

---

## 2. Persiapan & Backup (Pre-Flight Check)
Sebelum eksekusi, wajib melakukan backup parsial untuk tabel yang akan disentuh.

**Command Backup:**
```bash
mysqldump -u root -p esimko angsuran_belanja > C:\IIS\Esimko\backups\angsuran_belanja_PRE_MASS_FIX.sql
```

**Verifikasi Backup:**
Pastikan file `.sql` tersebut ada dan ukurannya wajar (tidak 0 KB).

---

## 3. Script Eksekusi (The Fix)
Kita akan menggunakan script SQL yang presisi untuk **hanya menghapus duplikat**.

**SQL Logic:**
```sql
DELETE ab
FROM esimko.angsuran_belanja ab
INNER JOIN (
    -- Subquery: Cari transaksi yang SUDAH LUNAS (Status 6)
    SELECT DISTINCT fid_penjualan 
    FROM esimko.angsuran_belanja 
    WHERE fid_status = 6
) AS valid_lunas ON ab.fid_penjualan = valid_lunas.fid_penjualan
WHERE ab.fid_status = 3; -- Hapus yang statusnya 3 (Belum Lunas) jika sudah ada di daftar lunas
```

**Metode Eksekusi:**
Simpan query di atas ke file `fix_mass_cleanup.sql`, lalu jalankan via MySQL Client.

---

## 4. Langkah Verifikasi (Post-Fix Verification)
Setelah script dijalankan, lakukan pengecekan berikut:

### A. Cek Jumlah Terhapus
Output query harus menunjukkan `Row(s) affected: 1364` (atau angka yang mendekati).

### B. Validasi Limit (Sampling Member)
Cek limit 3 anggota acak yang sebelumnya terdampak:

1. **Rochani (K 0895)**
   - *Sebelum*: Limit Habis (Minus).
   - *Target*: Limit bertambah ~Rp 300rb - 1 Juta.
   ```bash
   php artisan tinker --execute="echo App\Helpers\GlobalHelper::limitKaryawan('K 0895');"
   ```

2. **Deddy Setiawan (K 1730)**
   ```bash
   php artisan tinker --execute="echo App\Helpers\GlobalHelper::limitKaryawan('K 1730');"
   ```

3. **Ongki Pradana (K 1571)**
   ```bash
   php artisan tinker --execute="echo App\Helpers\GlobalHelper::limitKaryawan('K 1571');"
   ```

---

## 5. Rencana Rollback (Jika Gagal)
Jika terjadi kesalahan fatal (misal: menghapus data valid), kembalikan data segera.

**Command Restore:**
```bash
mysql -u root -p esimko < C:\IIS\Esimko\backups\angsuran_belanja_PRE_MASS_FIX.sql
```
*Note: Restore ini akan mengembalikan tabel ke kondisi persis sebelum fix.*

---

## 6. Jadwal Eksekusi
Rekomendasi waktu eksekusi adalah saat transaksi sepi (Malam hari atau jam istirahat), untuk menghindari *table lock* yang lama. Namun karena query ini cepat (< 1 detik), bisa dijalankan kapan saja aman.
