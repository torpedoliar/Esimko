package com.esimko.mobile.data.remote.dto

import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = true)
data class ProductResponse(
    @Json(name = "id") val id: Long,
    @Json(name = "nama_produk") val nama: String? = null,
    @Json(name = "harga_jual") val harga: Double? = 0.0,   // produk.harga_jual double
    @Json(name = "stok_awal") val stok: Float? = 0f,       // produk.stok_awal float
    @Json(name = "gambar") val gambar: String? = null,
    @Json(name = "foto") val foto: String? = null,
    @Json(name = "satuan") val satuan: String? = null,
    @Json(name = "kode") val kode: String? = null,
    @Json(name = "kelompok") val kelompok: String? = null,
    @Json(name = "kategori") val kategori: String? = null,
    @Json(name = "sub_kategori") val subKategori: String? = null,
    @Json(name = "deskripsi") val deskripsi: String? = null,
    @Json(name = "margin") val margin: Float? = null,
    @Json(name = "harga_beli") val hargaBeli: Double? = 0.0 // produk.harga_beli double
)

@JsonClass(generateAdapter = true)
data class ProductDetailResponse(
    @Json(name = "id") val id: Long,
    @Json(name = "nama_produk") val nama: String? = null,
    @Json(name = "harga_jual") val harga: Double? = 0.0,
    @Json(name = "stok_awal") val stok: Float? = 0f,
    @Json(name = "gambar") val gambar: String? = null,
    @Json(name = "foto") val foto: String? = null,
    @Json(name = "satuan") val satuan: String? = null,
    @Json(name = "kode") val kode: String? = null,
    @Json(name = "kelompok") val kelompok: String? = null,
    @Json(name = "kategori") val kategori: String? = null,
    @Json(name = "sub_kategori") val subKategori: String? = null,
    @Json(name = "deskripsi") val deskripsi: String? = null,
    // MobileHelper::stokBarang mengembalikan hasil SUM() MySQL atas kolom float → bisa berdesimal
    @Json(name = "terjual") val terjual: Float? = 0f,
    @Json(name = "sisa") val sisa: Float? = 0f,
    @Json(name = "margin") val margin: Float? = null,
    @Json(name = "harga_beli") val hargaBeli: Double? = 0.0
)

@JsonClass(generateAdapter = true)
data class CartResponse(
    @Json(name = "items") val items: List<CartItemResponse>? = emptyList(),
    @Json(name = "total") val total: Long? = 0
)

@JsonClass(generateAdapter = true)
data class CartItemResponse(
    @Json(name = "id") val id: Long,
    @Json(name = "produk_id") val produkId: Long? = null,
    @Json(name = "fid_produk") val fidProduk: Long? = null,
    @Json(name = "nama") val nama: String? = null,
    @Json(name = "nama_produk") val namaProduk: String? = null,
    @Json(name = "harga") val harga: Double? = 0.0,        // keranjang_belanja.harga double
    @Json(name = "harga_jual") val hargaJual: Double? = 0.0,
    @Json(name = "qty") val qty: Float? = 0f,              // keranjang_belanja.jumlah float(20,0)
    @Json(name = "jumlah") val jumlah: Float? = 0f,
    @Json(name = "subtotal") val subtotal: Double? = 0.0,  // keranjang_belanja.total double
    @Json(name = "foto") val foto: String? = null,         // dikirim keranjang(), dipakai Task 23
    @Json(name = "kode") val kode: String? = null,
    @Json(name = "sisa") val sisa: Float? = null           // stok tersisa, untuk batas tombol +
)

@JsonClass(generateAdapter = true)
data class CartRequest(
    @Json(name = "id") val id: Long,
    @Json(name = "jumlah") val jumlah: Int,
    @Json(name = "action") val action: String = "add"
)

@JsonClass(generateAdapter = true)
data class CheckoutRequest(
    @Json(name = "barang") val barang: List<Long>,
    @Json(name = "jumlah") val jumlah: List<Int>
)

@JsonClass(generateAdapter = true)
data class FailedItem(
    @Json(name = "fid_produk") val fidProduk: Long? = null,
    @Json(name = "nama") val nama: String? = null
)

@JsonClass(generateAdapter = true)
data class CheckoutResponse(
    @Json(name = "failed_items") val failedItems: List<FailedItem>? = emptyList()
)

@JsonClass(generateAdapter = true)
data class PurchaseHistoryResponse(
    @Json(name = "id") val id: Long,
    @Json(name = "no_transaksi") val noTransaksi: String? = null,
    // Double, bukan Long — alasan sama dengan total_angsuran di Step 4: kolom uang MySQL bisa
    // `double` dan Moshi menolak nilai berpecahan ke Long. Dibulatkan Math.round di mapper.
    @Json(name = "total_pembayaran") val total: Double? = 0.0,
    @Json(name = "tanggal") val tanggal: String? = null,
    @Json(name = "status") val status: String? = null,
    @Json(name = "color") val color: String? = null,
    // `jumlah` adalah SUM() atas kolom jumlah item — dibaca Double lalu dibulatkan, sama alasannya.
    @Json(name = "jumlah") val jumlah: Double? = 0.0,
    @Json(name = "angsuran") val angsuran: Int? = null
)

@JsonClass(generateAdapter = true)
data class PurchaseDetailResponse(
    @Json(name = "id") val id: Long,
    @Json(name = "no_transaksi") val noTransaksi: String? = null,
    // SUM/round MySQL bisa datang sebagai desimal → Double, dibulatkan di mapper.
    @Json(name = "total_pembayaran") val total: Double? = 0.0,
    @Json(name = "tanggal") val tanggal: String? = null,
    @Json(name = "status") val status: String? = null,
    @Json(name = "label_status") val labelStatus: String? = null,
    @Json(name = "keterangan_status") val keteranganStatus: String? = null,
    @Json(name = "metode_pembayaran") val metodePembayaran: String? = null,
    @Json(name = "jumlah") val jumlah: Double? = 0.0,
    @Json(name = "subtotal") val subtotal: Double? = 0.0,
    @Json(name = "diskon_nominal") val diskonNominal: Double? = 0.0,
    @Json(name = "sisa_angsuran") val sisaAngsuran: Double? = 0.0,
    @Json(name = "sisa_tenor") val sisaTenor: Int? = 0,
    @Json(name = "items") val items: List<PurchaseItemResponse>? = emptyList()
)

@JsonClass(generateAdapter = true)
data class PurchaseItemResponse(
    @Json(name = "id") val id: Long,
    @Json(name = "nama") val nama: String? = null,
    @Json(name = "nama_produk") val namaProduk: String? = null,
    @Json(name = "harga") val harga: Double? = 0.0,        // item_penjualan.harga double
    @Json(name = "jumlah") val qty: Float? = 0f,           // item_penjualan.jumlah float(20,0)
    @Json(name = "total") val subtotal: Double? = 0.0      // item_penjualan.total double
)

@JsonClass(generateAdapter = true)
data class ShoppingInstallmentResponse(
    @Json(name = "id") val id: Long,
    @Json(name = "no_transaksi") val noTransaksi: String? = null,
    @Json(name = "jenis_belanja") val jenisBelanja: String? = null,
    @Json(name = "bulan") val bulan: String? = null,
    @Json(name = "nama_bulan") val namaBulan: String? = null,
    // Dibaca Double, bukan Long: `total_angsuran` ikut kolom uang MySQL yang bisa `double`, dan
    // Moshi menolak `208333.5` ke `Long` (kasus nyata di Task 16). Adapter Double menerima angka
    // bulat maupun berpecahan, jadi ini yang benar di dua-duanya.
    @Json(name = "total_angsuran") val nominal: Double? = 0.0,
    @Json(name = "angsuran_ke") val ke: Int? = 0,
    @Json(name = "status_angsuran") val status: String? = null,
    @Json(name = "color") val color: String? = null
)

@JsonClass(generateAdapter = true)
data class ReturnResponse(
    @Json(name = "id") val id: Long,
    @Json(name = "no_retur") val noRetur: String? = null,
    @Json(name = "nama_produk") val namaProduk: String? = null,
    @Json(name = "jumlah") val jumlah: Int? = 0,
    @Json(name = "keterangan") val keterangan: String? = null,
    @Json(name = "tanggal") val tanggal: String? = null,
    @Json(name = "foto") val foto: String? = null,
    @Json(name = "satuan") val satuan: String? = null,
    @Json(name = "kode") val kode: String? = null
)

@JsonClass(generateAdapter = true)
data class CancelPurchaseRequest(
    @Json(name = "id") val id: Long,
    @Json(name = "alasan") val alasan: String
)
