package com.esimko.mobile.data.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.data.local.TokenStore
import com.esimko.mobile.data.remote.api.AuthApi
import com.esimko.mobile.data.remote.dto.ApiResponse
import com.esimko.mobile.data.remote.dto.LoginResponse
import com.google.common.truth.Truth.assertThat
import io.mockk.coEvery
import io.mockk.coVerify
import io.mockk.every
import io.mockk.mockk
import io.mockk.verify
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.CompletableDeferred
import kotlinx.coroutines.async
import kotlinx.coroutines.test.runCurrent
import kotlinx.coroutines.test.runTest
import org.junit.Before
import org.junit.Test

class AuthRepositoryImplTest {
    private lateinit var repository: AuthRepositoryImpl
    private lateinit var authApi: AuthApi
    private lateinit var tokenStore: TokenStore

    @Before
    fun setup() {
        authApi = mockk()
        tokenStore = mockk(relaxed = true)
        every { tokenStore.saveSessionIfCurrent(any(), any(), any()) } returns true
        repository = AuthRepositoryImpl(authApi, tokenStore)
    }

    private fun loginResponse(token: String? = "test_token") = ApiResponse(
        success = true, message = "Login berhasil",
        data = LoginResponse(token = token, no_anggota = "12345", nama = "Test User", avatar = null), meta = null
    )

    @Test
    fun `login success saves token and member atomically`() = runTest {
        coEvery { authApi.login(any()) } returns loginResponse()
        val result = repository.login("12345", "password")
        assertThat(result).isInstanceOf(Result.Success::class.java)
        assertThat((result as Result.Success).data.noAnggota).isEqualTo("12345")
        verify { tokenStore.saveSessionIfCurrent(0L, "test_token", "12345") }
        verify(exactly = 0) { tokenStore.token = any() }
        verify(exactly = 0) { tokenStore.noAnggota = any() }
    }

    @Test
    fun `login failure preserves existing session`() = runTest {
        coEvery { authApi.login(any()) } returns ApiResponse(false, "Wrong password", null, null)
        assertThat(repository.login("12345", "wrong")).isInstanceOf(Result.Error::class.java)
        verify(exactly = 0) { tokenStore.clear() }
        verify(exactly = 0) { tokenStore.saveSessionIfCurrent(any(), any(), any()) }
    }

    @Test
    fun `malformed login cannot save blank credentials`() = runTest {
        coEvery { authApi.login(any()) } returns loginResponse(" ")
        assertThat(repository.login("12345", "password")).isInstanceOf(Result.Error::class.java)
        verify(exactly = 0) { tokenStore.saveSessionIfCurrent(any(), any(), any()) }
    }

    @Test
    fun `late login cannot overwrite a changed session`() = runTest {
        coEvery { authApi.login(any()) } returns loginResponse()
        every { tokenStore.saveSessionIfCurrent(any(), any(), any()) } returns false
        assertThat(repository.login("12345", "password")).isInstanceOf(Result.Error::class.java)
    }

    @Test
    fun `earlier login response cannot win after a newer account login starts`() = runTest {
        val release = CompletableDeferred<Unit>()
        coEvery { authApi.login(match { it.username == "A" }) } coAnswers {
            release.await()
            loginResponse("token-a")
        }
        coEvery { authApi.login(match { it.username == "B" }) } returns loginResponse("token-b")
        val first = async { repository.login("A", "password") }
        runCurrent()
        assertThat(repository.login("B", "password")).isInstanceOf(Result.Success::class.java)
        release.complete(Unit)
        assertThat(first.await()).isInstanceOf(Result.Error::class.java)
        verify(exactly = 0) { tokenStore.saveSessionIfCurrent(any(), "token-a", any()) }
    }

    @Test
    fun `logout prevents already pending login from resurrecting session`() = runTest {
        val release = CompletableDeferred<Unit>()
        coEvery { authApi.login(any()) } coAnswers {
            release.await()
            loginResponse()
        }
        every { tokenStore.currentSession() } returns null
        val pending = async { repository.login("A", "password") }
        runCurrent()
        repository.logout()
        release.complete(Unit)
        assertThat(pending.await()).isInstanceOf(Result.Error::class.java)
        verify(exactly = 0) { tokenStore.saveSessionIfCurrent(any(), any(), any()) }
    }

    @Test
    fun `logout clears locally before request and binds old bearer`() = runTest {
        val oldSession = TokenStore.Session("account-a", "A", 4)
        every { tokenStore.currentSession() } returns oldSession
        var cleared = false
        every { tokenStore.clear() } answers { cleared = true }
        coEvery { authApi.logout("Bearer account-a") } coAnswers {
            assertThat(cleared).isTrue()
            ApiResponse(true, "Logout", null, null)
        }
        assertThat(repository.logout()).isInstanceOf(Result.Success::class.java)
        coVerify { authApi.logout("Bearer account-a") }
        verify(exactly = 1) { tokenStore.clear() }
    }

    @Test
    fun `late failed logout cannot clear replacement session`() = runTest {
        every { tokenStore.currentSession() } returns TokenStore.Session("a", "A", 1)
        val release = CompletableDeferred<Unit>()
        coEvery { authApi.logout(any()) } coAnswers {
            release.await()
            throw java.io.IOException("offline")
        }
        val result = async { repository.logout() }
        runCurrent()
        verify(exactly = 1) { tokenStore.clear() }
        every { tokenStore.currentSession() } returns TokenStore.Session("b", "B", 3)
        release.complete(Unit)
        assertThat(result.await()).isInstanceOf(Result.Success::class.java)
        verify(exactly = 1) { tokenStore.clear() }
    }

    @Test
    fun `screen cancellation after local clear does not cancel captured bearer revocation`() = runTest {
        every { tokenStore.currentSession() } returns TokenStore.Session("a", "A", 1)
        val release = CompletableDeferred<Unit>()
        var revoked = false
        coEvery { authApi.logout("Bearer a") } coAnswers {
            release.await()
            revoked = true
            ApiResponse(true, null, null, null)
        }
        val pending = async { repository.logout() }
        runCurrent()
        pending.cancel()
        runCurrent()
        assertThat(revoked).isFalse()
        release.complete(Unit)
        pending.join()
        assertThat(revoked).isTrue()
        verify(exactly = 1) { tokenStore.clear() }
    }

    @Test
    fun `logout with no credentials still invalidates pending login without server call`() = runTest {
        every { tokenStore.currentSession() } returns null
        repository.logout()
        verify { tokenStore.clear() }
        coVerify(exactly = 0) { authApi.logout(any()) }
    }

    @Test
    fun `cancellation is not converted into login error`() = runTest {
        coEvery { authApi.login(any()) } throws CancellationException("cancelled")
        var cancelled = false
        try { repository.login("12345", "password") } catch (_: CancellationException) { cancelled = true }
        assertThat(cancelled).isTrue()
        verify(exactly = 0) { tokenStore.saveSessionIfCurrent(any(), any(), any()) }
    }

    @Test
    fun `isLoggedIn delegates to current credentials`() {
        every { tokenStore.isLoggedIn() } returns true
        assertThat(repository.isLoggedIn()).isTrue()
        every { tokenStore.isLoggedIn() } returns false
        assertThat(repository.isLoggedIn()).isFalse()
    }
}
