package com.esimko.mobile.data.remote.api

import com.esimko.mobile.data.remote.dto.ApiResponse
import com.esimko.mobile.data.remote.dto.TransactionTypeResponse
import com.esimko.mobile.data.remote.dto.TransactionStatusResponse
import retrofit2.http.GET
import retrofit2.http.Path

interface MasterApi {
    @GET("mobile/master/jenis_transaksi/{modul}")
    suspend fun getTransactionTypes(
        @Path("modul") modul: String
    ): ApiResponse<List<TransactionTypeResponse>>

    @GET("mobile/master/status_transaksi/{modul}")
    suspend fun getTransactionStatuses(
        @Path("modul") modul: String
    ): ApiResponse<List<TransactionStatusResponse>>
}
