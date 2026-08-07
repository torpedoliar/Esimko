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
    val subtotal: Long,
    val foto: String? = null,   // thumbnail baris keranjang (Task 23)
    val sisa: Int? = null       // batas tombol + di keranjang (Task 23)
)

data class Checkout(
    val failedItems: List<FailedItemInfo>
)

data class FailedItemInfo(
    val fidProduk: Long? = null,
    val nama: String? = null
)

data class PurchaseHistory(
    val id: Long,
    val total: Long,
    val tanggal: String,
    val status: String,
    val angsuran: Int?,
    val color: String? = null,
    val noTransaksi: String? = null,
    val jumlah: Int = 0
)

data class PurchaseDetail(
    val id: Long,
    val total: Long,
    val tanggal: String,
    val status: String,
    val items: List<PurchaseItem>,
    val noTransaksi: String = "",
    /** Label status dari `keterangan_status_transaksi`; `status` teks tidak dikirim endpoint ini. */
    val labelStatus: String = "",
    val keteranganStatus: String = "",
    val metodePembayaran: String = "",
    val jumlah: Int = 0,
    val subtotal: Long = 0L,
    val diskonNominal: Long = 0L,
    val sisaAngsuran: Long = 0L,
    val sisaTenor: Int = 0
) {
    /** Yang ditampilkan sebagai status: label dulu, `status` teks hanya cadangan. */
    val statusTampil: String get() = labelStatus.ifBlank { status }
}

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
    val bulan: String?,
    val namaBulan: String?,
    val status: String,
    val color: String? = null,
    val noTransaksi: String? = null,
    /** `penjualan.jenis_belanja` — `toko` | `konsinyasi` | `online`. Menentukan path detail. */
    val jenisBelanja: String = "toko"
)

data class Return(
    val id: Long,
    val noRetur: String,
    val namaProduk: String,
    val jumlah: Int,
    val keterangan: String?,
    val tanggal: String,
    val foto: String? = null,
    val satuan: String = "",
    val kode: String = ""
)
