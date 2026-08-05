package com.esimko.mobile.data.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.data.remote.api.VersionApi
import com.esimko.mobile.domain.model.Version
import com.esimko.mobile.domain.model.VersionCheck
import com.esimko.mobile.domain.repository.VersionRepository
import javax.inject.Inject

class VersionRepositoryImpl @Inject constructor(
    private val api: VersionApi
) : VersionRepository {

    override suspend fun getVersion(): Result<Version> {
        return try {
            val response = api.getVersion()
            if (response.success && response.data != null) {
                val dto = response.data
                Result.Success(Version(
                    version = dto.version.orEmpty(),
                    build = dto.build ?: 0,
                    minBuild = dto.minBuild ?: 0,
                    url = dto.url
                ))
            } else {
                Result.Error(response.message ?: "Failed to load version")
            }
        } catch (e: Exception) {
            Result.Error(e.message ?: "Network error")
        }
    }

    override suspend fun checkVersion(): Result<VersionCheck> {
        return try {
            val response = api.checkVersion(com.esimko.mobile.BuildConfig.VERSION_NAME)
            if (response.success && response.data != null) {
                val dto = response.data
                Result.Success(VersionCheck(
                    updateAvailable = dto.updateAvailable ?: false,
                    forceUpdate = dto.forceUpdate ?: false,
                    message = dto.message,
                    url = dto.url
                ))
            } else {
                Result.Error(response.message ?: "Failed to check version")
            }
        } catch (e: Exception) {
            Result.Error(e.message ?: "Network error")
        }
    }
}
