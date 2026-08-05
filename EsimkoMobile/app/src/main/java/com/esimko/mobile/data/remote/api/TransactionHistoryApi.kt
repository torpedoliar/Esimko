package com.esimko.mobile.data.remote.api

import com.esimko.mobile.data.remote.dto.ApiResponse
import com.esimko.mobile.data.remote.dto.TransactionHistoryResponse
import retrofit2.http.GET
import retrofit2.http.Query

interface TransactionHistoryApi {
    @GET("mobile/riwayat_transaksi")
    suspend fun getTransactionHistory(
        @Query("id") id: Long,
        @Query("jenis") jenis: String
    ): ApiResponse<List<TransactionHistoryResponse>>
}
