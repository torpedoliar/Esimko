package com.esimko.mobile.ui.auth.login

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.User
import com.esimko.mobile.domain.repository.AuthRepository
import io.mockk.coEvery
import io.mockk.mockk
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.ExperimentalCoroutinesApi
import kotlinx.coroutines.test.StandardTestDispatcher
import kotlinx.coroutines.test.resetMain
import kotlinx.coroutines.test.runTest
import kotlinx.coroutines.test.setMain
import org.junit.After
import org.junit.Before
import org.junit.Test
import com.google.common.truth.Truth.assertThat

@OptIn(ExperimentalCoroutinesApi::class)
class LoginViewModelTest {

    private lateinit var viewModel: LoginViewModel
    private lateinit var authRepository: AuthRepository
    private val testDispatcher = StandardTestDispatcher()

    @Before
    fun setup() {
        Dispatchers.setMain(testDispatcher)
        authRepository = mockk()
        viewModel = LoginViewModel(authRepository)
    }

    @After
    fun tearDown() {
        Dispatchers.resetMain()
    }

    @Test
    fun `initial state is correct`() {
        assertThat(viewModel.uiState.username).isEmpty()
        assertThat(viewModel.uiState.password).isEmpty()
        assertThat(viewModel.uiState.isLoading).isFalse()
        assertThat(viewModel.uiState.error).isNull()
        assertThat(viewModel.uiState.isLoggedIn).isFalse()
    }

    @Test
    fun `onUsernameChange updates username`() {
        viewModel.onUsernameChange("12345")
        assertThat(viewModel.uiState.username).isEqualTo("12345")
    }

    @Test
    fun `onPasswordChange updates password`() {
        viewModel.onPasswordChange("password123")
        assertThat(viewModel.uiState.password).isEqualTo("password123")
    }

    @Test
    fun `login with empty username shows error`() = runTest {
        viewModel.onUsernameChange("")
        viewModel.onPasswordChange("password")
        viewModel.login()

        assertThat(viewModel.uiState.error).isNotNull()
        assertThat(viewModel.uiState.error).contains("wajib diisi")
    }

    @Test
    fun `login with empty password shows error`() = runTest {
        viewModel.onUsernameChange("12345")
        viewModel.onPasswordChange("")
        viewModel.login()

        assertThat(viewModel.uiState.error).isNotNull()
        assertThat(viewModel.uiState.error).contains("wajib diisi")
    }

    @Test
    fun `login success sets isLoggedIn to true`() = runTest {
        val user = User("12345", "Test User", "token123", null)
        coEvery { authRepository.login("12345", "password") } returns Result.Success(user)

        viewModel.onUsernameChange("12345")
        viewModel.onPasswordChange("password")
        viewModel.login()
        testDispatcher.scheduler.advanceUntilIdle()

        assertThat(viewModel.uiState.isLoggedIn).isTrue()
        assertThat(viewModel.uiState.isLoading).isFalse()
    }

    @Test
    fun `login failure shows error message`() = runTest {
        coEvery { authRepository.login("12345", "wrong") } returns Result.Error("Login gagal")

        viewModel.onUsernameChange("12345")
        viewModel.onPasswordChange("wrong")
        viewModel.login()
        testDispatcher.scheduler.advanceUntilIdle()

        assertThat(viewModel.uiState.error).isEqualTo("Login gagal")
        assertThat(viewModel.uiState.isLoggedIn).isFalse()
    }
}
