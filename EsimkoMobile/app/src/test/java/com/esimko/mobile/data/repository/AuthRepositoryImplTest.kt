package com.esimko.mobile.data.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.data.local.TokenStore
import com.esimko.mobile.data.remote.api.AuthApi
import com.esimko.mobile.data.remote.dto.ApiResponse
import com.esimko.mobile.data.remote.dto.LoginRequest
import com.esimko.mobile.data.remote.dto.LoginResponse
import io.mockk.coEvery
import io.mockk.coVerify
import io.mockk.every
import io.mockk.mockk
import io.mockk.mockkStatic
import kotlinx.coroutines.test.runTest
import com.google.common.truth.Truth.assertThat
import org.junit.Before
import org.junit.Test

class AuthRepositoryImplTest {

    private lateinit var repository: AuthRepositoryImpl
    private lateinit var authApi: AuthApi
    private lateinit var tokenStore: TokenStore

    @Before
    fun setup() {
        // android.util.Log throws "not mocked" in JVM unit tests unless stubbed
        mockkStatic(android.util.Log::class)
        every { android.util.Log.d(any(), any()) } returns 0
        every { android.util.Log.e(any(), any()) } returns 0
        every { android.util.Log.e(any(), any(), any()) } returns 0
        authApi = mockk()
        tokenStore = mockk(relaxed = true)
        repository = AuthRepositoryImpl(authApi, tokenStore)
    }

    @Test
    fun `login success saves token and returns user`() = runTest {
        val loginResponse = LoginResponse(
            token = "test_token",
            no_anggota = "12345",
            nama = "Test User",
            avatar = null
        )
        val apiResponse = ApiResponse(
            success = true,
            message = "Login berhasil",
            data = loginResponse,
            meta = null
        )

        coEvery { authApi.login(any()) } returns apiResponse

        val result = repository.login("12345", "password")

        assertThat(result).isInstanceOf(Result.Success::class.java)
        val user = (result as Result.Success).data
        assertThat(user.noAnggota).isEqualTo("12345")
        assertThat(user.nama).isEqualTo("Test User")
        assertThat(user.token).isEqualTo("test_token")

        coVerify { tokenStore.token = "test_token" }
        coVerify { tokenStore.noAnggota = "12345" }
    }

    @Test
    fun `login failure returns error`() = runTest {
        val apiResponse = ApiResponse<LoginResponse>(
            success = false,
            message = "Username atau password salah",
            data = null,
            meta = null
        )

        coEvery { authApi.login(any()) } returns apiResponse

        val result = repository.login("12345", "wrong")

        assertThat(result).isInstanceOf(Result.Error::class.java)
        assertThat((result as Result.Error).message).isEqualTo("Username atau password salah")
    }

    @Test
    fun `logout clears token store`() = runTest {
        coEvery { authApi.logout() } returns ApiResponse(success = true, message = "Logout berhasil", data = null, meta = null)
        repository.logout()
        coVerify { tokenStore.clear() }
    }

    @Test
    fun `isLoggedIn returns true when token exists`() {
        coEvery { tokenStore.isLoggedIn() } returns true
        assertThat(repository.isLoggedIn()).isTrue()
    }

    @Test
    fun `isLoggedIn returns false when no token`() {
        coEvery { tokenStore.isLoggedIn() } returns false
        assertThat(repository.isLoggedIn()).isFalse()
    }
}
