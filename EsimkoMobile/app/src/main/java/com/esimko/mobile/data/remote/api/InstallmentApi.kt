package com.esimko.mobile.data.remote.api

import com.esimko.mobile.data.remote.dto.ApiResponse
import com.esimko.mobile.data.remote.dto.InstallmentResponse
import com.esimko.mobile.data.remote.dto.SalaryResponse
import retrofit2.http.GET

interface InstallmentApi {
    @GET("mobile/angsuran")
    suspend fun getLoanInstallments(): ApiResponse<List<InstallmentResponse>>

    @GET("mobile/gaji_pokok")
    suspend fun getBaseSalary(): ApiResponse<SalaryResponse>
}
