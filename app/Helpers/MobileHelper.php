<?php

namespace App\Helpers;

use App\Transaksi;
use App\Angsuran;

class MobileHelper
{
    /**
     * Safe wrapper for angsuran_pinjaman to prevent null pointer exceptions
     * when a transaction has no installments.
     */
    public static function angsuranPinjamanSafe($anggota, $jenis)
    {
        $query = Transaksi::where('fid_anggota', $anggota)->where('fid_status', 4);
        if ($jenis == 'all') {
            $pinjaman = $query->whereIn('transaksi.fid_jenis_transaksi', [9, 10, 11])->get();
        } else {
            $pinjaman = $query->where('transaksi.fid_jenis_transaksi', $jenis)->get();
        }
        $total_angsuran = 0;
        foreach ($pinjaman as $value) {
            $list_angsuran = Angsuran::select('angsuran.*', 'status_angsuran.status_angsuran', 'status_angsuran.color')
                ->join('status_angsuran', 'status_angsuran.id', '=', 'angsuran.fid_status')
                ->where('angsuran.fid_transaksi', $value->id)
                ->first();
            
            // NULL SAFE CHECK
            if (!empty($list_angsuran)) {
                $angsuran = $list_angsuran->angsuran_bunga + $list_angsuran->angsuran_pokok;
                $total_angsuran = $total_angsuran + $angsuran;
            }
        }
        return $total_angsuran;
    }

    /**
     * Definisi resmi grouping saldo tabungan untuk Mobile API.
     * Tidak terpengaruh perubahan pada GlobalHelper web.
     */
    public static function saldoTabungan($anggota, $jenis)
    {
        $query = Transaksi::where('fid_anggota', $anggota)->where('fid_status', 4);
        
        if ($jenis === 'Total Simpanan') {
            $query->whereIn('fid_jenis_transaksi', [1, 2, 3, 4, 5, 6, 7, 8]);
        } elseif ($jenis === 'Simpanan Sukarela') {
            $query->whereIn('fid_jenis_transaksi', [3, 5, 6]);
        } elseif ($jenis === 'Simpanan Hari Raya') {
            $query->whereIn('fid_jenis_transaksi', [4, 7]);
        } elseif ($jenis !== 'all') {
            $query->where('fid_jenis_transaksi', $jenis);
        }
        
        return intval($query->sum('nominal'));
    }

    /**
     * Safe wrapper for stok_barang to ensure all keys are present,
     * specifically `retur_penjualan` which is missing in the fallback array.
     */
    public static function stokBarang($id, $penjualan = 'all')
    {
        $stok = \App\Helpers\GlobalHelper::stok_barang($id, $penjualan);
        
        return [
            'stok_awal' => $stok['stok_awal'] ?? 0,
            'pembelian' => $stok['pembelian'] ?? 0,
            'retur' => $stok['retur'] ?? 0,
            'retur_penjualan' => $stok['retur_penjualan'] ?? 0,
            'terjual' => $stok['terjual'] ?? 0,
            'sisa' => $stok['sisa'] ?? 0,
            'penyesuaian' => $stok['penyesuaian'] ?? 0,
        ];
    }
}
