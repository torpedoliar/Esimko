<?php
namespace Tests\Unit;

use App\Support\LoanProgress;
use PHPUnit\Framework\TestCase;

class LoanProgressTest extends TestCase
{
    public function test_unpaid_totals_use_stored_schedule_not_current_interest()
    {
        $loan = (object) ['id'=>1,'fid_status'=>4,'jenis_transaksi'=>'Pinjaman','tanggal'=>'2026-01-01','nominal'=>-30000000,'tenor'=>20];
        $rows = [
            (object) ['fid_status'=>6,'angsuran_ke'=>1,'angsuran_pokok'=>1500000,'angsuran_bunga'=>300000],
            (object) ['fid_status'=>5,'angsuran_ke'=>2,'angsuran_pokok'=>1500000,'angsuran_bunga'=>300000],
            (object) ['fid_status'=>3,'angsuran_ke'=>3,'angsuran_pokok'=>1500000,'angsuran_bunga'=>300000],
        ];
        $r = LoanProgress::summarize($loan,$rows);
        $this->assertSame(1,$r['terbayar']);
        $this->assertSame(2,$r['berikutnya']);
        $this->assertEquals(3000000,$r['sisa_pokok']);
        $this->assertEquals(3600000,$r['sisa_total']);
        $loan->fid_status=6;
        $settled=LoanProgress::summarize($loan,$rows);
        $this->assertEquals(0,$settled['sisa_total']);
        $this->assertNull($settled['berikutnya']);
    }
}
