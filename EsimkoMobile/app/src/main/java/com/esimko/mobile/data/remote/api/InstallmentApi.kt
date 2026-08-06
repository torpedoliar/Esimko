package com.esimko.mobile.data.remote.api

import com.esimko.mobile.data.remote.dto.ApiResponse
import com.esimko.mobile.data.remote.dto.InstallmentResponse
import com.esimko.mobile.data.remote.dto.SalaryResponse
import com.esimko.mobile.data.remote.dto.TransactionRequest
import com.esimko.mobile.data.remote.dto.TransactionResponse
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.Path

interface InstallmentApi {
    @GET("mobile/angsuran")
    suspend fun getLoanInstallments(): ApiResponse<List<InstallmentResponse>>

    @GET("mobile/gaji_pokok")
    suspend fun getBaseSalary(): ApiResponse<SalaryResponse>

    @POST("mobile/transaksi/{jenis}/proses")
    suspend fun submitLoan(
        @Path("jenis") jenis: String,
        @Body request: TransactionRequest
    ): ApiResponse<TransactionResponse>
}
