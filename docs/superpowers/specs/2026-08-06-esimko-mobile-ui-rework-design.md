# Rework UI/UX EsimkoMobile — Design Spec

Tanggal: 2026-08-06
Status: disetujui (per-bagian), eksekusi sekali jalan (bukan bertahap-dengan-jeda)

## Masalah

Tiga keluhan pengguna atas app Kotlin/Compose `EsimkoMobile`:

1. **Menu tidak cocok domain koperasi.** Bottom nav `Home/Simpanan/Belanja/Riwayat/Profil`.
   Pinjaman — fungsi inti koperasi — bukan tab, hanya terjangkau via ketuk kartu "Saldo
   Pinjaman" di dashboard. Tab "Riwayat" memakai dropdown "Ganti Jenis" berisi nama modul
   backend mentah (`transaksi`, `penjualan`). Endpoint `belanja/angsuran` dan `belanja/retur`
   nol UI. Rute `news` terdaftar tapi tidak ada navigasi ke sana. 12 field `Profile`
   diambil tapi tidak pernah dirender.
2. **Layout terasa "AI slop".** Tiap layar identik: `padding(16.dp)` + `spacedBy(16.dp)` +
   tumpukan `Card`. Kartu hero warna `primary` diulang di 4 layar. Tiga konvensi header
   berbeda. `Column.verticalScroll` alih-alih `LazyColumn`. Loading/error/konten satu `Box`
   sehingga bisa tumpang tindih. Nominal tanpa tabular figures. Dialog untuk input uang.
3. **Terlalu standar, tidak seperti app perbankan modern** (acuan pengguna: Livin, BCA
   mobile, blu, Bibit, Jenius, Jago). Font `FontFamily.Default` (Roboto). Logo esimko tidak
   dipakai sama sekali — launcher icon masih placeholder hijau Android `#3DDC84`.

## Temuan kunci

App ini **tidak punya transaksi instan**. Setoran, penarikan, pinjaman semuanya *pengajuan*
yang menunggu verifikasi pengurus. Meniru bentuk banking app mentah salah arah: yang paling
dibutuhkan anggota adalah "pengajuan saya sudah sampai mana" — dan itu sekarang tidak ada
di mana pun.

Backend sudah cukup: `transaksi/{modul}` dukung filter `jenis`/`status`/tanggal/pagination
dan kirim `color` per status; `produk` dukung `search` + kirim `kelompok`/`kategori`;
`detail_berita` kirim array `attachment`. Client belum memakai semuanya.
**Nol perubahan backend** untuk seluruh rework.

## Keputusan

| Aspek | Keputusan |
|---|---|
| Navigasi | 4 tab: Beranda · Aktivitas · Belanja · Akun (Opsi B) |
| Simpanan/Pinjaman | Diakses dari grid aksi di Beranda, bukan tab |
| Riwayat + status | Dilebur jadi tab "Aktivitas" |
| Warna | Hijau `#118334` dipertahankan; palet diperluas dari segel logo |
| Font | Plus Jakarta Sans bundled (`res/font`), 3 weight, tabular figures |
| Logo | Dipakai: launcher icon + login + bar Beranda |
| Dark mode | Didukung penuh, tiap `@Preview` dobel |
| Belanja | Gaya toko online (Indomaret/Alfamart): search, chip kategori, grid 2 kolom |
| Berita | HTML dirender, lampiran bisa dibuka, urut terbaru dulu di client |
| Backend | Tidak disentuh |

## 1. Informasi & Navigasi

**4 tab:** Beranda · Aktivitas · Belanja · Akun

Prinsip: tab untuk *tempat*, grid untuk *aksi*, Aktivitas untuk *status*. Akar keluhan
"menu tidak cocok" bukan tab kurang, tapi tab dipakai untuk hal yang seharusnya aksi
("Riwayat" itu aksi lintas-domain, bukan tempat).

### Beranda
- Bar atas: wordmark esimko (kiri, kecil) + avatar (kanan)
- Hero saldo: kartu hijau pekat, angka utama Saldo Simpanan. Bisa dibuka → rincian
  Pokok / Wajib / Sukarela / Hari Raya (4 field yang sekarang diambil tapi dibuang)
- Grid aksi 4×2: Setor · Tarik · Ajukan Pinjaman · Angsuran Pinjaman · Angsuran Belanja ·
  Retur Barang · Simpanan · Berita
- Strip "Pengajuan berjalan" — hanya muncul kalau ada yang menunggu verifikasi, ketuk → Aktivitas
- Berita 2 item + "Lihat semua"

### Aktivitas
- Chip filter: Semua / Menunggu / Disetujui / Ditolak (dari `master/status_transaksi`)
- Grup "Berjalan" di atas, "Selesai" di bawah; header bulan menempel saat scroll
- `StatusChip` pakai `color` dari backend **plus** ikon (tidak bergantung warna sendirian)
- Dropdown "Ganti Jenis" dibuang; label manusia: Simpanan, Pinjaman, Belanja
- Selalu kirim `page` (tanpa `page` backend balikkan seluruh riwayat)

### Belanja — gaya toko online
- Search bar menempel di atas + ikon keranjang berbadge. Pakai param `search` yang sudah ada
  di backend, debounce 400ms
- Baris chip kategori gulir horizontal, dari `kelompok` produk
- `LazyVerticalGrid` 2 kolom. Kartu: foto 1:1, nama maks 2 baris, harga tebal + `/satuan`,
  tombol **+** di sudut kanan bawah → tambah keranjang tanpa buka detail. Stok habis: kartu
  diredam, label "Habis", tombol + mati
- Bar keranjang menempel di bawah, muncul kalau keranjang tidak kosong: "3 barang · Rp 145.000"
  + "Lihat Keranjang"
- Scroll mentok bawah → muat halaman berikutnya
- Riwayat/angsuran/retur belanja pindah ke Aktivitas; tombol "Riwayat" di header Belanja dibuang
- Tidak ada banner promo — tidak ada endpoint promo

### Akun
- Profil + `divisi`, `bagian`, `statusAnggota` (sekarang diambil, tidak dirender)
- Pengaturan / Ubah Password / Logout jadi baris daftar, bukan tumpukan tombol lebar

### Teknis
`when(selectedTab)` diganti NavHost bersarang + `saveState`/`restoreState`. Sekarang pindah
tab menghapus posisi scroll dan tidak ada deep link.

## 2. Warna & Tipografi

Palet diambil dari segel logo (hijau tua, emas rantai, hitam wordmark). Tidak menambah
warna asing.

### Tangga hijau

| Peran | Light | Dark | Pakai di |
|---|---|---|---|
| Hero (hijau pekat) | `#0B3D22` | `#0E2A19` | kartu saldo, header |
| Primary (brand) | `#118334` | `#7BD494` | tombol, ikon aktif, chip terpilih |
| Surface latar | `#F7FAF7` | `#101510` | latar layar |

`#118334` tetap primer. Bedanya sekarang punya pasangan gelap — itu yang bikin hero terasa
seperti blu/Jago, bukan kartu hijau pucat.

### Emas segel
`#F2C230` **hanya** di atas hijau pekat (angka nominal hero, aksen aktif). Di atas putih
kontrasnya 1.68:1 — jauh di bawah 4.5:1. Versi di atas putih pakai `#7A5E1F` (6.09:1).
Aturan ini ditulis di token supaya tidak salah pakai.

### Kontras terhitung (WCAG AA butuh 4.5:1)

```
12.32  putih di hero (#FFFFFF / #0B3D22)
 7.35  emas di hero  (#F2C230 / #0B3D22)
 4.86  primary di putih (#118334 / #FFFFFF)
 6.09  emas-tua di putih (#7A5E1F / #FFFFFF)
15.46  teks di latar (#17231A / #F7FAF7)
10.28  primary di dark (#7BD494 / #101510)
11.59  emas di dark (#F0C94F / #101510)
14.72  teks di dark (#DCE9DC / #101510)
```

Semua lulus. Paling mepet `#118334` di putih (4.86) — aman untuk teks; tombol besar tetap
pakai teks putih di atas hijau.

### Tipografi — Plus Jakarta Sans, 3 weight

| Peran | Ukuran/Weight |
|---|---|
| Saldo hero | 32sp Bold, tabular |
| Nominal daftar | 16sp Bold, tabular |
| Judul layar | 22sp Bold |
| Judul kartu | 16sp Medium |
| Isi | 14sp Regular |
| Label/caption | 12sp Medium |

`FontFeatureSetting("tnum")` di semua gaya nominal — saldo tidak goyang saat angka ganti.
Isi minimal 14sp, tidak ada teks di bawah 12sp.

### Status bar
`SideEffect { window.statusBarColor = primary }` di `Theme.kt` **dihapus** — itu bikin status
bar hijau terang menempel di semua layar. Diganti edge-to-edge: hero hijau pekat mengalir ke
belakang status bar, layar lain transparan.

## 3. Pola layout & komponen

Akar "AI slop" bukan warna — tapi tiap layar bentuknya identik. Perbaikannya struktural.

### Tiga aturan pengganti
1. **Satu hero per layar, kartu untuk sisanya.** Hero hijau pekat hanya di Beranda. Layar
   lain buka dengan judul + angka besar tanpa kotak.
2. **Latar bertingkat, bukan kartu bertumpuk.** Daftar (riwayat, angsuran, produk) jadi baris
   dengan pemisah tipis, bukan kartu per item. 20 kartu berbayang terlihat seperti tumpukan
   kuitansi.
3. **Ritme jarak 4/8/16/24** — 4 dalam satu baris, 8 antar elemen sekerabat, 16 antar
   kelompok, 24 antar bagian besar.

### Header — satu konvensi
- Layar tab: judul besar mengalir dengan konten, mengecil saat scroll
- Layar tumpukan (Ajukan Pinjaman, Detail Produk, Keranjang, Pengaturan): `TopAppBar` + kembali

### Komponen

Dibuang (ditandai `@Deprecated` lebih dulu, dihapus setelah pemakai terakhir hilang):
- `EsimkoCard` — pembungkus 19 baris yang tidak menambah apa pun
- `EsimkoButton` / `EsimkoOutlinedButton` — `fillMaxWidth().height(56.dp)` paksa; penyebab
  profil jadi tumpukan tombol lebar
- Kalimat mati di `EmptyStateView`: "Data belum tersedia, coba lagi nanti atau hubungi kasir."

Ditambah:
- `Money(value, style)` — satu-satunya jalan tampil rupiah, tabular dijamin
- `StatusChip(status, color)` — warna backend + ikon
- `ActionGrid` — grid aksi Beranda
- `SectionHeader(title, action)`
- `AmountField` — input nominal, layar penuh bukan dialog

### Dialog nominal diganti layar
Setoran/penarikan sekarang `AlertDialog` yang di tengah jalan berubah jadi langkah unggah
bukti. Dialog yang ganti isi membingungkan, dan kalau tertutup tak sengaja data hilang.
Jadi satu layar dua langkah: nominal → unggah bukti, dengan indikator langkah dan konfirmasi
sebelum keluar.

### Gerak
Tekan kartu mengecil ke 0.98; masuk daftar bertahap 40ms; pindah layar geser sesuai arah;
semua 150–250ms; hormati `prefers-reduced-motion`. Tidak ada animasi hias.

### Preview
Setiap layar dapat `@Preview` light + dark. Sekarang nol — tidak ada yang bisa dilihat di
Android Studio tanpa build APK.

## 4. Papan informasi (berita)

Empat masalah yang bikin berita tidak terbaca sekarang:

1. **Isi HTML mentah.** Form admin pakai TinyMCE, `content` berisi `<p>`/`<strong>`/`<ul>`.
   Compose menaruhnya di `Text(detail.konten)` → tampil apa adanya. Solusi:
   `AnnotatedString.fromHtml()` (butuh Compose BOM ≥ 2024.09), fallback `HtmlCompat.fromHtml`
   dari `androidx.core` yang sudah ada.
2. **Lampiran hilang total.** `detail_berita` kirim array `attachment` (judul + URL); list
   kirim `jumlah_attachment`. DTO Kotlin tidak menangkap satu pun — lampiran rapat/edaran/
   notulen tidak pernah kelihatan. Perbaikan: tambah field di DTO + model, render daftar
   lampiran yang bisa dibuka (`Intent.ACTION_VIEW`), badge jumlah di daftar berita.
3. **Tidak punya pintu.** Rute `news` terdaftar tapi tak ada yang menavigasi ke sana;
   dashboard hanya buka detail 3 berita teratas, berita ke-4 tak terjangkau. Perbaikan: aksi
   "Berita" di grid Beranda + "Lihat semua".
4. **Urutan terbalik.** `Berita::orderBy('created_at')` tanpa `DESC` → terlama di atas.
   Diurut di client (keputusan pengguna: backend tidak disentuh). Lihat Risiko.

Layar detail: judul 22sp Bold, tanggal, gambar rasio 16:9 (bukan `height(200.dp)` yang
memotong), isi 16sp `lineHeight` 1.6, bagian **Lampiran** (baris per file, ikon sesuai jenis,
ketuk buka), pencarian berita (param `search` sudah ada di backend).

## 5. Urutan kerja

Dikerjakan sekali jalan (pengguna berubah pikiran dari bertahap-dengan-jeda). Urutan tetap
penting: fondasi lebih dulu, kalau tidak semua layar dikerjakan dua kali.

| Tahap | Isi |
|---|---|
| 0. Fondasi | Font ke `res/font`; token warna hero/emas; `Type.kt` tabular; `Theme.kt` edge-to-edge; logo ke `res/drawable` + launcher icon; komponen baru; `@Deprecated` komponen lama; coba naikkan Compose BOM |
| 1. Navigasi | 4 tab, NavHost bersarang + `saveState`, insets |
| 2. Beranda | Hero + rincian simpanan; `ActionGrid`; strip pengajuan berjalan; berita 2 item |
| 3. Aktivitas | Chip filter status; grup Berjalan/Selesai; header bulan menempel; `StatusChip`; label manusia; perbaiki bug `onBack != {}` |
| 4. Simpanan | Layar nominal 2 langkah ganti dialog; `AmountField`; konfirmasi keluar; unggah bukti |
| 5. Pinjaman | Daftar angsuran; form pengajuan dirombak; hitung angsuran keluar dari UI ke ViewModel |
| 6. Belanja | Search menempel; chip kategori; grid 2 kolom; tombol + di kartu; bar keranjang; paginasi scroll |
| 7. Papan informasi | HTML dirender; lampiran (DTO+model+UI); urut terbaru dulu; pencarian; pintu dari Beranda |
| 8. Akun | Profil + divisi/bagian/status; baris daftar ganti tombol lebar; perbaiki password mismatch yang sekarang diam |
| 9. Poles | Login/Register pakai logo; skeleton; gerak; sapuan aksesibilitas; cek semua preview dark |

### Cacat non-kosmetik yang diperbaiki sambil jalan
- `HistoryTab.kt:38` — `onBack != {}` membandingkan identitas lambda, selalu `true`
- `ProfileScreen.kt` — password tidak sama → cabang kosong `// Show error`, user tak dapat pesan
- `LoanApplicationScreen.kt:288` — bunga pinjaman dihitung di UI

## 6. Keadaan data, gagal, aksesibilitas

### Keadaan saling eksklusif
Sekarang `LoadingOverlay`/`ErrorView`/konten satu `Box` — bisa tampil bersamaan.

| Keadaan | Tampilan |
|---|---|
| Muat pertama | Skeleton berbentuk konten aslinya, bukan spinner tengah |
| Muat ulang | `PullToRefresh`, konten lama tetap tampil |
| Sukses kosong | Pesan sesuai konteks + aksi kalau ada |
| Gagal, ada data lama | Konten lama tetap + banner tipis "Gagal memuat · Coba lagi" |
| Gagal, tak ada data | Layar penuh: pesan + Coba Lagi |
| Kirim | Tombol mati + spinner di tombol, bukan overlay seluruh layar |

Gagal saat sudah ada data **tidak menghapus data**. Sekarang error menutupi konten.

### Pesan kosong per konteks
Aktivitas "Belum ada pengajuan. Mulai dari Beranda." · Angsuran "Tidak ada angsuran
berjalan." · Keranjang "Keranjang kosong." + Mulai Belanja · Berita "Belum ada informasi." ·
Pencarian nihil "Tidak ada produk untuk \"kopi\"." + Hapus pencarian

### Pesan gagal
`apiErrorMessage()` sudah ambil `message` dari envelope backend — dipertahankan. Tambahan:
`IOException` → "Tidak ada koneksi. Periksa jaringan." (sekarang user lihat `e.message` mentah
macam `failed to connect to /10.10.6.9`). 401 auto-logout dipertahankan. Tiap pesan gagal
wajib punya jalan keluar.

### Aksesibilitas (tidak dipangkas)
- Target ketuk ≥ 48dp. Tombol + di kartu produk: gambar boleh 32dp, area ketuk 48dp
- Tiap tombol ikon punya `contentDescription`
- Kontras semua pasangan ≥ 4.5:1 (terhitung di bagian 2)
- Status = warna **dan** ikon
- `semantics` pada hero: dibaca "Saldo simpanan, Rp 4.250.000", bukan angka lepas
- Skala teks sistem dihormati; pada 200% tidak ada teks terpotong — kartu tinggi-tetap dihindari
- `prefers-reduced-motion` mematikan animasi masuk

### Uang
`Money` satu-satunya jalan tampil rupiah. Nol tampil "Rp 0", bukan kosong. Negatif
(penarikan) pakai minus + warna **plus** label.

### Cek yang ditinggalkan (satu per area, bukan suite penuh)
- Unit test `Money` — ribuan, nol, negatif
- Unit test pengelompokan Berjalan/Selesai + urutan
- Unit test hitung angsuran setelah pindah ke ViewModel
- Unit test HTML→teks (tag terkupas, entity `&amp;`) + urutan berita terbaru dulu
- `@Preview` sebagai cek visual, `assembleDebug` sebagai cek kompilasi

## 7. Risiko & batasan yang diterima

**Urut-di-client punya batas nyata.** Backend `berita` paginasi terlama dulu. Ambil halaman 1
lalu urut di client = dapat 20 berita **tertua** yang diurut terbalik; berita terbaru tidak
pernah muncul. Mitigasi: minta `per_page=50` sekali, urut di client. Aman selama total berita
≤ 50; lewat itu berita baru mulai hilang. Ditandai `ponytail:` di kode. Perbaikan benarnya
satu kata di backend (`->orderBy('created_at','DESC')`) — pengguna memutuskan backend tidak
disentuh.

**Pengelompokan Berjalan/Selesai pakai dugaan.** API kirim daftar status tapi tidak menandai
mana yang final. Ditebak dari nama (`Lunas`, `Ditolak`, `Batal`, `Selesai` = selesai; sisanya
berjalan). Status baru dengan nama lain akan masuk "Berjalan". Ceiling ditulis di komentar.

**Compose BOM dinaikkan — bisa gagal.** Sekarang 2024.02 + compiler 1.5.8 (Kotlin 1.9.22).
Kalau kompilasi rewel, tidak dipaksa; turun ke `HtmlCompat.fromHtml` untuk HTML dan tombol
"Muat ulang" biasa untuk refresh (`PullToRefreshBox` hanya ada di material3 1.3+). Grid produk
aman di BOM sekarang. Hasil akhir dilaporkan, tidak diam-diam.

**Font harus diunduh.** Plus Jakarta Sans OFL. Kalau jaringan menghalangi: dep
`ui-text-google-fonts` (butuh Play Services), atau Roboto + tabular saja. Prioritas bundle TTF.

**Ikon launcher dari segel = padat.** Segel punya rantai, timbangan, padi, teks melingkar; di
48dp jadi bubur. Dipasang di atas hijau brand dengan zona aman — layak, tapi bukan ikon yang
dirancang ulang untuk ukuran kecil. Mark tersederhanakan adalah kerja desain, bukan kode.

**Cakupan ViewModel berubah saat NavHost bersarang.** `hiltViewModel()` sekarang menempel ke
entri "home"; setelah bersarang tiap tab dapat instance sendiri. Rawan: badge keranjang di
Belanja vs Beranda beda angka. Mitigasi: VM keranjang di-scope ke graf, bukan ke tab.

**Aktivitas selalu kirim `page`.** Tanpa `page`, `transaksi/{modul}` balikkan seluruh riwayat —
berat untuk anggota lama. Bentuk respons beda antara mode paginasi dan non (`meta` hanya ada
saat paginasi); client tangani keduanya.

**Layar lama ikut kena tahap 0.** Token dan edge-to-edge berlaku global, jadi layar yang belum
dirombak berubah rasa sebelum gilirannya. Diterima.

**Bar navigasi Android 9/10.** Edge-to-edge di HP tombol-3 butuh scrim di bar bawah. Dipasang,
tidak dibiarkan tembus.

**Tidak ada tes UI Compose.** Dep `ui-test-junit4` tidak ada dan tidak ditambah. Cek visual
lewat `@Preview`, logika lewat unit test.

**Tidak dikerjakan:** biometrik (dep ada, nol pemakaian), notifikasi, mode luring, banner
promo (tidak ada endpoint), unggah avatar (endpoint ada, tidak diminta).

## 8. Kriteria selesai

### Per tahap
1. `./gradlew assembleDebug` lulus, nol warning baru
2. `@Preview` light dan dark ada untuk tiap layar/komponen yang disentuh, render tanpa error
3. Unit test tahap itu lulus (kalau ada logika)
4. Layar lama tetap jalan — tidak ada tahap meninggalkan app rusak

### Keseluruhan — jawaban atas tiga keluhan, bisa dicek satu-satu

Menu cocok domain:
- Pinjaman terjangkau dari Beranda, bukan tersembunyi di balik kartu saldo
- `belanja/angsuran` + `belanja/retur` punya UI (nol → ada)
- Nama modul backend tidak lagi kelihatan user
- Papan informasi punya pintu; lampiran berita bisa dibuka
- Field profil terpakai: sukarela, hari raya, divisi, bagian, status anggota
- Status pengajuan punya satu rumah

Tidak lagi mainstream/AI slop:
- Hero hijau pekat + emas segel, satu per layar (bukan 4 kartu primary identik)
- Plus Jakarta Sans ganti Roboto; nominal tabular
- Daftar jadi baris berpemisah
- Jarak 4/8/16/24
- Edge-to-edge, status bar hijau paksa hilang
- Logo esimko dipakai (launcher + login)

Belanja gaya toko online:
- Search menempel, chip kategori, grid 2 kolom, tombol + di kartu, bar keranjang bawah

### Cek mutu (bukan negosiasi)
Kontras ≥ 4.5:1 · target ketuk ≥ 48dp · teks sistem 200% tidak memotong · tiap tombol ikon
punya `contentDescription` · status warna **dan** ikon · tiap layar punya keadaan
muat/kosong/gagal yang tidak bertumpuk · nol perubahan backend

### Cara menilai
Buka file layar di Android Studio, panel Split, lihat preview light+dark tanpa build. Uji di
HP: `./gradlew assembleDebug`.
