package com.esimko.mobile.domain.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.*

interface ShoppingRepository {
    suspend fun getProducts(page: Int, perPage: Int = 20, search: String? = null): Result<Paged<Product>>
    suspend fun getProductDetail(id: String): Result<ProductDetail>
    suspend fun getCart(): Result<Cart>
    suspend fun updateCart(produkId: Long, qty: Int): Result<Cart>
    suspend fun deleteFromCart(produkId: Long): Result<Cart>
    suspend fun checkout(barang: List<Long>, jumlah: List<Int>): Result<Checkout>
    suspend fun getPurchaseHistory(jenis: String, page: Int = 1, perPage: Int = 20): Result<Paged<PurchaseHistory>>
    suspend fun getPurchaseDetail(jenis: String, id: Long): Result<PurchaseDetail>
    suspend fun getShoppingInstallments(page: Int = 1, perPage: Int = 20): Result<Paged<ShoppingInstallment>>
    suspend fun getReturns(search: String? = null, page: Int = 1, perPage: Int = 20): Result<Paged<Return>>
}
