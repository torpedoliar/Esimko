package com.esimko.mobile.data.remote.api

import com.esimko.mobile.data.remote.dto.ApiResponse
import com.esimko.mobile.data.remote.dto.TransactionResponse
import com.esimko.mobile.data.remote.dto.TransactionDetailResponse
import com.esimko.mobile.data.remote.dto.TransactionRequest
import com.esimko.mobile.data.remote.dto.CancelRequest
import okhttp3.MultipartBody
import okhttp3.RequestBody
import retrofit2.http.*

interface TransactionApi {
    @GET("mobile/transaksi/{modul}")
    suspend fun getTransactions(
        @Path("modul") modul: String,
        @Query("tanggal_mulai") tanggalMulai: String? = null,
        @Query("tanggal_akhir") tanggalAkhir: String? = null,
        @Query("page") page: Int? = null,
        @Query("per_page") perPage: Int? = null
    ): ApiResponse<List<TransactionResponse>>

    @GET("mobile/transaksi/{modul}/detail")
    suspend fun getTransactionDetail(
        @Path("modul") modul: String,
        @Query("id") id: Long
    ): ApiResponse<TransactionDetailResponse>

    @POST("mobile/transaksi/{jenis}/proses")
    suspend fun processTransaction(
        @Path("jenis") jenis: String,
        @Body request: TransactionRequest
    ): ApiResponse<TransactionResponse>

    @Multipart
    @POST("mobile/transaksi/upload_bukti_transaksi")
    suspend fun uploadTransactionProof(
        @Part("id") id: RequestBody,
        @Part bukti: MultipartBody.Part
    ): ApiResponse<Any>

    @POST("mobile/transaksi/batalkan")
    suspend fun cancelTransaction(@Body request: CancelRequest): ApiResponse<Any>
}
