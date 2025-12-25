<?php
//-----------------------------------------WEB API------------------------------------------------//

Route::get('find_anggota/{id}/{bulan?}', 'ApiController@find_anggota');
Route::get('find_metode_pembayaran/{id}', 'ApiController@find_metode_pembayaran');
Route::get('find_periode_pengurus/{id}', 'ApiController@find_periode_pengurus');
Route::get('find_produk/{id}', 'ApiController@find_produk');
Route::get('find_status/{jenis}/{id}', 'ApiController@find_status');
Route::get('find_supplier/{id}', 'ApiController@find_supplier');
Route::get('find_keranjang/{id}', 'ApiController@find_keranjang');
Route::get('find_jurnal_detail/{id}', 'ApiController@find_jurnal_detail');
Route::get('find_syarat_ketentuan/{id}', 'ApiController@find_syarat_ketentuan');
Route::get('find_items_pembelian/{id}', 'ApiController@find_items_pembelian');
Route::get('find_items_penjualan/{id}', 'ApiController@find_items_penjualan');
Route::get('find_items_return_pembelian/{id}', 'ApiController@find_items_return_pembelian');
Route::get('find_buku_kas_detail/{id}', 'ApiController@find_buku_kas_detail');
Route::get('find_attachment_berita/{id}', 'ApiController@find_attachment_berita');
Route::get('get_anggota/{jenis?}/{search?}', 'ApiController@get_anggota');
Route::get('get_kategori/{parent_id}/{selected?}', 'ApiController@get_kategori');
Route::get('get_tree_kategori', 'ApiController@get_tree_kategori');
Route::get('get_bagan_akun/{status?}', 'ApiController@get_bagan_akun');
Route::get('get_produk/{supplier?}/{search?}', 'ApiController@get_produk');
Route::get('get_produk2/{search?}', 'ApiController@get_produk2');
Route::get('get_items_penjualan/{id}', 'ApiController@get_items_penjualan');
Route::get('get_items_pembelian/{id}', 'ApiController@get_items_pembelian');
Route::get('check_sisa_pinjaman/{anggota}/{jenis}', 'ApiController@check_sisa_pinjaman');

//----------------------------------------MOBILE API--------------------------------------------//

Route::post('mobile/auth/login', 'MobileController@login');
Route::post('mobile/auth/register', 'MobileController@register');

Route::get('mobile/anggota/profil', 'MobileController@profil_anggota');
Route::post('mobile/anggota/ubah_password', 'MobileController@ubah_password');

Route::get('mobile/master/jenis_transaksi/{modul}', 'MobileController@jenis_transaksi');
Route::get('mobile/master/status_transaksi/{modul}', 'MobileController@status_transaksi');

Route::get('mobile/berita', 'MobileController@berita');
Route::get('mobile/berita/detail', 'MobileController@detail_berita');

Route::get('mobile/riwayat_transaksi', 'MobileController@riwayat_transaksi');

Route::get('mobile/transaksi/{modul}', 'MobileController@transaksi');
Route::get('mobile/transaksi/{modul}/detail', 'MobileController@detail_transaksi');

Route::get('mobile/gaji_pokok', 'MobileController@gaji_pokok');

Route::post('mobile/transaksi/{jenis}/proses', 'MobileController@proses_transaksi');
Route::post('mobile/transaksi/upload_bukti_transaksi', 'MobileController@upload_bukti_transaksi');
Route::post('mobile/transaksi/batalkan', 'MobileController@batalkan_transaksi');

Route::get('mobile/angsuran', 'MobileController@angsuran_pinjaman');

Route::get('mobile/produk', 'MobileController@produk');
Route::get('mobile/produk/detail', 'MobileController@detail_produk');

Route::get('mobile/belanja/keranjang', 'MobileController@keranjang');
Route::post('mobile/belanja/keranjang/proses', 'MobileController@proses_keranjang');
Route::post('mobile/belanja/keranjang/checkout', 'MobileController@checkout_keranjang');
Route::post('mobile/belanja/batalkan', 'MobileController@batalkan_belanja');

Route::get('mobile/belanja/riwayat/{jenis?}', 'MobileController@belanja');
Route::get('mobile/belanja/riwayat/{jenis?}/detail', 'MobileController@detail_belanja');

Route::get('mobile/belanja/angsuran', 'MobileController@angsuran_belanja');

Route::get('mobile/belanja/retur', 'MobileController@retur_barang');

Route::post('mobile/upload_avatar', 'MobileController@upload_avatar');
