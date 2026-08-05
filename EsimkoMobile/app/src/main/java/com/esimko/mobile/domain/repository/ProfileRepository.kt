package com.esimko.mobile.domain.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.Profile
import okhttp3.MultipartBody

interface ProfileRepository {
    suspend fun getProfile(): Result<Profile>
    suspend fun changePassword(oldPassword: String, newPassword: String): Result<Unit>
    suspend fun uploadAvatar(avatar: MultipartBody.Part): Result<Unit>
    suspend fun logout()
}
