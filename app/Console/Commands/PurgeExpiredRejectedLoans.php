<?php

namespace App\Console\Commands;

use App\Transaksi;
use App\Support\WebLoanDocuments;
use Illuminate\Console\Command;

class PurgeExpiredRejectedLoans extends Command
{
    protected $signature = 'loan:purge-expired-rejected';
    protected $description = 'Hapus file dokumen pengajuan yang ditolak melewati masa 7 hari';

    public function handle()
    {
        $n = static::purgeExpired();
        $this->info("Purged {$n} expired rejected loan(s).");
        return 0;
    }

    /** Dijalankan juga secara lazy dari controller; kembalikan jumlah yang dibersihkan. */
    public static function purgeExpired()
    {
        $batas = now()->subDays(7);
        $rows = Transaksi::where('fid_status', 2)
            ->whereNotNull('ditolak_at')
            ->where('ditolak_at', '<', $batas->toDateTimeString())
            ->whereNotNull('draft_pengajuan')
            ->get();

        $n = 0;
        foreach ($rows as $field) {
            $draft = json_decode($field->draft_pengajuan, true);
            if (!is_array($draft)) {
                continue;
            }
            $paths = [];
            if (!empty($draft['slip'])) {
                $paths = is_array($draft['slip']) ? array_values($draft['slip']) : [$draft['slip']];
            }
            foreach ((array) ($draft['dokumen'] ?? []) as $p) {
                if (!empty($p)) $paths[] = $p;
            }
            unset($draft['slip'], $draft['dokumen']);
            $field->draft_pengajuan = json_encode($draft);
            $field->save();
            foreach (array_unique(array_filter($paths)) as $p) {
                WebLoanDocuments::deleteIfUnreferenced($p);
            }
            $n++;
        }
        return $n;
    }
}