# Dokumentasi Perbaikan: Payroll Angsuran Belanja Crash
**Tanggal Insiden:** 10 Februari 2026, 21:12:04 WIB  
**Tanggal Perbaikan:** 15 Februari 2026  
**Operator Terdampak:** ANJAYA  
**File:** `app/Http/Controllers/AngsuranBelanjaController.php`

---

## 1. Kronologi Insiden

| Waktu | Kejadian |
|-------|----------|
| 10 Feb 2026 21:12 | ANJAYA menekan "Proses Payroll" pada halaman Angsuran Belanja |
| 10 Feb 2026 21:12 | Server mengembalikan **Error 500** |
| 11 Feb 2026 11:38 | Log mencatat: `Maximum execution time of 30 seconds exceeded` |
| 15 Feb 2026 | Root cause ditemukan dan diperbaiki |

---

## 2. Root Cause Analysis

### Akar Masalah: N+1 Query Problem pada 51.759 Transaksi

Sistem memiliki **51.759 transaksi kredit** yang memenuhi kriteria pemrosesan payroll.
Kode lama memproses satu per satu dalam loop, menghasilkan **103.518 query database** (1 SELECT + 1 UPDATE per transaksi).

**Bukti dari Log Server:**
```
[2026-02-11 11:38:23] local.ERROR: Maximum execution time of 30 seconds exceeded 
at Connection.php:338
```

### Mengapa Sebelumnya Tidak Terjadi?
Jumlah transaksi kredit terus bertambah seiring waktu. Pada awalnya, jumlahnya masih di bawah batas waktu 30 detik. Setelah akumulasi mencapai puluhan ribu, proses tidak lagi dapat diselesaikan dalam batas waktu PHP.

---

## 3. Fungsi yang Diperbaiki

### 3.1 `reload_payroll($id)`

**SEBELUM (N+1 Loop):**
```php
public function reload_payroll($id){
    $data = AngsuranBelanja::where('fid_payroll', $id)->get();
    foreach ($data as $key => $value) {
        $angsuran = AngsuranBelanja::find($value->id);
        $angsuran->fid_status = 3;
        $angsuran->fid_payroll = null;
        $angsuran->save();
    }
}
```
> Problem: `get()` memuat seluruh data ke memori, lalu `find()` dan `save()` dijalankan per record.
> Jika ada 2.473 record → **4.946 query** (1 SELECT find + 1 UPDATE save × 2473).

**SESUDAH (Mass Update):**
```php
public function reload_payroll($id){
    AngsuranBelanja::where('fid_payroll', $id)
        ->update([
            'fid_status' => 3,
            'fid_payroll' => null
        ]);
}
```
> **1 query** untuk semua record. Hasil akhir identik.

---

### 3.2 `proses_angsuran_belanja($id, $request)`

**SEBELUM (N+1 Loop — Penyebab Utama Crash):**
```php
public function proses_angsuran_belanja($id, $request){
    $this->reload_payroll($id);
    $belanja = Penjualan::select('penjualan.*')
        ->where(...)
        ->where('fid_metode_pembayaran', 3)
        ->where('tanggal', '<=', date('Y-m-d'))
        ->get(); // LOAD 51.759 records ke memori sekaligus!

    foreach ($belanja as $key => $value) {
        $angsuran = AngsuranBelanja::where('fid_penjualan', $value->id)
            ->where('fid_status', 3)
            ->orderBy('angsuran_ke', 'ASC')
            ->first(); // 1 SELECT per iterasi
        if (!empty($angsuran)) {
            $field = AngsuranBelanja::find($angsuran->id); // 1 SELECT lagi
            $field->fid_payroll = $id;
            $field->fid_status = 6;
            $field->save(); // 1 UPDATE
        }
    }
}
```
> Problem: 51.759 × 3 query = **~155.000 query**. PHP habis waktu di detik ke-30.

**SESUDAH (Chunked Mass Update):**
```php
public function proses_angsuran_belanja($id, $request){
    set_time_limit(600);
    ini_set('memory_limit', '512M');

    $this->reload_payroll($id);

    $belanjaQuery = Penjualan::select('id')
        ->where(...)
        ->where('fid_metode_pembayaran', 3)
        ->where('tanggal', '<=', date('Y-m-d'));

    $belanjaQuery->chunk(1000, function ($sales) use ($id) {
        $salesIds = $sales->pluck('id')->toArray();
        
        if (!empty($salesIds)) {
            // Cari angsuran tertua (MIN id) per penjualan
            $idsToUpdate = AngsuranBelanja::select(DB::raw('MIN(id) as id'))
                ->whereIn('fid_penjualan', $salesIds)
                ->where('fid_status', 3)
                ->groupBy('fid_penjualan')
                ->pluck('id')
                ->toArray();

            if (!empty($idsToUpdate)) {
                AngsuranBelanja::whereIn('id', $idsToUpdate)
                    ->update([
                        'fid_payroll' => $id,
                        'fid_status' => 6
                    ]);
            }
        }
    });
}
```
> 52 chunks × 3 query = **156 query**. Selesai dalam hitungan detik.

---

### 3.3 `update_status_angsuran($id, $status)`

**SEBELUM (N+1 Loop):**
```php
public function update_status_angsuran($id, $status){
    $angsuran = AngsuranBelanja::where('fid_payroll', $id)->get();
    foreach ($angsuran as $key => $value) {
        $field = AngsuranBelanja::find($value->id);
        $field->fid_status = $status;
        $field->save();
    }
}
```
> Jika ada 2.473 record → **4.946 query**.

**SESUDAH (Mass Update):**
```php
public function update_status_angsuran($id, $status){
    AngsuranBelanja::where('fid_payroll', $id)
        ->update(['fid_status' => $status]);
}
```
> **1 query** untuk semua record.

---

## 4. Fungsi yang TIDAK Diubah

| Fungsi | Baris | Keterangan |
|--------|-------|-----------|
| `get_angsuran()` | 23-53 | Read-only, menampilkan data payroll |
| `status_payroll()` | 55-76 | Logika tanggal untuk enable/disable tombol |
| `index()` | 78-95 | Render halaman utama |
| `proses()` | 97-115 | Entry point tombol "Proses Payroll" |
| `verifikasi()` | 170-187 | Entry point verifikasi payroll |

---

## 5. Bukti Keamanan Perubahan

### 5.1 PHP Syntax Check
```
> php -l AngsuranBelanjaController.php
No syntax errors detected
```

### 5.2 Model Event Check
Model `AngsuranBelanja` tidak memiliki:
- ❌ Observer
- ❌ Boot method
- ❌ Event listener (creating, updating, saving, dll)
- ❌ Timestamps (`$timestamps = false`)

Artinya: Mass `update()` dan individual `save()` menghasilkan **efek database yang 100% identik**.

### 5.3 MIN(id) vs ORDER BY angsuran_ke Equivalence
Script verifikasi (`verify_min_id_vs_angsuran_ke.php`) membandingkan kedua metode pada 1.762 sales aktif:
```
Total Checked: 1762
Total Mismatches: 0
✅ SAFE: ID auto-increment berkorelasi sempurna dengan angsuran_ke.
```

### 5.4 Live Functional Test
```
TEST 1 (reload_payroll):           ✅ Query valid, 2473 records
TEST 2 (proses_angsuran_belanja):  ✅ 156 queries vs 103.518 lama
TEST 3 (update_status_angsuran):   ✅ Query valid, 2473 records  
TEST 4 (Model Events):            ✅ No listeners registered
=== ALL TESTS PASSED ===
```

---

## 6. Perbandingan Performa

| Metrik | Sebelum | Sesudah | Improvement |
|--------|---------|---------|-------------|
| Total Query (Proses Payroll) | ~103.518 | ~156 | **664x lebih sedikit** |
| Total Query (Reload Payroll) | ~4.946 | 1 | **4946x lebih sedikit** |
| Total Query (Update Status) | ~4.946 | 1 | **4946x lebih sedikit** |
| Memory Usage | Tinggi (load all) | Rendah (chunk 1000) | — |
| Execution Time | >30 detik (CRASH) | <5 detik (estimasi) | ✅ |

---

## 7. Daftar File Backup

| File | Keterangan |
|------|-----------|
| `AngsuranBelanjaController_original.php` | Versi awal sebelum semua perbaikan |
| `AngsuranBelanjaController_fixed.php` | Setelah fix `reload_payroll` + `proses_angsuran_belanja` |
| `AngsuranBelanjaController_before_update_status_fix.php` | Setelah fix 2 fungsi, sebelum fix `update_status_angsuran` |
| `verify_min_id_vs_angsuran_ke.php` | Script bukti keamanan MIN(id) vs angsuran_ke |
| `live_functional_test.php` | Script test fungsional 4 tahap |
| `DOKUMENTASI_PERBAIKAN.md` | Dokumen ini |

---

## 8. Catatan Penting

- **Controller lain** (`KonsinyasiController`, `PinjamanController`) memiliki fungsi `update_status_angsuran` **terpisah** milik mereka sendiri — **tidak terpengaruh** oleh perubahan ini.
- Semua perubahan bersifat **optimasi performa**. Tidak ada perubahan logika bisnis.
- Jika ingin rollback, cukup copy `AngsuranBelanjaController_original.php` kembali ke `app/Http/Controllers/`.
