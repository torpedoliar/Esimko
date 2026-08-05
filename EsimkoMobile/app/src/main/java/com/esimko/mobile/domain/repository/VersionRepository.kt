package com.esimko.mobile.domain.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.Version
import com.esimko.mobile.domain.model.VersionCheck

interface VersionRepository {
    suspend fun getVersion(): Result<Version>
    suspend fun checkVersion(): Result<VersionCheck>
}
