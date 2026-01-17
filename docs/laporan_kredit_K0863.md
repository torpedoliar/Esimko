# Laporan Kredit Anggota K 0863

**Tanggal Laporan:** 13 Januari 2026

---

## Informasi Anggota

| Field | Value |
|-------|-------|
| No. Anggota | K 0863 |
| Nama | GOENAWAN |
| Limit Kredit | Rp 1,500,000 |

---

## Ringkasan Kredit Aktif

| Metrik | Nilai |
|--------|-------|
| Total Transaksi Kredit Aktif | **90 transaksi** |
| Total Nilai Belanja | **Rp 45,853,150** |
| Total Sudah Dibayar | Rp 45,520,350 |
| **Total Hutang (Belum Bayar)** | **Rp 3,829,000** |

---

## Status Angsuran

| Status | Jumlah | Total |
|--------|--------|-------|
| Status 6 (Lunas) | 125 angsuran | Rp 45,520,350 |
| Status 3 (Belum Bayar) | 4 angsuran | Rp 3,829,000 |

---

## Transaksi dengan Hutang Aktif

| No Transaksi | Tanggal | Total Belanja | Hutang |
|--------------|---------|---------------|--------|
| JL-0019-20251211000136 | 2025-12-11 | Rp 1,165,400 | **Rp 3,496,200** |
| JL-0019-20260109000097 | 2026-01-09 | Rp 332,800 | **Rp 332,800** |

---

## ⚠️ ANOMALI DITEMUKAN: Duplikasi Angsuran

### Transaksi: JL-0019-20251211000136

| ID | Angsuran Ke | Total | Status |
|----|-------------|-------|--------|
| 278670 | 1 | Rp 1,165,400 | ✅ Lunas (6) |
| 278671 | 1 | Rp 1,165,400 | ❌ Belum Bayar (3) - **DUPLIKAT** |
| 278672 | 1 | Rp 1,165,400 | ❌ Belum Bayar (3) - **DUPLIKAT** |
| 278673 | 1 | Rp 1,165,400 | ❌ Belum Bayar (3) - **DUPLIKAT** |

**Penyebab:** Ada 4 record angsuran ke-1 padahal hanya butuh 1. Kemungkinan:
1. Staff menekan tombol submit berkali-kali
2. Bug di sistem saat membuat angsuran
3. Import data yang salah

**Solusi:** Hapus 3 record duplikat (ID: 278671, 278672, 278673)

```sql
-- Query untuk menghapus duplikat
DELETE FROM angsuran_belanja WHERE id IN (278671, 278672, 278673);
```

---

## Detail 30 Transaksi Terbaru

| No Transaksi | Tgl Beli | Total | Angsuran | Belum Bayar | Sudah Bayar |
|--------------|----------|-------|----------|-------------|-------------|
| JL-0019-20260109000097 | 2026-01-09 | 332,800 | 1 | 332,800 | 0 |
| JL-0019-20260102000026 | 2026-01-02 | 175,000 | 1 | 0 | 175,000 |
| JL-0019-20251224000012 | 2025-12-24 | 51,500 | 1 | 0 | 51,500 |
| JLK-0019-20251212000010 | 2025-12-12 | 105,000 | 1 | 0 | 105,000 |
| JL-0019-20251211000136 | 2025-12-11 | 1,165,400 | 4 | 3,496,200 | 1,165,400 |
| JL-0019-20251202000038 | 2025-12-02 | 124,000 | 1 | 0 | 124,000 |
| JLK-0019-20251125000005 | 2025-11-25 | 205,000 | 1 | 0 | 205,000 |
| JL-0019-20251125000009 | 2025-11-25 | 103,000 | 1 | 0 | 103,000 |
| JL-0019-20251119000060 | 2025-11-19 | 76,500 | 1 | 0 | 76,500 |
| JL-0019-20251111000049 | 2025-11-11 | 987,700 | 1 | 0 | 987,700 |
| JL-0019-20251029000029 | 2025-10-29 | 213,800 | 1 | 0 | 213,800 |
| JL-0019-20251020000074 | 2025-10-20 | 271,500 | 1 | 0 | 271,500 |
| JL-0019-20251010000125 | 2025-10-10 | 943,400 | 1 | 0 | 943,400 |
| JL-0019-20251001000032 | 2025-10-01 | 377,900 | 1 | 0 | 377,900 |
| JL-0019-20250911000158 | 2025-09-11 | 849,900 | 1 | 0 | 849,900 |
| JL-0019-20250819000034 | 2025-08-19 | 267,000 | 1 | 0 | 267,000 |
| JL-0019-20250812000100 | 2025-08-12 | 686,000 | 1 | 0 | 686,000 |
| JL-0019-20250710000064 | 2025-07-10 | 1,134,100 | 1 | 0 | 1,134,100 |
| JL-0019-20250623000017 | 2025-06-23 | 376,000 | 1 | 0 | 376,000 |
| JL-0019-20250610000159 | 2025-06-10 | 739,000 | 1 | 0 | 739,000 |
| JL-0019-20250516000036 | 2025-05-16 | 78,500 | 1 | 0 | 78,500 |
| JL-0019-20250509000119 | 2025-05-09 | 1,028,400 | 1 | 0 | 1,028,400 |
| JL-0019-20250415000047 | 2025-04-15 | 487,000 | 1 | 0 | 487,000 |
| JL-0019-20250325000092 | 2025-03-25 | 432,000 | 1 | 0 | 432,000 |
| JL-0019-20250319000025 | 2025-03-19 | 232,500 | 1 | 0 | 232,500 |
| JL-0019-20250310000118 | 2025-03-10 | 607,500 | 1 | 0 | 607,500 |
| JL-0019-20250301000022 | 2025-03-01 | 206,500 | 1 | 0 | 206,500 |
| JL-0019-20250210000111 | 2025-02-10 | 1,057,100 | 1 | 0 | 1,057,100 |
| JL-0019-20250130000069 | 2025-01-30 | 59,500 | 1 | 0 | 59,500 |
| JL-0019-20250117000060 | 2025-01-17 | 394,000 | 1 | 0 | 394,000 |

---

## Masalah yang Ditemukan

### 1. Status Transaksi Tidak Diupdate
90 transaksi kredit masih berstatus "aktif" (status 2 atau 4) padahal sebagian besar angsuran sudah dibayar (125 angsuran lunas).

**Rekomendasi:** Update status penjualan menjadi "Lunas" untuk transaksi yang semua angsurannya sudah dibayar.

### 2. Anomali pada Transaksi JL-0019-20251211000136
- Total belanja: Rp 1,165,400
- Total hutang: Rp 3,496,200
- Jumlah angsuran: 4

Hutang 3x lipat dari total belanja menunjukkan ada duplikasi data angsuran.

**Rekomendasi:** Periksa dan hapus angsuran duplikat.

### 3. Limit Kredit Terlampaui
Dengan limit Rp 1,500,000 dan hutang aktif Rp 3,829,000, anggota ini seharusnya **tidak bisa melakukan transaksi kredit baru**.

---

## Kesimpulan

| Pertanyaan | Jawaban |
|------------|---------|
| Kesalahan sistem limit? | ❌ **TIDAK** - Sistem benar menolak karena hutang melebihi limit |
| Data anomali? | ✅ **YA** - Ada duplikasi angsuran pada transaksi Des 2025 |
| Hutang sebenarnya | Rp 332,800 (transaksi terakhir) + masalah duplikasi |
| Perlu investigasi? | ✅ **YA** - Periksa transaksi JL-0019-20251211000136 |

---

*Laporan dibuat otomatis oleh sistem analisa eSIMKO*
