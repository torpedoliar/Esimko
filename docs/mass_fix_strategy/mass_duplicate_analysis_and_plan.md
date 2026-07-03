# Analisa & Rencana Perbaikan: Mass Duplicate Cleanup

## 1. Executive Summary (Temuan Utama)
Berdasarkan investigasi mendalam pada kasus **K 1662 (Pilot)** dan audit seluruh database, ditemukan bug kritis yang menyebabkan **Limit Anggota Habis** padahal hutang sudah lunas.

- **Masalah Utama**: Duplikasi Data Pembayaran.
- **Dampak Finansial**: Terdapat **Rp 218.489.054** (218 Juta) hutang "Hantu" yang membebani limit anggota.
- **Korban**: **524 Anggota** terdeteksi memiliki limit yang tidak sesuai.
- **Total Transaksi Error**: 1.364 Transaksi.

---

## 2. Analisa Akar Masalah (Root Cause)
Sistem POS sebelumnya memiliki bug saat memproses pelunasan (Status 6). Bukannya mengupdate baris hutang yang ada, sistem malah membuat baris baru.

**Ilustrasi Data Error (Contoh K 1662):**
| ID Transaksi | Angsuran Ke | Total | Status | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| 96793 | 1 | 895.600 | **6 (Lunas)** | ✅ Data Benar (Sudah potong gaji) |
| 96793 | 1 | 895.600 | **3 (Belum)** | ❌ **HANTU** (Bikin limit habis) |
| 96793 | 1 | 895.600 | **3 (Belum)** | ❌ **HANTU** (Double Bikin limit habis) |

Akibatnya, anggota membayar 1x, tapi limit dipotong 3x.

---

## 3. Strategi Perbaikan (Implementation Plan)

Kami akan melakukan perbaikan dalam **2 Fase** untuk memastikan keamanan data.

### FASE 1: Cleanup "Ghost" Duplicates (PRIORITAS UTAMA)
Fokus: Menghapus baris status 3 (Belum Lunas) **HANYA JIKA** transaksi tersebut sudah punya status 6 (Lunas).

**Langkah Teknis:**
1. **Backup Data**: Export tabel `angsuran_belanja` sebelum eksekusi.
2. **Execute Cleanup Script**:
   Menggunakan Query cerdas yang hanya menghapus jika ada duplikat.
   ```sql
   DELETE ab FROM esimko.angsuran_belanja ab
   JOIN (
       SELECT fid_penjualan 
       FROM esimko.angsuran_belanja 
       WHERE fid_status = 6
   ) as lunas ON ab.fid_penjualan = lunas.fid_penjualan
   WHERE ab.fid_status = 3;
   ```
3. **Hasil yang Diharapkan**:
   - 1.364 Baris Hantu terhapus.
   - **Rp 218 Juta Limit** kembali ke 524 anggota.

### FASE 2: Fix Valid Null-Tenor (Secondary)
Setelah Fase 1 selesai, kita akan menghadapi sisa data: Transaksi yang **MEMANG BELUM LUNAS** (Status 3 murni) tapi Tenor-nya NULL.
*Solusi*: Split menjadi cicilan 10 bulan (seperti rencana awal).

---

## 4. Verifikasi & Bukti
Untuk membuktikan keamanan, kami telah melakukan **Pilot Fix pada K 1662**.
- **Sebelum Fix**: Limit sisa Rp 6.300.
- **Setelah Fix (Hapus Hantu)**: Limit kembali menjadi **Rp 901.900**.
- **Validasi**: Sisa potongan adalah transaksi hari ini (11 Feb 2026) yang valid.

## 5. Rekomendasi
Segera jalankan **FASE 1 (Mass Cleanup)**. Ini adalah *Quick Win* yang akan langsung dirasakan manfaatnya oleh 524 anggota tanpa resiko keuangan (karena yang dihapus hanya data sampah).
