package com.esimko.mobile.data.repository

import com.esimko.mobile.BuildConfig
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.data.remote.api.VersionApi
import com.esimko.mobile.data.remote.dto.ApiResponse
import com.esimko.mobile.data.remote.dto.VersionCheckResponse
import io.mockk.coEvery
import io.mockk.mockk
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.test.runTest
import org.junit.Assert.*
import org.junit.Test

class VersionRepositoryImplTest {
    private val api = mockk<VersionApi>()
    private val repository = VersionRepositoryImpl(api)

    @Test fun `higher Android code offers same name rebuild`() = runTest {
        coEvery { api.checkVersion(any()) } returns ApiResponse(true, null,
            VersionCheckResponse(updateAvailable = false, latestVersion = BuildConfig.VERSION_NAME,
                latestVersionCode = BuildConfig.VERSION_CODE + 1), null)
        val result = repository.checkVersion() as Result.Success
        assertTrue(result.data.updateAvailable)
    }

    @Test fun `force update still offers update when server flags disagree`() = runTest {
        coEvery { api.checkVersion(any()) } returns ApiResponse(true, null,
            VersionCheckResponse(updateAvailable = false, forceUpdate = true), null)
        val result = repository.checkVersion() as Result.Success
        assertTrue(result.data.forceUpdate)
        assertTrue(result.data.updateAvailable)
    }

    @Test fun `both version endpoints preserve cancellation`() = runTest {
        coEvery { api.checkVersion(any()) } throws CancellationException("cancelled")
        coEvery { api.getVersion() } throws CancellationException("cancelled")
        try { repository.checkVersion(); fail("Must cancel") } catch (_: CancellationException) { }
        try { repository.getVersion(); fail("Must cancel") } catch (_: CancellationException) { }
    }
}
