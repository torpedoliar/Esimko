package com.esimko.mobile.data.remote.api

import com.esimko.mobile.data.remote.dto.ApiResponse
import com.esimko.mobile.data.remote.dto.LoginRequest
import com.esimko.mobile.data.remote.dto.LoginResponse
import com.esimko.mobile.data.remote.dto.RegisterRequest
import com.esimko.mobile.data.remote.dto.ProfileResponse
import retrofit2.http.Body
import retrofit2.http.POST
import retrofit2.http.Header

interface AuthApi {
    @POST("mobile/auth/login")
    suspend fun login(@Body request: LoginRequest): ApiResponse<LoginResponse>

    @POST("mobile/auth/logout")
    suspend fun logout(@Header("Authorization") authorization: String): ApiResponse<Any>

    @POST("mobile/auth/register")
    suspend fun register(@Body request: RegisterRequest): ApiResponse<ProfileResponse>
}
