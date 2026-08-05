package com.esimko.mobile.data.remote.api

import com.esimko.mobile.data.remote.dto.ApiResponse
import com.esimko.mobile.data.remote.dto.ChangePasswordRequest
import com.esimko.mobile.data.remote.dto.ProfileResponse
import okhttp3.MultipartBody
import okhttp3.RequestBody
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.Multipart
import retrofit2.http.POST
import retrofit2.http.Part

interface ProfileApi {
    @GET("mobile/anggota/profil")
    suspend fun getProfile(): ApiResponse<ProfileResponse>

    @POST("mobile/anggota/ubah_password")
    suspend fun changePassword(@Body request: ChangePasswordRequest): ApiResponse<Any>

    @Multipart
    @POST("mobile/upload_avatar")
    suspend fun uploadAvatar(@Part avatar: MultipartBody.Part): ApiResponse<Any>
}
