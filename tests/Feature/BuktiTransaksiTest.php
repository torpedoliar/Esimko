<?php

namespace Tests\Feature;

use App\Support\BuktiTransaksi;
use App\Http\Controllers\BuktiTransaksiController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BuktiTransaksiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'app.key' => 'base64:' . base64_encode(str_repeat('x', 32)), 'app.url' => 'https://esimko.test']);
        DB::purge('sqlite');
        Schema::create('anggota', function (Blueprint $t) { $t->increments('id'); $t->string('no_anggota'); $t->string('nama_lengkap'); });
        Schema::create('jenis_transaksi', function (Blueprint $t) { $t->increments('id'); $t->string('jenis_transaksi'); });
        Schema::create('transaksi', function (Blueprint $t) {
            $t->increments('id'); $t->string('fid_anggota'); $t->integer('fid_status'); $t->integer('fid_jenis_transaksi');
            $t->decimal('nominal', 18, 2); $t->string('tanggal'); $t->integer('fid_angsuran')->nullable(); $t->integer('tenor')->default(0); $t->integer('fid_metode_transaksi')->nullable();
            $t->string('created_by')->default('STAFF'); $t->string('keterangan')->nullable(); $t->timestamps();
        });
        Schema::create('penjualan', function (Blueprint $t) {
            $t->increments('id'); $t->string('fid_anggota'); $t->integer('fid_status'); $t->string('jenis_belanja');
            $t->decimal('total_pembayaran', 18, 2); $t->string('tanggal'); $t->integer('fid_metode_pembayaran')->nullable();
            $t->string('no_transaksi'); $t->string('created_by')->default('STAFF'); $t->timestamps();
        });
        Schema::create('produk', function (Blueprint $t) { $t->increments('id'); $t->string('nama_produk'); $t->string('kode'); $t->integer('fid_satuan')->nullable(); });
        Schema::create('satuan_barang', function (Blueprint $t) { $t->increments('id'); $t->string('satuan'); });
        Schema::create('item_penjualan', function (Blueprint $t) { $t->increments('id'); $t->integer('fid_penjualan'); $t->integer('fid_produk')->nullable(); $t->string('nama_barang')->nullable(); $t->integer('jumlah'); $t->decimal('total',18,2); });
        Schema::create('angsuran', function (Blueprint $t) { $t->increments('id'); $t->integer('fid_transaksi'); $t->integer('fid_status'); $t->integer('angsuran_ke'); $t->decimal('angsuran_pokok',18,2); $t->decimal('angsuran_bunga',18,2); $t->timestamps(); });
        Schema::create('rekening_pembayaran', function (Blueprint $t) { $t->increments('id'); $t->string('keterangan'); });
        Schema::create('verifikasi_transaksi', function (Blueprint $t) { $t->increments('id'); $t->integer('fid_transaksi'); $t->string('jenis'); $t->string('created_by')->nullable(); $t->timestamps(); });
        Schema::create('otoritas_user', function (Blueprint $t) { $t->increments('id'); $t->integer('fid_hak_akses'); $t->integer('fid_modul'); $t->string('is_view'); $t->string('is_print'); $t->string('is_all_user'); });
        DB::table('anggota')->insert([['no_anggota'=>'A','nama_lengkap'=>'Anggota Contoh'],['no_anggota'=>'B','nama_lengkap'=>'Bukan Pemilik'],['no_anggota'=>'STAFF','nama_lengkap'=>'Petugas Uji']]);
        foreach ([4=>'Simpanan Hari Raya',9=>'Pinjaman Jangka Panjang',12=>'Angsuran Pokok'] as $id=>$label) DB::table('jenis_transaksi')->insert(['id'=>$id,'jenis_transaksi'=>$label]);
        DB::table('transaksi')->insert([
            ['id'=>1,'fid_anggota'=>'A','fid_status'=>4,'fid_jenis_transaksi'=>4,'nominal'=>250000,'tanggal'=>'2026-11-07','tenor'=>0],
            ['id'=>2,'fid_anggota'=>'A','fid_status'=>3,'fid_jenis_transaksi'=>9,'nominal'=>-12000000,'tanggal'=>'2026-11-07','tenor'=>12],
            ['id'=>3,'fid_anggota'=>'B','fid_status'=>4,'fid_jenis_transaksi'=>4,'nominal'=>100000,'tanggal'=>'2026-11-07','tenor'=>0],
            ['id'=>4,'fid_anggota'=>'A','fid_status'=>2,'fid_jenis_transaksi'=>4,'nominal'=>100000,'tanggal'=>'2026-11-07','tenor'=>0],
        ]);
        DB::table('angsuran')->insert(['id'=>1,'fid_transaksi'=>2,'fid_status'=>6,'angsuran_ke'=>1,'angsuran_pokok'=>1000000,'angsuran_bunga'=>120000]);
        DB::table('penjualan')->insert(['id'=>1,'fid_anggota'=>'A','fid_status'=>2,'jenis_belanja'=>'toko','total_pembayaran'=>50000,'tanggal'=>'2026-11-07','no_transaksi'=>'TEST-01']);
        DB::table('satuan_barang')->insert(['id'=>1,'satuan'=>'pcs']);
        DB::table('produk')->insert(['id'=>1,'nama_produk'=>'Contoh Produk','kode'=>'P-001','fid_satuan'=>1]);
        DB::table('item_penjualan')->insert(['fid_penjualan'=>1,'fid_produk'=>1,'jumlah'=>2,'total'=>50000]);
        Route::get('/receipt-test/{jenis}/{id}', function (Request $r, $jenis, $id) { return (new BuktiTransaksiController)->mobile($r,$jenis,$id); });
        Route::get('/receipt-web/{jenis}/{id}', function (Request $r,$jenis,$id) { return (new BuktiTransaksiController)->web($r,$jenis,$id); });
    }

    public function test_eligibility_is_type_specific_and_never_uses_final_for_rejections()
    {
        foreach ([1,2,3,4,6,7,8,12,13,14] as $type) {
            foreach ([1,2,3,4,5,6] as $status) $this->assertSame($status===4, BuktiTransaksi::eligible('transaksi',(object)['fid_status'=>$status,'fid_jenis_transaksi'=>$type]));
        }
        foreach ([9,10,11,15] as $type) foreach ([1,2,3,4,5,6] as $status) $this->assertSame(in_array($status,[3,4,6],true),BuktiTransaksi::eligible('transaksi',(object)['fid_status'=>$status,'fid_jenis_transaksi'=>$type]));
        $this->assertFalse(BuktiTransaksi::eligible('transaksi',(object)['fid_status'=>4,'fid_jenis_transaksi'=>5]));
        $this->assertFalse(BuktiTransaksi::eligible('penjualan',(object)['fid_status'=>3,'jenis_belanja'=>'online']));
        $this->assertTrue(BuktiTransaksi::eligible('angsuran',(object)['fid_status'=>6]));
    }

    public function test_mobile_owner_can_download_pdf_but_other_owner_and_unsuccessful_cannot()
    {
        $pdf=$this->get('/receipt-test/transaksi/1?no_anggota=A')->assertOk()->assertHeader('Content-Type','application/pdf');
        $this->assertSame('%PDF',substr($pdf->getContent(),0,4));
        $this->assertStringContainsString('no-store',$pdf->headers->get('Cache-Control'));
        $this->get('/receipt-test/transaksi/3?no_anggota=A')->assertStatus(404);
        $this->get('/receipt-test/transaksi/4?no_anggota=A')->assertStatus(404);
        $this->get('/receipt-test/unknown/1?no_anggota=A')->assertStatus(404);
        $this->get('/api/mobile/bukti/transaksi/1')->assertStatus(401);
    }

    public function test_web_member_and_staff_permissions_are_enforced()
    {
        $this->withSession(['useractive'=>(object)['no_anggota'=>'A','hak_akses'=>2]])->get('/receipt-web/transaksi/3')->assertStatus(404);
        $this->withSession(['useractive'=>(object)['no_anggota'=>'STAFF','hak_akses'=>1]])->get('/receipt-web/transaksi/1')->assertStatus(404);
        DB::table('otoritas_user')->insert(['fid_hak_akses'=>1,'fid_modul'=>34,'is_view'=>'Y','is_print'=>'N','is_all_user'=>'Y']);
        $this->get('/receipt-web/transaksi/1')->assertStatus(404);
        DB::table('otoritas_user')->update(['is_print'=>'Y','is_all_user'=>'N']);
        $this->withSession(['useractive'=>(object)['no_anggota'=>'STAFF','hak_akses'=>1]])->get('/receipt-web/transaksi/1')->assertOk();
        // is_all_user/created_by tidak dipakai aplikasi; petugas dengan lihat+cetak boleh mengunduh
        // transaksi anggota mana pun di modulnya.
        DB::table('transaksi')->where('id',1)->update(['created_by'=>'OTHER']);
        $this->withSession(['useractive'=>(object)['no_anggota'=>'STAFF','hak_akses'=>1]])->get('/receipt-web/transaksi/1')->assertOk();
    }

    public function test_item_name_from_product_staff_shown_and_verify_page_has_logo()
    {
        // Belanja: nama barang harus dari tabel produk (nama_produk), bukan kode/nama_barang kosong.
        $data = BuktiTransaksi::load('penjualan', 1);
        $this->assertSame('Contoh Produk', $data['items'][0]['nama']);
        $this->assertSame('pcs', $data['items'][0]['satuan']);
        $this->assertNotSame('P-001', $data['items'][0]['nama']);

        // Petugas: terisi dari created_by walau tidak ada baris verifikasi_transaksi.
        $this->assertSame('Petugas Uji', $data['petugas']);
        $this->assertSame('Petugas Uji', BuktiTransaksi::load('transaksi', 1)['petugas']);

        $data['logo'] = 'data:image/png;base64,';
        $html = view('bukti.transaksi', ['bukti' => $data, 'qr' => 'data:image/png;base64,', 'verifyUrl' => 'x'])->render();
        $this->assertStringContainsString('Contoh Produk', $html);
        $this->assertStringContainsString('pcs', $html);
        $this->assertStringContainsString('Petugas Uji', $html);

        // Halaman cek verifikasi menampilkan logo koperasi.
        $kode = BuktiTransaksi::load('penjualan', 1)['kode'];
        $page = $this->get('/cek-bukti?' . http_build_query(['ref' => 'penjualan:1', 'kode' => $kode]))->assertOk();
        $this->assertStringContainsString(config('koperasi.logo'), $page->getContent());
    }

    public function test_web_print_returns_inline_pdf()
    {
        $this->withSession(['useractive' => (object) ['no_anggota' => 'A', 'hak_akses' => 2]])
            ->get('/receipt-web/transaksi/1?inline=1')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'inline; filename="bukti-transaksi-1.pdf"');
    }

    public function test_verification_masks_personal_data_and_detects_tampering_and_changes()
    {
        $data=BuktiTransaksi::load('transaksi',1);
        $url='/cek-bukti?'.http_build_query(['ref'=>'transaksi:1','kode'=>$data['kode']]);
        $valid=$this->get($url)->assertOk()->assertHeader('X-Robots-Tag','noindex, nofollow, noarchive')->assertHeader('Referrer-Policy','no-referrer');
        $this->assertStringNotContainsString('Anggota Contoh',$valid->getContent());
        $this->get('/cek-bukti?ref=transaksi:1&kode='.str_repeat('0',64))->assertStatus(404);
        DB::table('transaksi')->where('id',1)->update(['nominal'=>260000]);
        $this->get($url)->assertStatus(404);
        $this->get('/cek-bukti?ref[]=x&kode[]=x')->assertStatus(404);
    }

    public function test_receipts_render_for_synthetic_examples_and_approved_loan_is_not_disbursement()
    {
        $dir=base_path('EsimkoMobile/app/build/receipt-samples');
        if(!is_dir($dir)) mkdir($dir,0700,true);
        foreach (['transaksi'=>[1,2],'angsuran'=>[1],'penjualan'=>[1]] as $jenis=>$ids) foreach($ids as $id) {
            $data=BuktiTransaksi::load($jenis,$id);
            $pdf=BuktiTransaksi::pdf($data);
            $this->assertSame('%PDF',substr($pdf,0,4));
            file_put_contents($dir.'/'.$jenis.'-'.$id.'.pdf',$pdf);
        }
        $this->assertSame('Bukti Persetujuan Pinjaman',BuktiTransaksi::load('transaksi',2)['judul']);
        $this->assertSame('seratus dua puluh tiga ribu empat ratus lima puluh enam',BuktiTransaksi::terbilang(123456));
    }
}
