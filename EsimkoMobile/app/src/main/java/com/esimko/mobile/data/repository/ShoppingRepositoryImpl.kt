package com.esimko.mobile.data.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.core.network.apiErrorMessage
import com.esimko.mobile.data.remote.api.ShoppingApi
import com.esimko.mobile.data.remote.dto.CartRequest
import com.esimko.mobile.data.remote.dto.CancelPurchaseRequest
import com.esimko.mobile.data.remote.dto.CheckoutRequest
import com.esimko.mobile.domain.model.*
import com.esimko.mobile.domain.repository.ShoppingRepository
import javax.inject.Inject

class ShoppingRepositoryImpl @Inject constructor(
    private val api: ShoppingApi
) : ShoppingRepository {

    override suspend fun getProducts(page: Int, perPage: Int, search: String?): Result<Paged<Product>> {
        return try {
            val response = api.getProducts(page, perPage, search?.takeIf { it.isNotBlank() })
            if (response.success && response.data != null) {
                val items = response.data.map { dto ->
                    Product(
                        id = dto.id,
                        nama = dto.nama.orEmpty(),
                        harga = dto.harga?.let { Math.round(it) } ?: 0L,
                        stok = dto.stok?.let { Math.round(it) } ?: 0,
                        gambar = dto.foto ?: dto.gambar,
                        satuan = dto.satuan.orEmpty(),
                        kode = dto.kode.orEmpty(),
                        kategori = dto.kategori.orEmpty(),
                        kelompok = dto.kelompok.orEmpty()
                    )
                }
                Result.Success(
                    Paged(
                        items = items,
                        page = response.meta?.page ?: page,
                        lastPage = response.meta?.last_page ?: (response.meta?.page ?: page)
                    )
                )
            } else {
                Result.Error(response.message ?: "Gagal memuat produk")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Tidak ada koneksi. Periksa jaringan."))
        }
    }

    override suspend fun getProductDetail(id: String): Result<ProductDetail> {
        return try {
            val response = api.getProductDetail(id)
            if (response.success && response.data != null) {
                val dto = response.data
                Result.Success(ProductDetail(
                    id = dto.id,
                    nama = dto.nama.orEmpty(),
                    harga = dto.harga?.let { Math.round(it) } ?: 0L,
                    stok = dto.stok?.let { Math.round(it) } ?: 0,
                    gambar = dto.foto ?: dto.gambar,
                    deskripsi = dto.deskripsi,
                    satuan = dto.satuan.orEmpty(),
                    kode = dto.kode.orEmpty(),
                    kategori = dto.kategori.orEmpty(),
                    kelompok = dto.kelompok.orEmpty(),
                    terjual = dto.terjual?.let { Math.round(it) } ?: 0,
                    sisa = dto.sisa?.let { Math.round(it) } ?: 0
                ))
            } else {
                Result.Error(response.message ?: "Gagal memuat detail produk")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Tidak ada koneksi. Periksa jaringan."))
        }
    }

    override suspend fun getCart(): Result<Cart> {
        return try {
            val response = api.getCart()
            if (response.success && response.data != null) {
                Result.Success(Cart(
                    items = (response.data.items ?: emptyList()).map { item ->
                        CartItem(
                            id = item.id,
                            produkId = item.produkId ?: item.fidProduk ?: 0,
                            nama = item.nama ?: item.namaProduk.orEmpty(),
                            harga = (item.harga ?: item.hargaJual)?.let { Math.round(it) } ?: 0L,
                            qty = (item.qty ?: item.jumlah)?.let { Math.round(it) } ?: 0,
                            subtotal = item.subtotal?.let { Math.round(it) } ?: 0L,
                            foto = item.foto,
                            sisa = item.sisa?.let { Math.round(it) }
                        )
                    },
                    total = response.data.total ?: 0
                ))
            } else {
                Result.Error(response.message ?: "Gagal memuat keranjang")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Tidak ada koneksi. Periksa jaringan."))
        }
    }

    override suspend fun updateCart(produkId: Long, qty: Int): Result<Cart> {
        return try {
            val request = CartRequest(id = produkId, jumlah = qty, action = "add")
            val response = api.updateCart(request)
            if (response.success) {
                getCart()
            } else {
                // Pesan server termasuk 'Jumlah melebih stok' (typo milik backend, jangan diperbaiki
                // di client — pesannya diteruskan apa adanya supaya cocok dengan web).
                Result.Error(response.message ?: "Gagal mengubah keranjang")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Tidak ada koneksi. Periksa jaringan."))
        }
    }

    override suspend fun deleteFromCart(produkId: Long): Result<Cart> {
        return try {
            val request = CartRequest(id = produkId, jumlah = 0, action = "delete")
            val response = api.updateCart(request)
            if (response.success) {
                getCart()
            } else {
                Result.Error(response.message ?: "Gagal menghapus barang dari keranjang")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Tidak ada koneksi. Periksa jaringan."))
        }
    }

    override suspend fun checkout(barang: List<Long>, jumlah: List<Int>): Result<Checkout> {
        return try {
            val response = api.checkout(CheckoutRequest(barang, jumlah))
            if (response.success && response.data != null) {
                Result.Success(Checkout(
                    failedItems = (response.data.failedItems ?: emptyList()).map {
                        FailedItemInfo(fidProduk = it.fidProduk, nama = it.nama)
                    }
                ))
            } else {
                Result.Error(response.message ?: "Gagal checkout")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Tidak ada koneksi. Periksa jaringan."))
        }
    }

    override suspend fun getPurchaseHistory(jenis: String, page: Int, perPage: Int): Result<Paged<PurchaseHistory>> {
        return try {
            val response = api.getPurchaseHistory(jenis, page, perPage)
            if (response.success && response.data != null) {
                Result.Success(Paged(
                    items = response.data.map { dto ->
                        PurchaseHistory(
                            id = dto.id,
                            total = dto.total?.let { Math.round(it) } ?: 0L,
                            tanggal = dto.tanggal.orEmpty(),
                            status = dto.status.orEmpty(),
                            angsuran = dto.angsuran,
                            color = dto.color,
                            noTransaksi = dto.noTransaksi,
                            jumlah = dto.jumlah?.let { Math.round(it).toInt() } ?: 0
                        )
                    },
                    page = response.meta?.page ?: page,
                    lastPage = response.meta?.last_page ?: (response.meta?.page ?: page)
                ))
            } else {
                Result.Error(response.message ?: "Gagal memuat riwayat belanja")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Tidak ada koneksi. Periksa jaringan."))
        }
    }

    override suspend fun getPurchaseDetail(jenis: String, id: Long): Result<PurchaseDetail> {
        return try {
            val response = api.getPurchaseDetail(jenis, id)
            if (response.success && response.data != null) {
                val dto = response.data
                Result.Success(
                    PurchaseDetail(
                        id = dto.id,
                        total = Math.round(dto.total ?: 0.0),
                        tanggal = dto.tanggal.orEmpty(),
                        status = dto.status.orEmpty(),
                        items = dto.items.orEmpty().map { item ->
                            PurchaseItem(
                                id = item.id,
                                nama = item.nama ?: item.namaProduk.orEmpty(),
                                harga = item.harga?.let { Math.round(it) } ?: 0L,
                                qty = item.qty?.let { Math.round(it) } ?: 0,
                                subtotal = item.subtotal?.let { Math.round(it) } ?: 0L
                            )
                        },
                        noTransaksi = dto.noTransaksi.orEmpty(),
                        labelStatus = dto.labelStatus.orEmpty(),
                        keteranganStatus = dto.keteranganStatus.orEmpty(),
                        metodePembayaran = dto.metodePembayaran.orEmpty(),
                        jumlah = Math.round(dto.jumlah ?: 0.0).toInt(),
                        subtotal = Math.round(dto.subtotal ?: 0.0),
                        diskonNominal = Math.round(dto.diskonNominal ?: 0.0),
                        sisaAngsuran = Math.round(dto.sisaAngsuran ?: 0.0),
                        sisaTenor = dto.sisaTenor ?: 0
                    )
                )
            } else {
                Result.Error(response.message ?: "Gagal memuat detail belanja")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Tidak ada koneksi. Periksa jaringan."))
        }
    }

    override suspend fun getShoppingInstallments(page: Int, perPage: Int): Result<Paged<ShoppingInstallment>> {
        return try {
            val response = api.getShoppingInstallments(page, perPage)
            if (response.success && response.data != null) {
                Result.Success(Paged(
                    items = response.data.map { dto ->
                        ShoppingInstallment(
                            id = dto.id,
                            ke = dto.ke ?: 0,
                            nominal = dto.nominal?.let { Math.round(it) } ?: 0L,
                            bulan = dto.bulan?.takeIf { it.isNotBlank() },
                            namaBulan = dto.namaBulan?.takeIf { it.isNotBlank() },
                            status = dto.status.orEmpty(),
                            color = dto.color,
                            noTransaksi = dto.noTransaksi,
                            jenisBelanja = dto.jenisBelanja?.takeIf { it.isNotBlank() } ?: "toko"
                        )
                    },
                    page = response.meta?.page ?: page,
                    lastPage = response.meta?.last_page ?: (response.meta?.page ?: page)
                ))
            } else {
                Result.Error(response.message ?: "Gagal memuat angsuran belanja")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Tidak ada koneksi. Periksa jaringan."))
        }
    }

    override suspend fun getReturns(search: String?, page: Int, perPage: Int): Result<Paged<Return>> {
        return try {
            val response = api.getReturns(search?.takeIf { it.isNotBlank() }, page, perPage)
            if (response.success && response.data != null) {
                Result.Success(Paged(
                    items = response.data.map { dto ->
                        Return(
                            id = dto.id,
                            noRetur = dto.noRetur.orEmpty(),
                            namaProduk = dto.namaProduk.orEmpty(),
                            jumlah = dto.jumlah ?: 0,
                            keterangan = dto.keterangan,
                            tanggal = dto.tanggal.orEmpty(),
                            foto = dto.foto,
                            satuan = dto.satuan.orEmpty(),
                            kode = dto.kode.orEmpty()
                        )
                    },
                    page = response.meta?.page ?: page,
                    lastPage = response.meta?.last_page ?: (response.meta?.page ?: page)
                ))
            } else {
                Result.Error(response.message ?: "Gagal memuat retur barang")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Tidak ada koneksi. Periksa jaringan."))
        }
    }
}
