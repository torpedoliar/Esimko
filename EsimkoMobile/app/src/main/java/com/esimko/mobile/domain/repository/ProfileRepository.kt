package com.esimko.mobile.domain.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.Profile
import com.esimko.mobile.domain.model.ProfileCacheStatus
import kotlinx.coroutines.flow.StateFlow
import okhttp3.MultipartBody

interface ProfileRepository {
    /** Session-bound memory snapshot; null immediately on session invalidation. */
    val profile: StateFlow<Profile?>
    val cacheStatus: StateFlow<ProfileCacheStatus>

    suspend fun getProfile(forceRefresh: Boolean = false): Result<Profile>
    suspend fun changePassword(oldPassword: String, newPassword: String): Result<Unit>
    suspend fun uploadAvatar(avatar: MultipartBody.Part): Result<Unit>
    suspend fun logout()
}
