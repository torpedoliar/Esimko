package com.esimko.mobile.data.repository

import com.esimko.mobile.core.network.Result
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

    override suspend fun getProducts(page: Int, perPage: Int): Result<List<Product>> {
        return try {
            val response = api.getProducts(page, perPage)
            if (response.success && response.data != null) {
                Result.Success(response.data.map { dto ->
                    Product(
                        id = dto.id,
                        nama = dto.nama.orEmpty(),
                        harga = dto.harga ?: 0,
                        stok = dto.stok ?: 0,
                        gambar = dto.foto ?: dto.gambar,
                        satuan = dto.satuan.orEmpty(),
                        kode = dto.kode.orEmpty(),
                        kategori = dto.kategori.orEmpty(),
                        kelompok = dto.kelompok.orEmpty()
                    )
                })
            } else {
                Result.Error(response.message ?: "Failed to load products")
            }
        } catch (e: Exception) {
            Result.Error(e.message ?: "Network error")
        }
    }

    override suspend fun getProductDetail(id: Long): Result<ProductDetail> {
        return try {
            val response = api.getProductDetail(id)
            if (response.success && response.data != null) {
                val dto = response.data
                Result.Success(ProductDetail(
                    id = dto.id,
                    nama = dto.nama.orEmpty(),
                    harga = dto.harga ?: 0,
                    stok = dto.stok ?: 0,
                    gambar = dto.foto ?: dto.gambar,
                    deskripsi = dto.deskripsi,
                    satuan = dto.satuan.orEmpty(),
                    kode = dto.kode.orEmpty(),
                    kategori = dto.kategori.orEmpty(),
                    kelompok = dto.kelompok.orEmpty(),
                    terjual = dto.terjual ?: 0,
                    sisa = dto.sisa ?: 0
                ))
            } else {
                Result.Error(response.message ?: "Failed to load product detail")
            }
        } catch (e: Exception) {
            Result.Error(e.message ?: "Network error")
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
                            harga = item.harga ?: item.hargaJual ?: 0,
                            qty = item.qty ?: item.jumlah ?: 0,
                            subtotal = item.subtotal ?: 0
                        )
                    },
                    total = response.data.total ?: 0
                ))
            } else {
                Result.Error(response.message ?: "Failed to load cart")
            }
        } catch (e: Exception) {
            Result.Error(e.message ?: "Network error")
        }
    }

    override suspend fun updateCart(produkId: Long, qty: Int): Result<Cart> {
        return try {
            val request = CartRequest(id = produkId, jumlah = qty, action = "add")
            val response = api.updateCart(request)
            if (response.success) {
                getCart()
            } else {
                Result.Error(response.message ?: "Failed to update cart")
            }
        } catch (e: Exception) {
            Result.Error(e.message ?: "Network error")
        }
    }

    override suspend fun deleteFromCart(produkId: Long): Result<Cart> {
        return try {
            val request = CartRequest(id = produkId, jumlah = 0, action = "delete")
            api.updateCart(request)
            getCart()
        } catch (e: Exception) {
            Result.Error(e.message ?: "Network error")
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
                Result.Error(response.message ?: "Failed to checkout")
            }
        } catch (e: Exception) {
            Result.Error(e.message ?: "Network error")
        }
    }

    override suspend fun cancelPurchase(id: Long, alasan: String): Result<Unit> {
        return try {
            val request = CancelPurchaseRequest(id = id, alasan = alasan)
            val response = api.cancelPurchase(request)
            if (response.success) {
                Result.Success(Unit)
            } else {
                Result.Error(response.message ?: "Failed to cancel purchase")
            }
        } catch (e: Exception) {
            Result.Error(e.message ?: "Network error")
        }
    }

    override suspend fun getPurchaseHistory(jenis: String, page: Int?, perPage: Int?): Result<List<PurchaseHistory>> {
        return try {
            val response = api.getPurchaseHistory(jenis, page, perPage)
            if (response.success && response.data != null) {
                Result.Success(response.data.map { dto ->
                    PurchaseHistory(
                        id = dto.id,
                        total = dto.total ?: 0,
                        tanggal = dto.tanggal.orEmpty(),
                        status = dto.status.orEmpty(),
                        angsuran = dto.angsuran
                    )
                })
            } else {
                Result.Error(response.message ?: "Failed to load purchase history")
            }
        } catch (e: Exception) {
            Result.Error(e.message ?: "Network error")
        }
    }

    override suspend fun getPurchaseDetail(jenis: String, id: Long): Result<PurchaseDetail> {
        return try {
            val response = api.getPurchaseDetail(jenis, id)
            if (response.success && response.data != null) {
                val dto = response.data
                Result.Success(PurchaseDetail(
                    id = dto.id,
                    total = dto.total ?: 0,
                    tanggal = dto.tanggal.orEmpty(),
                    status = dto.status.orEmpty(),
                    items = (dto.items ?: emptyList()).map { item ->
                        PurchaseItem(
                            id = item.id,
                            nama = item.nama ?: item.namaProduk.orEmpty(),
                            harga = item.harga ?: 0,
                            qty = item.qty ?: 0,
                            subtotal = item.subtotal ?: 0
                        )
                    }
                ))
            } else {
                Result.Error(response.message ?: "Failed to load purchase detail")
            }
        } catch (e: Exception) {
            Result.Error(e.message ?: "Network error")
        }
    }

    override suspend fun getShoppingInstallments(): Result<List<ShoppingInstallment>> {
        return try {
            val response = api.getShoppingInstallments()
            if (response.success && response.data != null) {
                Result.Success(response.data.map { dto ->
                    ShoppingInstallment(
                        id = dto.id,
                        ke = dto.ke ?: 0,
                        nominal = dto.nominal ?: 0,
                        tanggalJatuhTempo = dto.tanggalJatuhTempo.orEmpty(),
                        tanggalBayar = dto.tanggalBayar,
                        status = dto.status.orEmpty()
                    )
                })
            } else {
                Result.Error(response.message ?: "Failed to load shopping installments")
            }
        } catch (e: Exception) {
            Result.Error(e.message ?: "Network error")
        }
    }

    override suspend fun getReturns(): Result<List<Return>> {
        return try {
            val response = api.getReturns()
            if (response.success && response.data != null) {
                Result.Success(response.data.map { dto ->
                    Return(
                        id = dto.id,
                        belanjaId = dto.belanjaId ?: 0,
                        alasan = dto.alasan.orEmpty(),
                        tanggal = dto.tanggal.orEmpty(),
                        status = dto.status.orEmpty()
                    )
                })
            } else {
                Result.Error(response.message ?: "Failed to load returns")
            }
        } catch (e: Exception) {
            Result.Error(e.message ?: "Network error")
        }
    }
}
