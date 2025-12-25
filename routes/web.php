<?php

use Illuminate\Support\Facades\Route;

Route::get('storage/{folder}/{filename}', function ($folder, $filename){

    $path = storage_path('app/' . $folder . '/' . $filename);
    if (!File::exists($path)) {
        abort(404);
    }
    $file = File::get($path);
    $type = File::mimeType($path);
    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);
    return $response;

});

Route::get('zzzz', function () {
    $list_transaksi = \App\Angsuran::where('angsuran_pokok', '<=', 0)->select('fid_transaksi')->groupBy('fid_transaksi')->get()->pluck('fid_transaksi')->toArray();
    $transaksi = \App\Transaksi::whereIn('id', $list_transaksi)->get();
    foreach ($transaksi as $item) {
        $nominal = $item->nominal * -1;
        $sisa = $nominal;
        $tenor = $item->tenor;
        $angsuran = round($nominal / $tenor);
        $bunga = 0.01 * $nominal;
        for ($i = 1; $i <= $tenor; $i++) {
            \App\Angsuran::where('fid_transaksi', $item->id)->where('angsuran_ke', $i)->update([
                'sisa_hutang' => $sisa,
                'angsuran_pokok' => $angsuran,
                'angsuran_bunga' => $bunga
            ]);
            $sisa -= $angsuran;
        }
    }
});

Route::get('/', 'HomeController@landing_page');

Route::get('auth/login','AuthController@login');
Route::post('auth/login/proses','AuthController@proses_login');

// Route::get('auth/register','AuthController@register');
// Route::post('auth/register/proses','AuthController@proses_register');
// Route::get('auth/register/confirm','AuthController@confirm');

Route::group(['middleware' => ['ceklogin']],function(){
    Route::get('dashboard', 'HomeController@dashboard');
    Route::prefix('main')->group(function () {

        Route::get('dashboard', 'HomeController@main_dashboard');
        Route::get('profil', 'ProfilController@index');
        Route::post('profil/edit_profil', 'ProfilController@edit_profil');
        Route::post('profil/edit_kontak', 'ProfilController@edit_kontak');
        Route::post('profil/ubah_password', 'ProfilController@ubah_password');

        Route::get('simpanan', 'TransaksiController@simpanan');
        Route::get('simpanan/detail', 'TransaksiController@simpanan_detail');

        Route::get('pinjaman', 'TransaksiController@pinjaman');
        Route::get('pinjaman/form', 'TransaksiController@pinjaman_form');
        Route::get('pinjaman/detail', 'TransaksiController@pinjaman_detail');
        Route::post('pinjaman/konfirmasi_angsuran', 'TransaksiController@konfirmasi_angsuran');

        Route::get('angsuran', 'TransaksiController@angsuran');
        Route::get('angsuran/detail', 'TransaksiController@angsuran_detail');

        Route::post('transaksi/filter', 'TransaksiController@filter_transaksi');
        Route::post('transaksi/proses', 'TransaksiController@proses_transaksi');
        Route::post('transaksi/proses_pembatalan', 'TransaksiController@proses_pembatalan');
        Route::post('transaksi/upload', 'TransaksiController@upload_bukti_transaksi');

        Route::get('belanja', 'KeranjangController@pilih_produk');
        Route::post('belanja/produk/filter', 'KeranjangController@filter_produk');
        Route::get('belanja/produk/detail', 'KeranjangController@detail_produk');
        Route::post('belanja/produk/proses', 'KeranjangController@add_produk');

        Route::get('belanja/keranjang', 'KeranjangController@index');
        Route::post('belanja/keranjang/hapus', 'KeranjangController@delete');
        Route::post('belanja/keranjang/proses', 'KeranjangController@proses');

        Route::get('belanja/riwayat/{jenis}', 'BelanjaController@index');
        Route::get('belanja/riwayat/{jenis}/detail', 'BelanjaController@detail');
        Route::get('belanja/riwayat/{jenis}/form', 'BelanjaController@form');
        Route::post('belanja/riwayat/{jenis}/proses_pembatalan', 'BelanjaController@proses_pembatalan');

        Route::get('belanja/angsuran', 'BelanjaController@angsuran');
        Route::get('belanja/retur', 'BelanjaController@retur');

        Route::get('berita', 'TransaksiController@berita');
        Route::get('berita/detail', 'TransaksiController@detail_berita');

    });

    Route::get('anggota', 'AnggotaController@index');

    Route::get('anggota/form', 'AnggotaController@form');
    Route::post('anggota/proses', 'AnggotaController@proses');
    Route::get('anggota/detail', 'AnggotaController@detail');

    Route::get('simpanan/payroll', 'PayrollSimpananController@index');
    Route::post('simpanan/payroll/proses', 'PayrollSimpananController@proses');
    Route::post('simpanan/payroll/verifikasi', 'PayrollSimpananController@verifikasi');
    Route::get('simpanan/payroll/hapus', 'PayrollSimpananController@hapus');

    Route::get('simpanan/sukarela', 'SimpananController@index');
    Route::get('simpanan/sukarela/form', 'SimpananController@form');
    Route::get('simpanan/sukarela/detail', 'SimpananController@detail');
    Route::post('simpanan/sukarela/proses', 'SimpananController@proses');
    Route::get('simpanan/sukarela/delete', 'SimpananController@delete');
    Route::post('simpanan/sukarela/verifikasi', 'SimpananController@verifikasi');

    Route::get('simpanan/sukarela/berkala', 'SimpananController@setoran_berkala');
    Route::get('simpanan/sukarela/berkala/form', 'SimpananController@form_setoran_berkala');
    Route::get('simpanan/sukarela/berkala/detail', 'SimpananController@detail_setoran_berkala');
    Route::post('simpanan/sukarela/berkala/proses', 'SimpananController@proses_setoran_berkala');

    Route::get('simpanan/bunga', 'BungaSimpananController@index');
    Route::post('simpanan/bunga/proses', 'BungaSimpananController@proses');

    Route::get('simpanan/buku_simpanan', 'SimpananController@buku_simpanan');
    Route::get('simpanan/buku_simpanan/cover', 'SimpananController@cetak_cover');

    Route::get('penarikan/sukarela', 'PenarikanController@sukarela');
    Route::get('penarikan/sukarela/form', 'PenarikanController@form_penarikan_sukarela');
    Route::get('penarikan/sukarela/detail', 'PenarikanController@detail_penarikan');
    Route::post('penarikan/sukarela/proses', 'PenarikanController@proses_penarikan_sukarela');
    Route::post('penarikan/sukarela/verifikasi', 'PenarikanController@verifikasi_penarikan_sukarela');

    Route::get('penarikan/hari_raya', 'PenarikanController@hari_raya');
    Route::get('penarikan/hari_raya/form', 'PenarikanController@form_hari_raya');
    Route::post('penarikan/hari_raya/proses', 'PenarikanController@proses_hari_raya');
    Route::post('penarikan/hari_raya/verifikasi', 'PenarikanController@verifikasi_hari_raya');

    Route::get('penarikan/penutupan', 'PenarikanController@penutupan');
    Route::get('penarikan/penutupan/form', 'PenarikanController@form_penutupan');
    Route::get('penarikan/penutupan/detail', 'PenarikanController@detail_penutupan');
    Route::post('penarikan/penutupan/proses', 'PenarikanController@proses_penutupan');
    Route::post('penarikan/penutupan/verifikasi', 'PenarikanController@verifikasi_penutupan_simpanan');

    Route::get('pinjaman/pengajuan', 'PinjamanController@index');
    Route::get('pinjaman/pengajuan/form', 'PinjamanController@form');
    Route::get('pinjaman/pengajuan/detail', 'PinjamanController@detail');
    Route::get('pinjaman/pengajuan/delete/{id}', 'PinjamanController@delete');
    Route::get('pinjaman/pengajuan/export', 'PinjamanController@export');
    Route::post('pinjaman/pengajuan/proses', 'PinjamanController@proses');
    Route::post('pinjaman/pengajuan/pelunasan', 'PinjamanController@pelunasan');
    Route::post('pinjaman/pengajuan/verifikasi', 'PinjamanController@verifikasi');

    Route::get('pinjaman/payroll', 'PayrollPinjamanController@index');
    Route::get('pinjaman/payroll/export', 'PayrollPinjamanController@export');
    Route::post('pinjaman/payroll/proses', 'PayrollPinjamanController@proses');
    Route::post('pinjaman/payroll/verifikasi', 'PayrollPinjamanController@verifikasi');
    Route::get('pinjaman/payroll/hapus', 'PayrollPinjamanController@hapus');

    Route::get('monitoring/saldo_simpanan', 'MonitoringController@saldo_simpanan');
    Route::get('monitoring/saldo_simpanan/detail', 'MonitoringController@detail_saldo_simpanan');
    Route::post('monitoring/saldo_simpanan/update', 'MonitoringController@update_saldo_simpanan');
    Route::post('monitoring/saldo_simpanan/delete', 'MonitoringController@delete_saldo_simpanan');
    Route::get('monitoring/saldo_simpanan/export', 'MonitoringController@export_saldo_simpanan');
    Route::get('monitoring/saldo_simpanan/cetak', 'MonitoringController@saldo_simpanan_cetak');
    Route::get('monitoring/sisa_pinjaman', 'MonitoringController@sisa_pinjaman');

    Route::get('manajemen_stok/supplier', 'SupplierController@index');
    Route::post('manajemen_stok/supplier/proses', 'SupplierController@proses');

    Route::get('manajemen_stok/barang', 'ProdukController@index');
    Route::get('manajemen_stok/barang/form', 'ProdukController@form');
    Route::get('manajemen_stok/barang/detail', 'ProdukController@detail');
    Route::post('manajemen_stok/barang/proses', 'ProdukController@proses');

    Route::get('manajemen_stok/pembelian', 'PembelianController@index');
    Route::get('manajemen_stok/pembelian/form', 'PembelianController@form');
    Route::post('manajemen_stok/pembelian/proses', 'PembelianController@proses');
    Route::post('manajemen_stok/pembelian/items/proses', 'PembelianController@proses_items');

    Route::get('manajemen_stok/return', 'ReturnPembelianController@index');
    Route::get('manajemen_stok/return/form', 'ReturnPembelianController@form');
    Route::post('manajemen_stok/return/proses', 'ReturnPembelianController@proses');

    Route::get('manajemen_stok/cetak/label_harga', 'ProdukController@label_harga');
    Route::post('manajemen_stok/cetak/label_harga/filter', 'ProdukController@filter_label_harga');
    Route::post('manajemen_stok/cetak/label_harga/proses', 'ProdukController@proses_label_harga');

    Route::get('manajemen_stok/cetak/barcode_barang', 'ProdukController@barcode_barang');
    Route::post('manajemen_stok/cetak/barcode_barang/filter', 'ProdukController@filter_barcode_barang');
    Route::post('manajemen_stok/cetak/barcode_barang/proses', 'ProdukController@proses_barcode_barang');

    Route::get('pos/penjualan_baru', 'PenjualanBaruController@index');
    Route::post('pos/penjualan_baru/create', 'PenjualanBaruController@create');
    Route::post('pos/penjualan_baru/list_tunda', 'PenjualanBaruController@list_tunda');
    Route::post('pos/penjualan_baru/cari_anggota', 'PenjualanBaruController@cari_anggota');
    Route::post('pos/penjualan_baru/cari_produk', 'PenjualanBaruController@cari_produk');
    Route::post('pos/penjualan_baru/{id}/update', 'PenjualanBaruController@update');
    Route::post('pos/penjualan_baru/{id}/delete', 'PenjualanBaruController@delete');
    Route::post('pos/penjualan_baru/{id}/tunda', 'PenjualanBaruController@tunda');
    Route::get('pos/penjualan_baru/{id}/cetak_struk', 'PenjualanBaruController@cetak_struk');

    Route::post('pos/penjualan_baru/item/create', 'PenjualanBaruItemController@create');
    Route::post('pos/penjualan_baru/item/{id}/search', 'PenjualanBaruItemController@search');
    Route::post('pos/penjualan_baru/item/{id}/update', 'PenjualanBaruItemController@update');
    Route::post('pos/penjualan_baru/item/{id}/delete', 'PenjualanBaruItemController@delete');

    Route::get('manajemen_stok/pembelian_baru', 'PembelianBaruController@index');
    Route::post('manajemen_stok/pembelian_baru/create', 'PembelianBaruController@create');
    Route::get('manajemen_stok/pembelian_baru/selesai', 'PembelianBaruController@selesai');
    Route::post('manajemen_stok/pembelian_baru/cari_produk', 'PembelianBaruController@cari_produk');
    Route::post('manajemen_stok/pembelian_baru/{id}/update', 'PembelianBaruController@update');
    Route::post('manajemen_stok/pembelian_baru/{id}/delete', 'PembelianBaruController@delete');

    Route::post('manajemen_stok/pembelian_baru/item/create', 'PembelianBaruItemController@create');
    Route::get('manajemen_stok/pembelian_baru/item/produk_create', 'PembelianBaruItemController@produk_create');
    Route::post('manajemen_stok/pembelian_baru/item/produk_baru', 'PembelianBaruItemController@produk_baru');
    Route::post('manajemen_stok/pembelian_baru/item/{id}/search', 'PembelianBaruItemController@search');
    Route::post('manajemen_stok/pembelian_baru/item/{id}/update', 'PembelianBaruItemController@update');
    Route::post('manajemen_stok/pembelian_baru/item/{id}/delete', 'PembelianBaruItemController@delete');

    Route::get('pos/penjualan', 'PenjualanController@index');
    Route::get('pos/penjualan/form', 'PenjualanController@form');
    Route::get('pos/penjualan/detail', 'PenjualanController@detail');
    Route::get('pos/penjualan/cetak_struk', 'PenjualanController@cetak_struk');
    Route::get('pos/penjualan/check_limit', 'PenjualanController@check_limit');
    Route::post('pos/penjualan/proses', 'PenjualanController@proses');
    Route::post('pos/penjualan/delete_items', 'PenjualanController@delete_items');
    Route::post('pos/penjualan/proses_pembatalan', 'PenjualanController@proses_pembatalan');

    Route::get('pos/belanja/{jenis}', 'KonsinyasiController@index');
    Route::get('pos/belanja/{jenis}/form', 'KonsinyasiController@form');
    Route::get('pos/belanja/{jenis}/detail', 'KonsinyasiController@detail');
    Route::get('pos/belanja/{jenis}/bayar/{id}/{angsuran_id}', 'KonsinyasiController@bayar');
    Route::post('pos/belanja/{jenis}/items/proses', 'KonsinyasiController@proses_items');
    Route::post('pos/belanja/{jenis}/proses', 'KonsinyasiController@proses');
    Route::post('pos/belanja/{jenis}/verifikasi', 'KonsinyasiController@verifikasi');

    Route::get('pos/return', 'ReturnPenjualanController@index');
    Route::get('pos/return/form', 'ReturnPenjualanController@form');
    Route::post('pos/return/proses', 'ReturnPenjualanController@proses');
    Route::post('pos/return/items/delete', 'ReturnPenjualanController@delete_items');

    Route::get('pos/angsuran', 'AngsuranBelanjaController@index');
    Route::post('pos/angsuran/proses', 'AngsuranBelanjaController@proses');
    Route::post('pos/angsuran/verifikasi', 'AngsuranBelanjaController@verifikasi');

    Route::get('keuangan/bagan_akun', function () {
        return redirect('keuangan/akun');
    });
//    Route::get('keuangan/bagan_akun', 'BaganAkunController@index');
//    Route::post('keuangan/bagan_akun/proses', 'BaganAkunController@proses');

//    Route::get('keuangan/jurnal_umum', 'JurnalUmumController@index');
//    Route::get('keuangan/jurnal_umum/form', 'JurnalUmumController@form');
//    Route::post('keuangan/jurnal_umum/proses', 'JurnalUmumController@proses');
//    Route::post('keuangan/jurnal_umum/detail/proses', 'JurnalUmumController@proses_detail');
//
//    Route::get('keuangan/buku_kas', 'BukuKasController@index');
//    Route::get('keuangan/buku_kas/form', 'BukuKasController@form');
//    Route::post('keuangan/buku_kas/proses', 'BukuKasController@proses');

    Route::get('laporan/payroll', 'CetakPayrollController@index');

    Route::get('master/karyawan', 'MasterController@karyawan');
    Route::get('master/karyawan/form', 'MasterController@form_karyawan');
    Route::post('master/karyawan/proses', 'MasterController@proses_karyawan');

    Route::get('master/pengurus', 'MasterController@pengurus');
    Route::get('master/pengurus/form', 'MasterController@form_pengurus');
    Route::post('master/pengurus/proses', 'MasterController@proses_pengurus');

    Route::get('master/rekening_pembayaran', 'MasterController@rekening_pembayaran');
    Route::post('master/rekening_pembayaran/proses', 'MasterController@proses_rekening_pembayaran');

    Route::get('master/kategori_barang', 'MasterController@kategori_barang');
    Route::post('master/kategori_barang/proses', 'MasterController@proses_kategori_barang');

    Route::get('master/berita', 'MasterController@berita');
    Route::get('master/berita/form', 'MasterController@form_berita');
    Route::post('master/berita/proses', 'MasterController@proses_berita');
    Route::post('master/berita/attachment/proses', 'MasterController@proses_attachment_berita');

    Route::get('master/syarat_ketentuan/{jenis}', 'MasterController@syarat_ketentuan');
    Route::post('master/syarat_ketentuan/{jenis}/proses', 'MasterController@proses_syarat_ketentuan');

    Route::get('pengaturan/otoritas_user', 'PengaturanController@otoritas_user');
    Route::post('pengaturan/otoritas_user/proses', 'PengaturanController@proses_otoritas_user');

    Route::get('pengaturan/metode_pembayaran', 'PengaturanController@metode_pembayaran');
    Route::post('pengaturan/metode_pembayaran/proses', 'PengaturanController@proses_metode_pembayaran');

    Route::post('auth/user_akses/proses','AuthController@user_akses');
    Route::get('auth/logout','AuthController@proses_logout');

    Route::prefix('pos')->group(function () {
        Route::prefix('laporan_penjualan')->group(function () {
            Route::get('', 'LaporanPenjualanController@index');
            Route::post('/search', 'LaporanPenjualanController@search');
            Route::get('/excel', 'LaporanPenjualanController@excel');
            Route::get('/cetak', 'LaporanPenjualanController@cetak');
        });

        Route::prefix('laporan_pembelian')->group(function () {
            Route::get('', 'LaporanPembelianController@index');
            Route::post('/search', 'LaporanPembelianController@search');
            Route::get('/excel', 'LaporanPembelianController@excel');
            Route::get('/cetak', 'LaporanPembelianController@cetak');
        });

        Route::prefix('laporan_retur_penjualan')->group(function () {
            Route::get('', 'LaporanReturPenjualanController@index');
            Route::post('/search', 'LaporanReturPenjualanController@search');
            Route::get('/excel', 'LaporanReturPenjualanController@excel');
            Route::get('/cetak', 'LaporanReturPenjualanController@cetak');
        });

        Route::prefix('laporan_retur_pembelian')->group(function () {
            Route::get('', 'LaporanReturPembelianController@index');
            Route::post('/search', 'LaporanReturPembelianController@search');
            Route::get('/excel', 'LaporanReturPembelianController@excel');
            Route::get('/cetak', 'LaporanReturPembelianController@cetak');
        });

        Route::prefix('laporan_penyesuaian')->group(function () {
            Route::get('', 'LaporanPenyesuaianController@index');
            Route::post('/search', 'LaporanPenyesuaianController@search');
            Route::get('/excel', 'LaporanPenyesuaianController@excel');
            Route::get('/cetak', 'LaporanPenyesuaianController@cetak');
        });

        Route::prefix('laporan_stock')->group(function () {
            Route::get('', 'LaporanStockController@index');
            Route::post('/search', 'LaporanStockController@search');
            Route::get('/excel', 'LaporanStockController@excel');
            Route::get('/cetak', 'LaporanStockController@cetak');
        });

        Route::prefix('laporan_mutasi')->group(function () {
            Route::get('', 'LaporanMutasiController@index');
            Route::post('/search', 'LaporanMutasiController@search');
            Route::get('/excel', 'LaporanMutasiController@excel');
            Route::get('/cetak', 'LaporanMutasiController@cetak');
        });
    });

    Route::resource('manajemen_stok/stok_opname', 'StokOpnameController');
    Route::post('manajemen_stok/stok_opname/search', 'StokOpnameController@search');
    Route::get('manajemen_stok/stok_opname/search/produk', 'StokOpnameController@search_produk');

    Route::prefix('keuangan')->group(function () {
        Route::resource('akun', 'AkunController');
        Route::post('akun/search', 'AkunController@search');

        Route::resource('jurnal', 'JurnalController')->except('show');
        Route::post('jurnal/search', 'JurnalController@search');
        Route::get('jurnal/export', 'JurnalController@export');

        Route::resource('jurnal/{jurnal}/detail', 'JurnalDetailController');

        Route::get('buku_besar', 'BukuBesarController@index');
        Route::post('buku_besar/search', 'BukuBesarController@search');
        Route::get('buku_besar/export', 'BukuBesarController@export');

        Route::get('neraca', 'NeracaController@index');
        Route::post('neraca/search', 'NeracaController@search');
        Route::get('neraca/export', 'NeracaController@export');

        Route::get('laba_rugi', 'LabaRugiController@index');
        Route::post('laba_rugi/search', 'LabaRugiController@search');
        Route::get('laba_rugi/export', 'LabaRugiController@export');
    });

});
Route::get('import/payroll/{jenis?}', 'ImportController@update_payroll');
Route::get('import/produk', 'ImportController@produk');
Route::get('import/transaksi', 'ImportController@update_transaksi');
Route::get('import/get_kode_kategori/{id}/{kode?}', 'ImportController@get_kode_kategori');
Route::get('import/anggota', 'ImportController@anggota');
Route::get('import/update_status_pinjaman', 'ImportController@update_status_pinjaman');
Route::get('import/pinjaman/{jenis?}', 'ImportController@pinjaman');
Route::get('import/pinjaman/status', 'ImportController@proses_status_pinjaman');
Route::get('anggota/cetak', 'AnggotaController@cetak');
// Route::get('import/generate_password_anggota', 'ImportController@generate_password_anggota');

Route::get('simpanan/cetak_buku', 'CetakBukuController@cetak_buku');

Route::get('data_kosong', function () {
    // Turn off output buffering
    ini_set('output_buffering', 'off');
    // Turn off PHP output compression
    ini_set('zlib.output_compression', false);

    //Flush (send) the output buffer and turn off output buffering
    while(@ob_end_flush());

    // Implicitly flush the buffer(s)
    ini_set('implicit_flush', true);
    ob_implicit_flush(true);

    $produk = \App\Produk::orderBy('nama_produk', 'asc')->get();
    $result = [];
    echo "Proses " . count($produk) . "<br>";
    foreach ($produk as $key => $item) {
        echo "Proses ". ($key + 1) ." /" . count($produk) . "<br>";
        $stock = \App\Helpers\GlobalHelper::stok_barang($item->id);

        if ($stock['stok_awal'] == 0 && $stock['pembelian'] == 0 && $stock['retur'] == 0 && $stock['retur_penjualan'] == 0 && $stock['terjual'] == 0 && $stock['penyesuaian'] == 0) {
            $result[] = $item;
        }
    }
    dd($result);
});

//Route::get('proses_zz', function () {
//    // Turn off output buffering
//    ini_set('output_buffering', 'off');
//    // Turn off PHP output compression
//    ini_set('zlib.output_compression', false);
//
//    //Flush (send) the output buffer and turn off output buffering
//    while(@ob_end_flush());
//
//    // Implicitly flush the buffer(s)
//    ini_set('implicit_flush', true);
//    ob_implicit_flush(true);
//
//    $data = \Illuminate\Support\Facades\DB::table('temp')->get();
//    foreach ($data as $item) {
//        echo $item->no . '<br>';
//        $anggota = \App\Anggota::where('no_anggota', $item->no)->first();
//
//        if (!empty($anggota)) {
////            \App\Transaksi::updateOrCreate([
////                'fid_anggota' => $item->no,
////                'tanggal' => '2023-05-30',
////                'fid_jenis_transaksi' => 1,
////                'fid_metode_transaksi' => 1,
////                'keterangan' => 'Saldo Awal Simpanan',
////                'fid_status' => 4,
////                'created_by' => 'K 0977',
////            ], [
////                'nominal' => $item->pokok
////            ]);
////
////            \App\Transaksi::updateOrCreate([
////                'fid_anggota' => $item->no,
////                'tanggal' => '2023-06-30',
////                'fid_jenis_transaksi' => 2,
////                'fid_metode_transaksi' => 1,
////                'keterangan' => 'Saldo Awal Simpanan',
////                'fid_status' => 4,
////                'created_by' => 'K 0977',
////            ], [
////                'nominal' => $item->wajib
////            ]);
//
//            \App\Transaksi::updateOrCreate([
//                'fid_anggota' => $item->no,
//                'tanggal' => '2023-06-30',
//                'fid_jenis_transaksi' => 3,
//                'fid_metode_transaksi' => 1,
//                'keterangan' => 'Saldo Awal Simpanan Sukarela',
//                'fid_status' => 4,
//                'created_by' => 'K 0977',
//            ], [
//                'nominal' => $item->sukarela
//            ]);
//
////            \App\Transaksi::updateOrCreate([
////                'fid_anggota' => $item->no,
////                'tanggal' => '2023-06-30',
////                'fid_jenis_transaksi' => 4,
////                'fid_metode_transaksi' => 1,
////                'keterangan' => 'Saldo Awal Simpanan',
////                'fid_status' => 4,
////                'created_by' => 'K 0977',
////            ], [
////                'nominal' => $item->sukarela_wajib
////            ]);
//        }
//    }
//});
//
//Route::get('penutupan_masal', function () {
//    $list_anggota = ['AK 0002', 'AK 0005', 'AK 0008', 'AK 0020', 'AK 0051', 'AK 0053', 'AK 0059', 'AK 0072', 'AK 0091', 'AK 0097', 'K 0037', 'K 0036', 'K 0041', 'K 0066', 'K 0073', 'K 0083', 'K 0106', 'K 0113', 'K 0122', 'K 0136', 'K 0142', 'K 0143', 'K 0146', 'K 0148', 'K 0154', 'K 0156', 'K 0160', 'K 0205', 'K 0231', 'K 0258', 'K 0261', 'K 0264', 'K 0274', 'K 0309', 'K 0311', 'K 0320', 'K 0330', 'K 0336', 'K 0325', 'K 0328', 'K 0344', 'K 0366', 'K 0377', 'K 0381', 'K 0386', 'K 0401', 'K 0427', 'K 0449', 'K 0457', 'K 0476', 'K 0489', 'K 0495', 'K 0504', 'K 0544', 'K 0560', 'K 0583', 'K 0586', 'K 0601', 'K 0605', 'K 0607', 'K 0655', 'K 0691', 'K 0729', 'K 0751', 'K 0769', 'K 0770', 'K 0771', 'K 0779', 'K 0869', 'K 0876', 'K 0907', 'K 0923', 'K 0972', 'K 1014', 'K 1015', 'K 1036', 'K 1037', 'K 1052', 'K 1069', 'K 1072', 'K 1091', 'K 1105', 'K 1108', 'K 1115', 'K 1130', 'K 1153', 'K 1156', 'K 1196', 'K 1250', 'K 1280', 'K 1285', 'K 1288', 'K 1317', 'K 1330', 'K 1412', 'K 1415', 'K 1441', 'K 1447', 'K 1470', 'K 1495', 'K 1511', 'K 1516', 'K 1526', 'K 1553', 'K 1605', 'K 1628', 'K 1670', 'K 1694', 'K 1728', 'K 1752', 'K 1753', 'K 1761', 'K 1763', 'K 1766', 'K 1772', 'K 1803', 'K 1842', 'K 1844', 'K 1847', 'K 1876', 'K 1878', 'K 1893', 'K 1884', 'K 1897', 'K 1899', 'K 1903', 'K 1951', 'K 1964'];
//    foreach ($list_anggota as $key => $no_anggota) {
//
//        $simpanan_pokok = \App\Helpers\GlobalHelper::saldo_tabungan($no_anggota, 1);
//        $simpanan_wajib = \App\Helpers\GlobalHelper::saldo_tabungan($no_anggota, 2);
//        $simpanan_sukarela = \App\Helpers\GlobalHelper::saldo_tabungan($no_anggota, 3);
//        $simpanan_hari_raya = \App\Helpers\GlobalHelper::saldo_tabungan($no_anggota, 4);
//
//        $field = new \App\Transaksi();
//        $field->created_at = date('Y-m-d H:i:s');
//        $field->created_by = 'K 0246';
//        $field->fid_status = 4;
//        $field->fid_jenis_transaksi = 1;
//        $field->fid_anggota = $no_anggota;
//        $field->tanggal = date('Y-m-d');
//        $field->fid_metode_transaksi = 1;
//        $field->nominal = intval($simpanan_pokok) * -1;
//        $field->keterangan = 'Penarikan Simpanan Masal';
//        $field->save();
//
//        $field = new \App\Transaksi();
//        $field->created_at = date('Y-m-d H:i:s');
//        $field->created_by = 'K 0246';
//        $field->fid_status = 4;
//        $field->fid_jenis_transaksi = 2;
//        $field->fid_anggota = $no_anggota;
//        $field->tanggal = date('Y-m-d');
//        $field->fid_metode_transaksi = 1;
//        $field->nominal = intval($simpanan_wajib) * -1;
//        $field->keterangan = 'Penarikan Simpanan Masal';
//        $field->save();
//
//        $field = new \App\Transaksi();
//        $field->created_at = date('Y-m-d H:i:s');
//        $field->created_by = 'K 0246';
//        $field->fid_status = 4;
//        $field->fid_jenis_transaksi = 3;
//        $field->fid_anggota = $no_anggota;
//        $field->tanggal = date('Y-m-d');
//        $field->fid_metode_transaksi = 1;
//        $field->nominal = intval($simpanan_sukarela) * -1;
//        $field->keterangan = 'Penarikan Simpanan Masal';
//        $field->save();
//
//        $field = new \App\Transaksi();
//        $field->created_at = date('Y-m-d H:i:s');
//        $field->created_by = 'K 0246';
//        $field->fid_status = 4;
//        $field->fid_jenis_transaksi = 4;
//        $field->fid_anggota = $no_anggota;
//        $field->tanggal = date('Y-m-d');
//        $field->fid_metode_transaksi = 1;
//        $field->nominal = intval($simpanan_hari_raya) * -1;
//        $field->keterangan = 'Penarikan Simpanan Masal';
//        $field->save();
//    }
//});
