---
name: eSIMKO Mobile
description: Aplikasi anggota koperasi SIMKO — keuangan jelas, transaksi mandiri, tenang dan terpercaya.
colors:
  primary: "#118334"
  on-primary: "#FFFFFF"
  primary-container: "#B0EAC2"
  on-primary-container: "#0A3B1E"
  secondary: "#3D5E48"
  secondary-container: "#D8EADB"
  tertiary: "#7A5E1F"
  tertiary-container: "#FFE4A5"
  error: "#B3261E"
  background: "#F6FBF4"
  surface: "#F6FBF4"
  surface-variant: "#DCE7DE"
  on-surface: "#17231A"
  on-surface-variant: "#3F4A42"
  outline: "#6F7A71"
  dark-primary: "#80CD98"
  dark-background: "#10150F"
  dark-surface: "#10150F"
  dark-on-surface: "#DCE9DC"
typography:
  display:
    fontFamily: "Roboto, system-ui"
    fontWeight: 700
    fontSize: "57sp"
    lineHeight: "64sp"
    letterSpacing: "-0.25sp"
  headline:
    fontFamily: "Roboto, system-ui"
    fontWeight: 700
    fontSize: "32sp"
    lineHeight: "40sp"
  title:
    fontFamily: "Roboto, system-ui"
    fontWeight: 600
    fontSize: "16sp"
    lineHeight: "24sp"
    letterSpacing: "0.15sp"
  body:
    fontFamily: "Roboto, system-ui"
    fontWeight: 400
    fontSize: "16sp"
    lineHeight: "24sp"
    letterSpacing: "0.5sp"
  label:
    fontFamily: "Roboto, system-ui"
    fontWeight: 500
    fontSize: "14sp"
    lineHeight: "20sp"
    letterSpacing: "0.1sp"
rounded:
  extra-small: "6dp"
  small: "10dp"
  medium: "14dp"
  large: "20dp"
  extra-large: "28dp"
spacing:
  xs: "4dp"
  sm: "8dp"
  md: "16dp"
  lg: "24dp"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.on-primary}"
    rounded: "{rounded.medium}"
    padding: "0 24dp"
    height: "52dp"
  button-tonal:
    backgroundColor: "{colors.secondary-container}"
    textColor: "{colors.on-secondary-container}"
    rounded: "{rounded.medium}"
    padding: "0 24dp"
    height: "48dp"
  card:
    backgroundColor: "{colors.surface}"
    rounded: "{rounded.large}"
    padding: "16dp 20dp"
  input:
    backgroundColor: "{colors.surface}"
    rounded: "{rounded.small}"
    padding: "0 16dp"
---

# Design System: eSIMKO Mobile

## 1. Overview

**Creative North Star: "Lembar Keuangan yang Tenang"**

Sistem ini adalah buku kas koperasi yang terbuka: setiap angka jelas, setiap tindakan bisa dipegang, dan tidak ada yang berteriak. Hijau koperasi membawa ketenangan; putih-hijau membawa keterbacaan; satu accent warna dipakai hanya untuk keputusan (tombol utama, status aktif). Anggota dari semua usia harus merasa aplikasi ini tenang seperti melihat saldo mereka di kasir — tapi bisa dipegang sendiri kapan saja.

Densitas: longgar dan terukur. Ruang putih adalah bagian dari desain, bukan sisa. Teks primer minimal 4.5:1, target sentuh minimal 48dp. Sistem ini secara eksplisit menolak: fintech komersial yang ribut (notifikasi, warna mencolok, promosi agresif) dan tampilan admin kantoran (datar, tabel penuh, rasa tool internal).

**Key Characteristics:**
- Satu brand color hijau carry permukaan utama (drenched pada hero/saldo card), sisanya tenang.
- Elevasi hybrid: tonal layering sebagai struktur, shadow tipis hanya pada kartu elevated.
- Komponen tegas & terkendali: sudut medium (14–20dp), tombol tinggi 52dp, tidak ada dekorasi.
- Konsistensi absolut antar layar: satu pola tombol, satu pola kartu, satu pola input.
- Angka keuangan selalu eksplisit (format Rupiah, status berwarna, tidak ambigu).

## 2. Colors

Palet turunan dari satu seed hijau koperasi (`#118334`), diambil dari logo eSIMKO. Semua warna menari di sekitar hue hijau; tidak ada biru, tidak ada ungu.

### Primary
- **Hijau Koperasi** (`#118334`): brand color. Dipakai untuk tombol utama, header drenched (login, greeting, saldo card), status bar, elemen aktif navigasi. Carry 30–60% permukaan pada layar hero.
- **On Primary** (`#FFFFFF`): teks di atas hijau.
- **Container Hijau** (`#B0EAC2`): container tonal, chip status sukses, background subtle aksen.
- **On Container** (`#0A3B1E`): teks di atas container.

### Secondary
- **Hijau Tenang** (`#3D5E48`): hijau desaturated untuk elemen sekunder (tombol tonal, ikon non-aktif).
- **Container Sekunder** (`#D8EADB`): tombol tonal, kartu informasi ringan.

### Tertiary
- **Emas Koperasi** (`#7A5E1F`): aksen hangat (badge, highlight jarang). Pemakaian ≤5% permukaan.

### Neutral
- **Hijau Putih** (`#F6FBF4`): background + surface. Tinted ke arah hue brand — bukan cream, bukan abu-abu netral.
- **Tinta** (`#17231A`): teks utama. Hijau-tinted, bukan hitam murni.
- **Surface Variant** (`#DCE7DE`): kartu secondary, input background.
- **On Surface Variant** (`#3F4A42`): teks sekunder, label.
- **Outline** (`#6F7A71`): border input, divider.

### Dark
Turunan gelap hijau: background `#10150F`, primary `#80CD98`, surface `#10150F`, teks `#DCE9DC`. Dark theme adalah warga kelas satu — diuji, bukan invert cepat.

### Named Rules
**The Quiet Ledger Rule.** Teks sekunder tidak pernah lebih terang dari `#3F4A42` di atas background. Keterbacaan adalah fitur utama; "abu-abu elegan" adalah kegagalan.

**The Rarity Rule.** Accent gold dipakai ≤5% layar. Kelangkaannya yang membuatnya berarti.

## 3. Typography

**Display Font:** Roboto (system) — satu keluarga, semua role.
**Body Font:** Roboto (system).
**Label/Mono Font:** — tidak ada; angka keuangan memakai Roboto dengan weight medium/bold, bukan tabular khusus.

**Character:** Satu keluarga sans yang tenang dan netral. Produk keuangan tidak butuh display font; hierarki dicapai lewat weight (Medium/SemiBold/Bold) dan skala M3, bukan lewat keluarga kedua.

### Hierarchy
- **Display** (Bold, 57sp, 64sp): hanya brand mark (login header "eSIMKO").
- **Headline** (Bold 32sp / SemiBold 24–28sp): judul layar, nama anggota, angka saldo besar.
- **Title** (SemiBold 22sp / Medium 16sp): judul kartu, item list.
- **Body** (Normal 16sp / 14sp): konten, deskripsi. Panjang baris dibatasi 65–75ch.
- **Label** (Medium 14sp / 12sp / 11sp): tombol, chip, caption, label field.

### Named Rules
**The Weight Rule.** Judul dibedakan dengan weight, bukan ukuran ekstrem. Jika dua heading bersebelahan terlihat sama, salah satu naik weight — bukan ukuran.

## 4. Elevation

Hybrid: tonal layering adalah struktur utama (Material 3 surface-container levels), shadow tipis hanya pada kartu yang perlu menonjol (saldo card, dialog). Kedalaman dibaca dari nuansa warna, bukan dari bayangan tebal.

### Shadow Vocabulary
- **Elevated card** (shadow 4dp, ambient tipis): kartu utama (saldo, greeting) — hanya yang perlu "diangkat" dari halaman.
- **Dialog/Sheet** (shadow 8dp): modal, bottom sheet.
- Semua kartu lain: flat tonal, tanpa shadow.

### Named Rules
**The Flat-By-Default Rule.** Kartu biasa datar. Shadow muncul hanya sebagai respons state atau untuk elemen yang benar-benar perlu diangkat. Jika tampak seperti app 2014, shadow terlalu gelap dan blur terlalu besar.

## 5. Components

### Buttons
- **Shape:** Sudut medium (14dp), tinggi minimal 48dp.
- **Primary:** hijau koperasi (`#118334`), teks putih, tinggi 52dp, padding horizontal 24dp. Satu per layar untuk tindakan utama.
- **Tonal:** container sekunder (`#D8EADB`), teks on-container, tinggi 48dp. Untuk aksi sekunder (Setoran/Penarikan).
- **Outlined/Text:** border outline (`#6F7A71`) atau teks saja. Untuk aksi tersier.
- **Hover/Focus:** tonal elevation naik satu level, ripple standar M3. Disabled: on-surface 12% alpha.

### Cards / Containers
- **Corner Style:** Sudut besar (20dp) untuk kartu utama, medium (14dp) untuk kartu item.
- **Background:** surface (`#F6FBF4`) untuk item; drenched hijau (`#118334`) untuk hero/saldo; container sekunder untuk info.
- **Shadow Strategy:** flat default; elevated shadow 4dp hanya kartu hero (lihat Elevation).
- **Border:** tidak ada border; pemisahan lewat tonal.
- **Internal Padding:** 16–20dp.

### Inputs / Fields
- **Style:** outlined, sudut kecil (10dp), background surface, border outline (`#6F7A71`).
- **Focus:** border 2px primary (`#118334`), label naik — perilaku M3 standar.
- **Error:** border error (`#B3261E`) + pesan error 4.5:1.
- **Disabled:** on-surface 38% alpha.

### Navigation
- **Navigation Bar (bottom):** 5 destinasi M3, ikon + label, active = primary hijau, inaktif = on-surface-variant.
- **Top App Bar:** surface, judul title-large. Kembali selalu via ikon + gesture Back sistem.

### Signature: Saldo Card
Kartu drenched hijau (`#118334`) di dashboard & tab simpanan: label on-primary 85% alpha, angka saldo headline-medium bold on-primary penuh, breakdown sekunder on-primary 85%. Ini "lembar keuangan" — angka terbesar, kontras tertinggi di layar.

## 6. Do's and Don'ts

### Do:
- **Do** pakai hijau koperasi (`#118334`) sebagai satu-satunya warna permukaan utama; semua aksen lain turunan hue yang sama.
- **Do** tampilkan angka keuangan eksplisit dengan format Rupiah, bold, kontras ≥4.5:1 — saldo, angsuran, status selalu terlihat tanpa tap.
- **Do** jaga target sentuh ≥48dp dan jarak antar elemen ≥8dp.
- **Do** gunakan shadow hanya pada kartu hero (4dp) dan dialog (8dp); sisanya flat tonal.
- **Do** konsisten: tombol primary satu per layar, pola kartu sama di semua tab.
- **Do** hormati Reduce Motion sistem dan sediakan dark theme penuh.

### Don't:
- **Don't** meniru aplikasi fintech komersial — notifikasi agresif, warna mencolok, promosi. Koperasi bukan tempat jualan.
- **Don't** meniru tampilan admin kantoran — tabel penuh, dense, rasa tool internal. Aplikasi ini untuk anggota awam.
- **Don't** pakai warna lain di luar palet hijau (tidak ada biru, ungu, oranye mencolok) kecuali error merah dan gold accent ≤5%.
- **Don't** pakai abu-abu terang untuk teks sekunder — tetap ≥4.5:1 (minimal `#3F4A42`).
- **Don't** double-kartu (nested cards) — satu kartu satu konten.
- **Don't** tambahkan shadow tebal/glow — jika tampak seperti 2014 app, kurangi.
