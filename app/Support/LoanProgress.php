<?php

namespace App\Support;

class LoanProgress
{
    public static function summarize($loan, $installments)
    {
        $paid = 0;
        $remaining = 0;
        $principal = 0;
        $interest = 0;
        $next = null;
        $settled = (int) $loan->fid_status === 6;
        foreach ($installments as $item) {
            if ((int) $item->fid_status === 6) {
                $paid++;
            } elseif (!$settled) {
                $remaining++;
                $principal += (float) $item->angsuran_pokok;
                $interest += (float) $item->angsuran_bunga;
                $next = $next === null ? (int) $item->angsuran_ke : min($next, (int) $item->angsuran_ke);
            }
        }
        return [
            'id' => $loan->id,
            'jenis' => $loan->jenis_transaksi,
            'tanggal' => $loan->tanggal,
            'nominal' => abs((float) $loan->nominal),
            'tenor' => (int) $loan->tenor,
            'terbayar' => $paid,
            'tersisa' => $remaining,
            'berikutnya' => $next,
            'sisa_pokok' => $principal,
            'sisa_bunga' => $interest,
            'sisa_total' => $principal + $interest,
            'lunas' => $settled,
        ];
    }
}
