package com.esimko.mobile.domain.model

data class Product(
    val id: Long,
    val nama: String,
    val harga: Long,
    val stok: Int,
    val gambar: String?,
    val satuan: String = "",
    val kode: String = "",
    val kategori: String = "",
    val kelompok: String = ""
)

data class ProductDetail(
    val id: Long,
    val nama: String,
    val harga: Long,
    val stok: Int,
    val gambar: String?,
    val deskripsi: String?,
    val satuan: String = "",
    val kode: String = "",
    val kategori: String = "",
    val kelompok: String = "",
    val terjual: Int = 0,
    val sisa: Int = 0
)

data class Cart(
    val items: List<CartItem>,
    val total: Long
)

data class CartItem(
    val id: Long,
    val produkId: Long,
    val nama: String,
    val harga: Long,
    val qty: Int,
    val subtotal: Long
)

data class Checkout(
    val id: Long,
    val total: Long,
    val tanggal: String
)

data class PurchaseHistory(
    val id: Long,
    val total: Long,
    val tanggal: String,
    val status: String,
    val angsuran: Int?
)

data class PurchaseDetail(
    val id: Long,
    val total: Long,
    val tanggal: String,
    val status: String,
    val items: List<PurchaseItem>
)

data class PurchaseItem(
    val id: Long,
    val nama: String,
    val harga: Long,
    val qty: Int,
    val subtotal: Long
)

data class ShoppingInstallment(
    val id: Long,
    val ke: Int,
    val nominal: Long,
    val tanggalJatuhTempo: String,
    val tanggalBayar: String?,
    val status: String
)

data class Return(
    val id: Long,
    val belanjaId: Long,
    val alasan: String,
    val tanggal: String,
    val status: String
)
