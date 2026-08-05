package com.esimko.mobile.domain.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.*

interface ShoppingRepository {
    suspend fun getProducts(page: Int, perPage: Int = 20): Result<List<Product>>
    suspend fun getProductDetail(id: String): Result<ProductDetail>
    suspend fun getCart(): Result<Cart>
    suspend fun updateCart(produkId: Long, qty: Int): Result<Cart>
    suspend fun deleteFromCart(produkId: Long): Result<Cart>
    suspend fun checkout(barang: List<Long>, jumlah: List<Int>): Result<Checkout>
    suspend fun cancelPurchase(id: Long, alasan: String): Result<Unit>
    suspend fun getPurchaseHistory(jenis: String, page: Int? = null, perPage: Int? = null): Result<List<PurchaseHistory>>
    suspend fun getPurchaseDetail(jenis: String, id: Long): Result<PurchaseDetail>
    suspend fun getShoppingInstallments(): Result<List<ShoppingInstallment>>
    suspend fun getReturns(): Result<List<Return>>
}
