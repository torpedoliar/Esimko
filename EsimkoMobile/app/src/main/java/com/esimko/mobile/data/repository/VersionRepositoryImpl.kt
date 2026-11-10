package com.esimko.mobile.data.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.core.network.apiErrorMessage
import com.esimko.mobile.data.remote.api.VersionApi
import com.esimko.mobile.domain.model.Version
import com.esimko.mobile.domain.model.VersionCheck
import com.esimko.mobile.domain.repository.VersionRepository
import javax.inject.Inject
import kotlinx.coroutines.CancellationException

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
        } catch (e: CancellationException) {
            throw e
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Network error"))
        }
    }

    override suspend fun checkVersion(): Result<VersionCheck> {
        return try {
            val response = api.checkVersion(com.esimko.mobile.BuildConfig.VERSION_NAME)
            if (response.success && response.data != null) {
                val dto = response.data
                Result.Success(VersionCheck(
                    // Backend compares names only; Android also permits same-name build updates.
                    updateAvailable = dto.updateAvailable == true || dto.forceUpdate == true ||
                        (dto.latestVersionCode ?: 0) > com.esimko.mobile.BuildConfig.VERSION_CODE,
                    forceUpdate = dto.forceUpdate ?: false,
                    message = dto.message,
                    url = dto.url,
                    latestVersion = dto.latestVersion,
                    latestVersionCode = dto.latestVersionCode ?: 0,
                    apkUrl = dto.apkUrl,
                    apkSha256 = dto.apkSha256,
                    apkUkuran = dto.apkUkuran ?: 0L
                ))
            } else {
                Result.Error(response.message ?: "Failed to check version")
            }
        } catch (e: CancellationException) {
            throw e
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Network error"))
        }
    }
}
