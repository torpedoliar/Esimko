package com.esimko.mobile.data.remote.dto

import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = true)
data class ProductResponse(
    @Json(name = "id") val id: Long,
    @Json(name = "nama_produk") val nama: String? = null,
    @Json(name = "harga_jual") val harga: Long? = 0,
    @Json(name = "stok_awal") val stok: Int? = 0,
    @Json(name = "gambar") val gambar: String? = null,
    @Json(name = "foto") val foto: String? = null,
    @Json(name = "satuan") val satuan: String? = null,
    @Json(name = "kode") val kode: String? = null,
    @Json(name = "kelompok") val kelompok: String? = null,
    @Json(name = "kategori") val kategori: String? = null,
    @Json(name = "sub_kategori") val subKategori: String? = null,
    @Json(name = "deskripsi") val deskripsi: String? = null,
    @Json(name = "margin") val margin: Float? = null,
    @Json(name = "harga_beli") val hargaBeli: Long? = 0
)

@JsonClass(generateAdapter = true)
data class ProductDetailResponse(
    @Json(name = "id") val id: Long,
    @Json(name = "nama_produk") val nama: String? = null,
    @Json(name = "harga_jual") val harga: Long? = 0,
    @Json(name = "stok_awal") val stok: Int? = 0,
    @Json(name = "gambar") val gambar: String? = null,
    @Json(name = "foto") val foto: String? = null,
    @Json(name = "satuan") val satuan: String? = null,
    @Json(name = "kode") val kode: String? = null,
    @Json(name = "kelompok") val kelompok: String? = null,
    @Json(name = "kategori") val kategori: String? = null,
    @Json(name = "sub_kategori") val subKategori: String? = null,
    @Json(name = "deskripsi") val deskripsi: String? = null,
    @Json(name = "terjual") val terjual: Int? = 0,
    @Json(name = "sisa") val sisa: Int? = 0,
    @Json(name = "margin") val margin: Float? = null,
    @Json(name = "harga_beli") val hargaBeli: Long? = 0
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
    @Json(name = "harga") val harga: Long? = 0,
    @Json(name = "harga_jual") val hargaJual: Long? = 0,
    @Json(name = "qty") val qty: Int? = 0,
    @Json(name = "jumlah") val jumlah: Int? = 0,
    @Json(name = "subtotal") val subtotal: Long? = 0
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
    @Json(name = "total_pembayaran") val total: Long? = 0,
    @Json(name = "tanggal") val tanggal: String? = null,
    @Json(name = "status") val status: String? = null,
    @Json(name = "angsuran") val angsuran: Int? = null
)

@JsonClass(generateAdapter = true)
data class PurchaseDetailResponse(
    @Json(name = "id") val id: Long,
    @Json(name = "total_pembayaran") val total: Long? = 0,
    @Json(name = "tanggal") val tanggal: String? = null,
    @Json(name = "status") val status: String? = null,
    @Json(name = "items") val items: List<PurchaseItemResponse>? = emptyList()
)

@JsonClass(generateAdapter = true)
data class PurchaseItemResponse(
    @Json(name = "id") val id: Long,
    @Json(name = "nama") val nama: String? = null,
    @Json(name = "nama_produk") val namaProduk: String? = null,
    @Json(name = "harga") val harga: Long? = 0,
    @Json(name = "jumlah") val qty: Int? = 0,
    @Json(name = "total") val subtotal: Long? = 0
)

@JsonClass(generateAdapter = true)
data class ShoppingInstallmentResponse(
    @Json(name = "id") val id: Long,
    @Json(name = "ke") val ke: Int? = 0,
    @Json(name = "nominal") val nominal: Long? = 0,
    @Json(name = "tanggal_jatuh_tempo") val tanggalJatuhTempo: String? = null,
    @Json(name = "tanggal_bayar") val tanggalBayar: String? = null,
    @Json(name = "status") val status: String? = null
)

@JsonClass(generateAdapter = true)
data class ReturnResponse(
    @Json(name = "id") val id: Long,
    @Json(name = "belanja_id") val belanjaId: Long? = null,
    @Json(name = "alasan") val alasan: String? = null,
    @Json(name = "tanggal") val tanggal: String? = null,
    @Json(name = "status") val status: String? = null
)

@JsonClass(generateAdapter = true)
data class CancelPurchaseRequest(
    @Json(name = "id") val id: Long,
    @Json(name = "alasan") val alasan: String
)
