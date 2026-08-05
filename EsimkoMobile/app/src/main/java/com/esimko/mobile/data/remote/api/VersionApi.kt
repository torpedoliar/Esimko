package com.esimko.mobile.data.remote.api

import com.esimko.mobile.data.remote.dto.ApiResponse
import com.esimko.mobile.data.remote.dto.VersionResponse
import com.esimko.mobile.data.remote.dto.VersionCheckResponse
import retrofit2.http.GET
import retrofit2.http.Query

interface VersionApi {
    @GET("version")
    suspend fun getVersion(): ApiResponse<VersionResponse>

    @GET("version/check")
    suspend fun checkVersion(@Query("version") version: String = "0.0.0"): ApiResponse<VersionCheckResponse>
}
