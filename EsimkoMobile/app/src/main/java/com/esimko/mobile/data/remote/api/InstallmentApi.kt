package com.esimko.mobile.data.remote.api

import com.esimko.mobile.data.remote.dto.ApiResponse
import com.esimko.mobile.data.remote.dto.InstallmentResponse
import com.esimko.mobile.data.remote.dto.SalaryResponse
import com.esimko.mobile.data.remote.dto.TransactionRequest
import com.esimko.mobile.data.remote.dto.TransactionResponse
import okhttp3.MultipartBody
import okhttp3.RequestBody
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.Multipart
import retrofit2.http.POST
import retrofit2.http.Part
import retrofit2.http.Path

interface InstallmentApi {
    @GET("mobile/angsuran")
    suspend fun getLoanInstallments(): ApiResponse<List<InstallmentResponse>>

    @GET("mobile/gaji_pokok")
    suspend fun getBaseSalary(): ApiResponse<SalaryResponse>

    @Multipart
    @POST("mobile/transaksi/{jenis}/proses")
    suspend fun submitLoan(
        @Path("jenis") jenis: String,
        @Part("action") action: RequestBody,
        @Part("nominal") nominal: RequestBody,
        @Part("tenor") tenor: RequestBody,
        @Part("jenis_pinjaman") jenisPinjaman: RequestBody,
        @Part("gaji_pokok") gajiPokok: RequestBody,
        @Part("keterangan") keterangan: RequestBody,
        @Part attachment: MultipartBody.Part?
    ): ApiResponse<TransactionResponse>
}
