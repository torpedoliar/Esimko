# DOKUMENTASI PERBAIKAN: POS Stock Validation
**Tanggal:** 15 Februari 2026
**File:** `app/Http/Controllers/PenjualanController.php`

---

## Problem

Kasir bisa menjual barang walaupun stok = 0, menyebabkan **stok menjadi minus**.
Contoh: Aqua botol stok 1, dijual 2 → stok jadi -1 → restock 35 → stok tampil 34.

---

## Root Cause

Ditemukan **2 bug** di `PenjualanController.php` (POS Lama):

### Bug 1: Silent Fail di `proses_items()` (line 407)
```php
// SEBELUM: Jika stok exceeded, TIDAK ADA error message
if($field->jumlah <= $barang['sisa']){
    $field->save();
}
// ← Tidak ada ELSE → kasir tidak dapat feedback apapun
```

### Bug 2: Zero Validation di `proses_all_items()` (line 481-486)
```php
// SEBELUM: TIDAK ADA validasi stok sama sekali
$field->jumlah = $request->jumlah[$value->id];
$field->save(); // Langsung save, jumlah berapapun
```

---

## Fix yang Diterapkan

### Fix 1: `proses_items()` — Error message saat stok habis/tidak cukup

```php
// SESUDAH: Cek stok habis di awal
if($barang['sisa'] <= 0){
    return Redirect::back()
        ->with('message','Stok ' . $produk->nama_produk . ' habis! (Sisa: 0)')
        ->with('message_type','error');
}

// ... (logic tambah item tetap sama) ...

// SESUDAH: Error message jika qty > stok
if($field->jumlah <= $barang['sisa']){
    $field->save();
} else {
    return Redirect::back()
        ->with('message','Stok ' . $produk->nama_produk . ' tidak cukup! (Sisa: ' . intval($barang['sisa']) . ')')
        ->with('message_type','error');
}
```

### Fix 2: `proses_all_items()` — Validasi stok sebelum save

```php
// SESUDAH: Validasi stok sebelum save
$stok = GlobalHelper::stok_barang($field->fid_produk, $id);
if($field->jumlah > $stok['sisa']){
    return Redirect::back()
        ->with('message','Stok ' . $value->kode . ' tidak cukup! Sisa: ' . intval($stok['sisa']))
        ->with('message_type','error');
}

$field->total = str_replace('.','', $harga * $field->jumlah);
$field->save();
```

---

## Impact Analysis

| Fitur | Terdampak? | Keterangan |
|-------|-----------|------------|
| POS Lama (pos/penjualan) | ✅ Ya | **Target fix ini** |
| POS Baru (pos/penjualan_baru) | ❌ Tidak | Sudah ada validasi stok |
| Konsinyasi | ❌ Tidak | Punya `proses_items()` sendiri |
| Pembelian | ❌ Tidak | Controller terpisah |
| Retur Penjualan | ❌ Tidak | Controller terpisah |
| Laporan Stock | ❌ Tidak | Read-only |
| Customer Display | ❌ Tidak | Display only |

---

## Backup Files

| File | Keterangan |
|------|-----------|
| `backups/pos_stock_fix_feb15/PenjualanController_original.php` | Versi asli sebelum fix |

---

## Verifikasi

- [x] PHP lint: No syntax errors
- [x] Deploy ke server 49.50.9.81
- [ ] Test manual: Scan barang stok 0 → harus muncul error
- [ ] Test manual: Scan barang stok 1 dua kali → error pada scan kedua
- [ ] Test manual: Edit qty manual melebihi stok → error saat BAYAR
