package com.esimko.mobile.data.repository

import com.esimko.mobile.data.local.TokenStore
import com.esimko.mobile.data.remote.api.DeviceApi
import com.esimko.mobile.data.remote.dto.ApiResponse
import com.google.common.truth.Truth.assertThat
import io.mockk.coEvery
import io.mockk.coVerify
import io.mockk.every
import io.mockk.mockk
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.CompletableDeferred
import kotlinx.coroutines.async
import kotlinx.coroutines.test.runCurrent
import kotlinx.coroutines.test.runTest
import org.junit.Before
import org.junit.Test

class DeviceRepositoryImplTest {
    private val api = mockk<DeviceApi>()
    private val store = mockk<TokenStore>()
    private val a = TokenStore.Session("auth-a", "A", 1)
    private val b = TokenStore.Session("auth-b", "B", 2)
    private lateinit var repository: DeviceRepositoryImpl

    @Before
    fun setup() {
        every { store.currentSession() } returns a
        repository = DeviceRepositoryImpl(api, store)
    }

    @Test
    fun `logged out and blank tokens never reach server`() = runTest {
        assertThat(repository.registerToken(" ")).isFalse()
        every { store.currentSession() } returns null
        assertThat(repository.registerToken("fcm")).isFalse()
        coVerify(exactly = 0) { api.register(any(), any(), any()) }
    }

    @Test
    fun `only successful response is deduplicated`() = runTest {
        coEvery { api.register("fcm", a, any()) } returnsMany listOf(
            ApiResponse(false, "retry", null, null), ApiResponse(true, "ok", null, null)
        )
        assertThat(repository.registerToken("fcm")).isFalse()
        assertThat(repository.registerToken("fcm")).isTrue()
        assertThat(repository.registerToken("fcm")).isTrue()
        coVerify(exactly = 2) { api.register("fcm", a, any()) }
    }

    @Test
    fun `network failure remains retryable`() = runTest {
        coEvery { api.register("fcm", a, any()) } throws java.io.IOException("offline")
        assertThat(repository.registerToken("fcm")).isFalse()
        coEvery { api.register("fcm", a, any()) } returns ApiResponse(true, null, null, null)
        assertThat(repository.registerToken("fcm")).isTrue()
        coVerify(exactly = 2) { api.register("fcm", a, any()) }
    }

    @Test
    fun `same FCM token is registered again for replacement session`() = runTest {
        coEvery { api.register(any(), any(), any()) } returns ApiResponse(true, null, null, null)
        assertThat(repository.registerToken("fcm")).isTrue()
        every { store.currentSession() } returns b
        assertThat(repository.registerToken("fcm")).isTrue()
        coVerify(exactly = 1) { api.register("fcm", a, any()) }
        coVerify(exactly = 1) { api.register("fcm", b, any()) }
    }

    @Test
    fun `late Firebase callback for old session never registers as replacement`() = runTest {
        every { store.currentSession() } returns b
        assertThat(repository.registerToken("fcm", a.revision)).isFalse()
        coVerify(exactly = 0) { api.register(any(), any(), any()) }
    }

    @Test
    fun `concurrent registration serialized and stale success never marks replacement complete`() = runTest {
        val release = CompletableDeferred<Unit>()
        coEvery { api.register("fcm", a, any()) } coAnswers {
            release.await()
            ApiResponse(true, null, null, null)
        }
        coEvery { api.register("fcm", b, any()) } returns ApiResponse(true, null, null, null)
        val first = async { repository.registerToken("fcm") }
        runCurrent()
        every { store.currentSession() } returns b
        val second = async { repository.registerToken("fcm") }
        runCurrent()
        release.complete(Unit)
        assertThat(first.await()).isFalse()
        assertThat(second.await()).isTrue()
        coVerify(exactly = 1) { api.register("fcm", b, any()) }
    }

    @Test
    fun `activity and service duplicate calls send once`() = runTest {
        val release = CompletableDeferred<Unit>()
        coEvery { api.register("fcm", a, any()) } coAnswers {
            release.await()
            ApiResponse(true, null, null, null)
        }
        val first = async { repository.registerToken("fcm") }
        val second = async { repository.registerToken("fcm") }
        runCurrent()
        release.complete(Unit)
        assertThat(first.await()).isTrue()
        assertThat(second.await()).isTrue()
        coVerify(exactly = 1) { api.register("fcm", a, any()) }
    }

    @Test
    fun `cancellation is propagated without caching success`() = runTest {
        coEvery { api.register("fcm", a, any()) } throws CancellationException("cancelled")
        var cancelled = false
        try { repository.registerToken("fcm") } catch (_: CancellationException) { cancelled = true }
        assertThat(cancelled).isTrue()
        coEvery { api.register("fcm", a, any()) } returns ApiResponse(true, null, null, null)
        assertThat(repository.registerToken("fcm")).isTrue()
        coVerify(exactly = 2) { api.register("fcm", a, any()) }
    }
}
