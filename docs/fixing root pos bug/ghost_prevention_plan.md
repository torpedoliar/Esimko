# Deep Analysis & Fix Plan: Prevention Ghost Transaction

## 1. Analisis Root Cause
**Masalah:** 
Di `PenjualanBaruController::update`, logika untuk `fid_metode_pembayaran == 3` (Kredit/Angsuran) menggunakan `updateOrCreate`, tetapi **tidak ada logika else** untuk menghapus angsuran jika user berpindah **DARI** Kredit **KE** Tunai/Lainnya.

```php
// Existing Code (Buggy)
if ($request->input('fid_metode_pembayaran') == 3) {
    AngsuranBelanja::updateOrCreate([...]);
}
// Missing: ELSE delete()
```

**Dampak:**
Jika kasir memilih Kredit (record terbuat), lalu berubah pikiran ke Tunai, record `angsuran_belanja` tertinggal di database sebagai "Ghost Transaction". Ini menyebabkan limit anggota terpotong secara misterius (seperti kasus K 1667).

---

## 2. Impact Analysis pada Fitur Lain

### A. PenjualanController (POS Lama)
**Status: AMAN ✅**
POS Lama menggunakan pendekatan berbeda di fungsi `proses_pembayaran`:
1.  Selalu jalankan `AngsuranBelanja::where(...)->delete()` terlebih dahulu.
2.  Baru create ulang jika metode = 3.
```php
// PenjualanController.php
AngsuranBelanja::where('fid_penjualan',$field->id)->delete(); // Cleanup first
if($pembayaran->fid_metode_pembayaran==3){
    $this->proses_angsuran($field->id,$request); // Re-create if needed
}
```

### B. PenjualanBaruController (POS Baru)
**Status: PERLU FIX ⚠️**
Fungsi `update` diakses via AJAX dari blade `index.blade.php` saat:
1.  Ganti metode pembayaran (dropdown change)
2.  Ganti anggota
3.  Ubah input bayar (keyup)

**Safety Check:**
Fix hanya akan berjalan jika `$request->has('fid_metode_pembayaran')`.
- Jika update field lain (contoh: keterangan, tanggal) tanpa kirim metode pembayaran → **AMAN** (tidak akan delete angsuran).
- Jika update metode pembayaran → **AMAN & CORRECT** (akan delete angsuran non-kredit).

---

## 3. Implementation Plan

### Modify: `app/Http/Controllers/PenjualanBaruController.php`

Tambahkan logic `else` pada blok pengecekan metode pembayaran:

```php
        if ($request->has('fid_metode_pembayaran')) {
            if ($request->input('fid_metode_pembayaran') == 3) {
                AngsuranBelanja::updateOrCreate([
                    'fid_penjualan' => $id,
                    'angsuran_ke' => 1,
                    'fid_status' => 3
                ], [
                    'total_angsuran' => unformat_number($request->input('total_pembayaran'))
                ]);
            } else {
                // FIX: Hapus angsuran jika metode berubah ke Non-Kredit
                AngsuranBelanja::where('fid_penjualan', $id)->delete();
            }
        }
```

### Verification
1.  User K 1667 (atau sembarang user test).
2.  Buka POS Baru, pilih item.
3.  Pilih Metode = **Kredit** → Cek DB: `angsuran_belanja` harus ada.
4.  Ubah Metode = **Tunai** → Cek DB: `angsuran_belanja` harus **HILANG**.
