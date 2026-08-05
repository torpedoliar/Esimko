package com.esimko.mobile.data.remote.api

import com.esimko.mobile.data.remote.dto.ApiResponse
import com.esimko.mobile.data.remote.dto.ProductResponse
import com.esimko.mobile.data.remote.dto.ProductDetailResponse
import com.esimko.mobile.data.remote.dto.CartResponse
import com.esimko.mobile.data.remote.dto.CartRequest
import com.esimko.mobile.data.remote.dto.CheckoutRequest
import com.esimko.mobile.data.remote.dto.CheckoutResponse
import com.esimko.mobile.data.remote.dto.PurchaseHistoryResponse
import com.esimko.mobile.data.remote.dto.PurchaseDetailResponse
import com.esimko.mobile.data.remote.dto.ShoppingInstallmentResponse
import com.esimko.mobile.data.remote.dto.ReturnResponse
import com.esimko.mobile.data.remote.dto.CancelPurchaseRequest
import retrofit2.http.*

interface ShoppingApi {
    @GET("mobile/produk")
    suspend fun getProducts(
        @Query("page") page: Int,
        @Query("per_page") perPage: Int = 20
    ): ApiResponse<List<ProductResponse>>

    @GET("mobile/produk/detail")
    suspend fun getProductDetail(@Query("id") id: String): ApiResponse<ProductDetailResponse>

    @GET("mobile/belanja/keranjang")
    suspend fun getCart(): ApiResponse<CartResponse>

    @POST("mobile/belanja/keranjang/proses")
    suspend fun updateCart(@Body request: CartRequest): ApiResponse<CartResponse>

    @POST("mobile/belanja/keranjang/checkout")
    suspend fun checkout(@Body request: CheckoutRequest): ApiResponse<CheckoutResponse>

    @POST("mobile/belanja/batalkan")
    suspend fun cancelPurchase(@Body request: CancelPurchaseRequest): ApiResponse<Any>

    @GET("mobile/belanja/riwayat/{jenis}")
    suspend fun getPurchaseHistory(
        @Path("jenis") jenis: String,
        @Query("page") page: Int? = null,
        @Query("per_page") perPage: Int? = null
    ): ApiResponse<List<PurchaseHistoryResponse>>

    @GET("mobile/belanja/riwayat/{jenis}/detail")
    suspend fun getPurchaseDetail(
        @Path("jenis") jenis: String,
        @Query("id") id: Long
    ): ApiResponse<PurchaseDetailResponse>

    @GET("mobile/belanja/angsuran")
    suspend fun getShoppingInstallments(): ApiResponse<List<ShoppingInstallmentResponse>>

    @GET("mobile/belanja/retur")
    suspend fun getReturns(): ApiResponse<List<ReturnResponse>>
}
