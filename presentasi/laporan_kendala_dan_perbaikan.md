# Laporan Kendala Sistem & Solusi Perbaikan
**Project:** Update Esimko (POS & Payroll)
**Tanggal:** 13 Februari 2026

Dokumen ini merinci kendala-kendala teknis yang ditemukan selama audit sistem, penyebab akarnya, serta solusi perbaikan yang telah (atau akan) diimplementasikan.

---

## 1. Bug UI: Ganti Metode Bayar (Cash vs Kredit)
**Kendala:** 
Ketika kasir mengganti metode pembayaran dari "Tunai" ke "Kredit" (atau sebaliknya) di tengah input transaksi, form tidak me-reset inputan sebelumnya dengan bersih.
*Contoh:* User input tunai, lalu berubah pikiran ke kredit, tapi field 'Tunai' masih tersimpan di database atau sebaliknya.

**Penyebab Teknis:**
- Logic di Backend (`PenjualanController`) sebelumnya menerima semua inputan `request` tanpa memfilter berdasarkan metode bayar yang final dipilih.
- Tidak ada validasi tegas untuk menull-kan field yang tidak relevan (misal: `tenor` harus null jika bayar tunai).

**Solusi Perbaikan:**
- **Strict Backend Validation:** Di `PenjualanController`, logic penyimpanan kini secara eksplisit memaksa `NULL` pada field yang tidak sesuai metode bayar.
  - Jika Tunai -> `Tenor = NULL`, `Angsuran = NULL`.
  - Jika Kredit -> `Tunai = NULL`, `Kembali = NULL`.

---

## 2. Bug Data: "Zombie Transaction" (Kasus K 1308)
**Kendala:** 
Limit anggota berkurang, tapi transaksinya tidak muncul di tagihan/laporan hutang.
*Kasus Nyata:* Anggota K 1308 (Susmadi) kehilangan limit Rp 131.000, tapi tidak ada tagihan.

**Penyebab Teknis:**
- **Partial Insert Failure:** Terjadi kegagalan koneksi saat menyimpan data.
  - Langkah 1: Simpan Header Transaksi (Berhasil).
  - Langkah 2: Simpan Rincian Barang/Angsuran (Gagal/Putus).
- Akibatnya tercipta data "Zombie": Header ada (makan limit), tapi Isinya kosong (tidak bisa ditagih).

**Solusi Perbaikan:**
- **Database Transaction (Atomic Lock):** Mengimplementasikan `DB::transaction()` pada proses simpan.
- **Cara Kerja:** Jika Langkah 2 gagal, sistem otomatis membatalkan Langkah 1. Database dijamin bersih (All or Nothing).
- **Status:** *Sudah Diimplementasikan (13 Feb 2026).*

---

## 3. Bug Data: "Ghost Transactions" (41.000 Data Sampah)
**Kendala:** 
Laporan Keuangan & Penjualan menunjukkan angka yang tidak masuk akal (Duplikasi Ratusan Juta), padahal fisik uang/barang tidak ada.
*Temuan:* Terdapat 41.000 baris data transaksi "Hantu" dari tahun 2025 yang terduplikasi secara sistemik.

**Penyebab Teknis:**
- Diduga kesalahan script migrasi atau error perulangan (looping) pada sistem lama yang tidak tertangani, menyebabkan satu transaksi dicatat berkali-kali dengan ID berbeda tapi nomor referensi sama.

**Solusi Perbaikan:**
- **Mass Cleanup Script:** Script khusus untuk mendeteksi duplikat, memilih satu yang valid, dan menghapus sisanya.
- **Date Filter:** Membatasi sistem agar hanya memproses data valid mulai 2026 (Untuk mencegah sampah lama naik lagi).

---

## 4. Bug System: Payroll Gagal (Memory Crash)
**Kendala:** 
Proses Payroll bulanan sering gagal (Time Out / White Screen) atau server hang saat dijalankan.

**Penyebab Teknis:**
- **Memory Exhaustion:** Script Payroll mencoba memuat seluruh riwayat hutang (termasuk 41.000 data hantu tadi) ke dalam RAM sekaligus.
- Server kehabisan nafas karena beban data sampah terlalu besar.

**Solusi Perbaikan:**
- **Smart Filtering:** Memasang filter tanggal (`>= 2026-01-01`) pada controller Payroll.
- **Efek:** Beban data berkurang 90%. Proses Payroll menjadi ringan dan cepat.

---

## 5. Bug Logic: Limit Anggota (Kasus K 0551)
**Kendala:** 
Anggota merasa limitnya tidak sesuai. *"Saya baru belanja 148rb, kenapa sisa limit tinggal segini?"*

**Penyebab Teknis:**
- **Persepsi vs Realita:** Ada transaksi lain (Konsinyasi/Toko) yang masuk lebih dulu secara sistem tapi belum disadari user.
- **Delay Sinkronisasi:** Tampilan limit di HP user mungkin belum refresh saat transaksi baru diinput admin.

**Solusi Perbaikan:**
- **Edukasi & Transparansi:** Menjelaskan bahwa limit bersifat Real-Time Global (Gabungan Toko + Konsinyasi + Pinjaman).
- **Validasi Data:** Memastikan coding perhitungan limit (`GlobalHelper`) mengakumulasi semua status hutang yang valid (Status 2 & 4).

---

## 6. Bug Data: "Missed Cutoff" (150 Anggota)
**Kendala:** 
Potongan gaji tidak masuk, padahal transaksi ada. Atau status transaksi "Lunas" padahal belum bayar.

**Penyebab Teknis:**
- Ketidakkonsistenan Status (`fid_status`) antara Header Transaksi dan Tabel Angsuran karena intervensi manual atau bug lawas.

**Solusi Perbaikan:**
- **Synchronization Script:** Menjalankan script diagnosa untuk mencocokkan status Header & Angsuran. Jika Ganjil -> Lakukan Auto-Correct.
