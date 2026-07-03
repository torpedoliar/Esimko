@echo off
cd /d C:\IIS\Esimko
php artisan tinker --execute="$p = App\Penjualan::where('fid_anggota','K 1454')->where('fid_metode_pembayaran',3)->whereIn('fid_status',[2,4])->whereHas('angsuran_belanja', function($q){ $q->where('fid_status',3); })->get(['id','angsuran','total_pembayaran']); echo 'COUNT: ' . $p->count() . PHP_EOL; foreach($p as $r){ echo 'ID:' . $r->id . ' ang:' . $r->angsuran . ' total:' . $r->total_pembayaran . PHP_EOL; } echo 'SUM_ANG: ' . $p->sum('angsuran') . PHP_EOL; echo 'SUM_TOTAL: ' . $p->sum('total_pembayaran') . PHP_EOL;"
