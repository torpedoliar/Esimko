package com.esimko.mobile.data.remote.api

import com.esimko.mobile.data.remote.dto.ApiResponse
import com.esimko.mobile.data.remote.dto.DeviceRegisterResponse
import com.esimko.mobile.data.local.TokenStore
import retrofit2.http.Tag
import retrofit2.http.Field
import retrofit2.http.FormUrlEncoded
import retrofit2.http.POST

interface DeviceApi {
    @FormUrlEncoded
    @POST("mobile/device/register")
    suspend fun register(
        @Field("token") token: String,
        @Tag session: TokenStore.Session,
        @Field("platform") platform: String = "android"
    ): ApiResponse<DeviceRegisterResponse>
}