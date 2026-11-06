package com.esimko.mobile.data.remote.api

import com.esimko.mobile.data.remote.dto.ApiResponse
import com.esimko.mobile.data.remote.dto.DeviceRegisterResponse
import retrofit2.http.Field
import retrofit2.http.FormUrlEncoded
import retrofit2.http.POST

interface DeviceApi {
    @FormUrlEncoded
    @POST("mobile/device/register")
    suspend fun register(
        @Field("token") token: String,
        @Field("platform") platform: String = "android"
    ): ApiResponse<DeviceRegisterResponse>
}