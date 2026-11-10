package com.esimko.mobile

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.data.local.TokenStore
import com.esimko.mobile.domain.repository.AuthRepository
import com.google.common.truth.Truth.assertThat
import io.mockk.coEvery
import io.mockk.every
import io.mockk.mockk
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.ExperimentalCoroutinesApi
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.test.StandardTestDispatcher
import kotlinx.coroutines.test.resetMain
import kotlinx.coroutines.test.runTest
import kotlinx.coroutines.test.setMain
import org.junit.After
import org.junit.Before
import org.junit.Test

@OptIn(ExperimentalCoroutinesApi::class)
class MainViewModelTest {
    private val dispatcher = StandardTestDispatcher()
    private val store = mockk<TokenStore>()
    private val auth = mockk<AuthRepository>()
    private val changes = MutableStateFlow(0L)
    private var loggedIn = false

    @Before
    fun setup() {
        Dispatchers.setMain(dispatcher)
        every { store.sessionChanges } returns changes.asStateFlow()
        every { store.isLoggedIn() } answers { loggedIn }
    }

    @After
    fun cleanup() { Dispatchers.resetMain() }

    @Test
    fun `login and 401 session changes update activity state without recreating view model`() = runTest {
        val vm = MainViewModel(auth, store)
        dispatcher.scheduler.runCurrent()
        assertThat(vm.isLoggedIn.value).isFalse()
        loggedIn = true
        changes.value = 1
        dispatcher.scheduler.runCurrent()
        assertThat(vm.isLoggedIn.value).isTrue()
        loggedIn = false
        changes.value = 2
        dispatcher.scheduler.runCurrent()
        assertThat(vm.isLoggedIn.value).isFalse()
    }

    @Test
    fun `late logout completion cannot hide newer login`() = runTest {
        coEvery { auth.logout() } coAnswers {
            loggedIn = false
            changes.value = 1
            kotlinx.coroutines.delay(100)
            loggedIn = true
            changes.value = 2
            Result.Success(Unit)
        }
        val vm = MainViewModel(auth, store)
        vm.logout()
        dispatcher.scheduler.advanceUntilIdle()
        assertThat(vm.isLoggedIn.value).isTrue()
    }
}
