# Deep Analysis: Penyebab Selisih Limit Anggota

Mengapa limit K 1662, K 0863, dan ribuan anggota lain tidak sesuai (terpotong terlalu besar)?

## 1. Akar Masalah: Data "Corrupt" (Tenor Hilang)
Hasil investigasi database menunjukkan fakta mengejutkan:
> **41,349 Transaksi Kredit** di database memiliki nilai `angsuran` = NULL dan `tenor` = NULL. 

### Simulasi Masalah (Kasus K 1662)
- Anggota belanja kredit senilai **Rp 895,600** (ID 96793).
- **Seharusnya**: Tenor 10 bulan → Cicilan **Rp 89,560** / bulan.
- **Fakta di Database**: 
  - Kolom `tenor`: **NULL** (Kosong)
  - Kolom `angsuran`: **NULL** (Kosong)
  - Tabel `angsuran_belanja`: Tercatat angka **Rp 895,600** (Full Harga!)

### Konsekuensi pada Rumus Limit
Baik rumus lama maupun rumus baru yang saya buat, akan "terkecoh" oleh data ini.
```php
// Logika Safety Fallback (yang saya buat):
if (angsuran == NULL) {
   $tenor = ($p->tenor > 0) ? $p->tenor : 1; // Jika tenor kosong, dianggap 1 bulan!
   $potongan = Total / 1; 
}
// Hasil: Rp 895,600 / 1 = Rp 895,600 (Limit langsung habis!)
```

## 2. Mengapa Ini Bisa Terjadi?
Ini adalah **Dampak Jangka Panjang** dari bug controller lama. Dulu, saat transaksi disimpan, kode controller **lupa** menyimpan kolom `tenor` dan `angsuran` ke tabel `penjualan`.
- Jadi selama berbulan-bulan/tahun, ribuan transaksi masuk tanpa data tenor yang jelas.
- Sistem berjalan "seolah-olah" benar karena frontend mungkin hanya menampilkan total, tapi hitungan limit di backend hancur.

## 3. Solusi: Data Fix Massal (Wajib!)
Kita tidak bisa memperbaiki ini hanya dengan kode (coding), karena sistem "tidak tahu" transaksi-transaksi lampau itu maunya tenor berapa bulan.

**Rekomendasi Tindakan:**
Karena mayoritas kredit toko adalah **10 Bulan**, saya menyarankan kita menjalankan perintah SQL untuk "menambal" 41,349 data kosong ini dengan asumsi Tenor 10x.

**Plan Perbaikan Data:**
1. Cari semua transaksi kredit (`fid_metode_pembayaran=3`) yang `angsuran` nya NULL/0.
2. Update `tenor` menjadi **10**.
3. Update `angsuran` menjadi `total_pembayaran / 10`.
4. Update juga tabel detail `angsuran_belanja` agar sinkron.

### Estimasi Hasil Setelah Fix:
- **K 1662**: Potongan limit turun dari 895rb menjadi 89rb. Limit kembali lega.
- **K 0863**: Potongan limit kembali wajar.
- **41,000+ Transaksi lain**: Limit anggota lain pun akan otomatis "sembuh".

Apakah Anda setuju saya jalankan **Mass Data Fix** ini dengan asumsi Tenor 10 bulan?
