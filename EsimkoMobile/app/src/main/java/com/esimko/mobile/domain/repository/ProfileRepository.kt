package com.esimko.mobile.domain.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.Profile
import kotlinx.coroutines.flow.StateFlow
import okhttp3.MultipartBody

interface ProfileRepository {
    /** Profil ter-cache yang ikut ter-update saat refresh latar selesai. */
    val profile: StateFlow<Profile?>

    suspend fun getProfile(forceRefresh: Boolean = false): Result<Profile>
    suspend fun changePassword(oldPassword: String, newPassword: String): Result<Unit>
    suspend fun uploadAvatar(avatar: MultipartBody.Part): Result<Unit>
    suspend fun logout()
}
