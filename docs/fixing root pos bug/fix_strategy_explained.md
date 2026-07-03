# Penjelasan Detail: Cara Kerja "Fix All" (Mass Fix)

Bapak bertanya: *"Sistem fix semuanya seperti apa?"*
Berikut penjelasan detail dengan bahasa yang mudah dipahami.

## 1. Analogi: "Memecah Batu Besar"
Bayangkan sistem Bapak sekarang mencatat hutang anggota sebagai **satu batu besar**.
- **Kondisi Sekarang (Salah):**
  - Hutang Rp 1.000.000 tercatat sebagai **1x Bayar**.
  - **Efek Limit:** Karena dianggap harus lunas bulan ini, limit anggota langsung dipotong Rp 1.000.000 (Habis).
  - **Efek Gaji:** Payroll akan mencoba memotong Rp 1.000.000 sekaligus (Member mengeluh gaji habis).

- **Setelah Di-Fix (Benar):**
  - Kita pecah batu besar itu menjadi **10 kerikil kecil** (Tenor 10 bulan).
  - **Efek Limit:** Bulan ini hanya dianggap berhutang Rp 100.000. **Limit sisa Rp 900.000 KEMBALI ke anggota.**
  - **Efek Gaji:** Payroll hanya memotong Rp 100.000 per bulan (Ringan).

---

## 2. Teknis: Apa yang Script Saya Lakukan?
Script "Fix All" akan bekerja secara otomatis memproses 41.349 data satu per satu dengan langkah berikut:

### Langkah A: Koreksi Induk (Tabel Penjualan)
Script mencari transaksi yang Tenor-nya NULL, lalu mengubahnya:
- `Tenor`: Diubah jadi **10**.
- `Angsuran`: Diubah jadi **Total / 10**.

### Langkah B: Generasi Anak (Tabel Angsuran Belanja)
Ini bagian paling penting.
- **Sekarang**: Di database hanya ada 1 baris tagihan (Baris ke-1).
- **Aksi Script**: Script akan secara otomatis **menciptakan (insert)** 9 baris tagihan baru untuk bulan ke-2 sampai ke-10.

**Ilustrasi Tabel Database:**
*Sebelum Fix:*
| Bulan ke | Jumlah Tagihan | Status |
| :--- | :--- | :--- |
| 1 | Rp 1.000.000 | Belum Lunas |

*Setelah Fix:*
| Bulan ke | Jumlah Tagihan | Status |
| :--- | :--- | :--- |
| 1 | Rp 100.000 | Belum Lunas |
| 2 | Rp 100.000 | Belum Lunas (Baru!) |
| ... | ... | ... |
| 10 | Rp 100.000 | Belum Lunas (Baru!) |

---

## 3. PENTING: Keputusan untuk Data Lama (2023-2024)
Untuk data tahun 2025-2026, jelas kita buat statusnya "Belum Lunas" agar dipotong gaji.
Tapi untuk data **2023-2024** (total ~27.000 transaksi), kita punya 2 pilihan:

### Opsi A: Anggap Lunas (Write-off Histories) - **RECOMMENDED**
- Kita ubah tenor jadi 10.
- Kita generate cicilan barunya, TAPI statusnya langsung kita set **LUNAS (Paid)**.
- **Hasil:** Limit anggota kembali penuh, tapi **Gaji Tidak Dipotong** lagi (karena dianggap hutang masa lalu yang sudah beres).
- *Cocok untuk merapikan data tanpa bikin kaget anggota.*

### Opsi B: Tagih Kembali (Collect All)
- Kita ubah tenor jadi 10.
- Masukkan cicilan baru dengan status **BELUM LUNAS**.
- **Hasil:** Limit anggota kembali penuh, tapi **Gaji Bulan Depan akan dipotong** untuk hutang tahun 2023 yang dulu sempat "hilang".
- *Resiko: Anggota akan protes keras "Kenapa hutang 2023 muncul lagi?".*

### Rekomendasi Saya:
Gunakan **Opsi A (Anggap Lunas)** untuk data 2023-2024.
Gunakan **Opsi B (Tagih)** untuk data 2025-2026.

Apakah penjelasan ini cukup jelas Pak?
