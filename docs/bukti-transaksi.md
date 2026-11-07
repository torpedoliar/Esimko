# Bukti transaksi PDF — web dan Android

## Tujuan dan batasan

Satu PDF dari server Laravel menjadi sumber untuk anggota web, petugas web dan Android. Dokumen hanya tersedia untuk status yang layak; bukti unggahan anggota (`transaksi.bukti_transaksi`) tetap terpisah dan tidak diganti.

- Setoran/penarikan selesai: bukti transaksi.
- Pinjaman disetujui: **bukti persetujuan**, bukan bukti uang telah dicairkan.
- Angsuran dibayar: bukti pembayaran angsuran.
- Belanja sukses: bukti belanja sesuai status jenisnya.
- Ditolak, dibatalkan dan masih menunggu: tidak boleh menerbitkan bukti sukses.

PDF tidak memuat KTP/NIK. Unduhan memerlukan sesi/token dan pemeriksaan kepemilikan di server. Petugas memerlukan otoritas modul yang sesuai. Tidak ada migrasi atau perubahan data keuangan untuk menerbitkan PDF.

## Integrasi

- Web: `GET /bukti-transaksi/{jenis}/{id}` melalui `ceklogin`.
- Android: `GET /api/mobile/bukti/{jenis}/{id}` melalui `mobile.auth`.
- `jenis`: `transaksi`, `angsuran`, `penjualan`.
- Cek keaslian: `GET /cek-bukti?ref=...&kode=...`, rate-limited. Respons tidak boleh di-cache/diindeks; hanya data minimal dan nama tersamar.
- API detail menambah Boolean `bukti_tersedia`. API angsuran tetap mempertahankan `id` pinjaman dan menambah `angsuran_id` untuk unduhan bukti cicilan yang benar.

Kode verifikasi menggunakan HMAC server dari data transaksi, bukan kode buatan klien. Bila data yang ditandatangani berubah, bukti lama tidak cocok lagi. Ini adalah validasi terhadap keadaan server saat diperiksa, bukan arsip immutable/sertifikat tanda tangan elektronik tersertifikasi. Membagikan PDF/QR berarti penerima dapat melihat informasi minimal halaman cek. Penggantian APP_KEY akan membatalkan kode lama.

## Dependensi dan keamanan renderer

Platform Composer dikunci ke PHP 7.4.33. Implementasi menggunakan dompdf langsung (bukan wrapper Laravel lama) dan chillerlan/php-qrcode. Versi awal wrapper 0.9 menarik dompdf 1.2 dengan advisory sehingga tidak digunakan. Lock menambahkan paket fitur tanpa menaikkan paket legacy yang tidak terkait. Advisory legacy yang sudah ada tidak terselesaikan oleh fitur ini dan perlu proyek upgrade terpisah.

Renderer wajib mematikan PHP/JavaScript/remote fetching dan hanya menerima template serta gambar lokal yang dikontrol aplikasi. Cache font/temp di storage khusus aplikasi, bukan folder publik atau vendor. PDF berisi data keuangan tidak disimpan di server sebagai file publik.

## Android

PDF diambil dengan klien terautentikasi, bukan token pada URL atau DownloadManager. Validasi tipe/magic PDF dan batas ukuran sebelum dipublikasikan. Android 10+ menyimpan ke `Download/eSIMKO/` dengan MediaStore pending; Android 9 memakai file privat + Buka/Bagikan tanpa izin penyimpanan tambahan. Aplikasi PDF eksternal/tujuan berbagi menerima content URI dengan izin baca sementara. Tidak semua perangkat memiliki penampil PDF; tampilkan pesan bila tidak ada.

Unduhan yang berpindah sesi saat proses berlangsung harus dibatalkan/dibuang. Debug logging tidak boleh memuat body PDF atau token.

## Verifikasi dan rilis

- PHP 7.4 + GD/DOM/mbstring/ZIP dan SQLite untuk tes lokal.
- Tes: kepemilikan, peran petugas, status sukses per jenis, manipulasi kode, perubahan transaksi, PDF/QR valid, respons tidak cache, regresi belanja.
- Rendering PDF contoh menggunakan data sintetis; cek visual dan scan QR lokal. Jangan menulis transaksi produksi untuk tes tanpa izin terpisah.
- `testDebugUnitTest`, build/sign Android 1.0.28 / code 29; verifikasi manifest, kamera dan semua ikon memakai skrip yang sudah tersedia.
- Sebelum deploy IIS: bandingkan hash file terkait, backup seluruh file terdampak termasuk vendor, composer.lock/config, catat rollback; tidak menjalankan blanket migration atau upgrade dependensi di server.
- Tes pascadeploy baca-saja. Mode wajib/opsional APK ditentukan pengguna sebelum publish, jangan diasumsikan.

## Perubahan lain yang ikut disiapkan

Versi Android ini juga membawa perbaikan kamera dan kelengkapan ikon 1.0.27 yang belum dipublikasikan. Produksi sebelum fitur ini adalah 1.0.26. Pengerjaan lokal/deployment web tidak otomatis berarti APK baru sudah dipublikasikan.
