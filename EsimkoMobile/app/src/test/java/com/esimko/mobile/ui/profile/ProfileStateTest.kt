package com.esimko.mobile.ui.profile

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.Profile
import com.esimko.mobile.domain.repository.ProfileRepository
import com.esimko.mobile.ui.common.UiState
import com.esimko.mobile.ui.dashboard.DashboardViewModel
import com.google.common.truth.Truth.assertThat
import io.mockk.*
import kotlinx.coroutines.*
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.test.*
import org.junit.After
import org.junit.Before
import org.junit.Test

@OptIn(ExperimentalCoroutinesApi::class)
class ProfileStateTest {
    private val dispatcher = StandardTestDispatcher()
    private val snapshot = Profile("A", "Member", "", "", "", null, null, 100, 0, 0)
    private val profiles = MutableStateFlow<Profile?>(null)
    private val repository = mockk<ProfileRepository>()

    @Before fun setup() {
        Dispatchers.setMain(dispatcher)
        every { repository.profile } returns profiles
        coEvery { repository.getProfile(any()) } coAnswers {
            profiles.value = snapshot
            Result.Success(snapshot)
        }
    }

    @After fun cleanup() { Dispatchers.resetMain() }

    @Test fun `profile clears invalidated data and shows refresh failures`() = runTest(dispatcher) {
        val model = ProfileViewModel(repository)
        runCurrent()
        assertThat(model.uiState.value).isInstanceOf(UiState.Success::class.java)
        profiles.value = null
        runCurrent()
        assertThat(model.uiState.value).isNotInstanceOf(UiState.Success::class.java)
        coEvery { repository.getProfile(any()) } returns Result.Error("Offline")
        model.loadProfile(true)
        runCurrent()
        assertThat((model.uiState.value as UiState.Error).message).isEqualTo("Offline")
    }

    @Test fun `profile suppresses duplicate loads`() = runTest(dispatcher) {
        val release = CompletableDeferred<Unit>()
        coEvery { repository.getProfile(any()) } coAnswers {
            release.await()
            Result.Error("Offline")
        }
        val model = ProfileViewModel(repository)
        model.loadProfile()
        runCurrent()
        model.loadProfile(true)
        runCurrent()
        coVerify(exactly = 1) { repository.getProfile(any()) }
        release.complete(Unit)
        runCurrent()
    }

    @Test fun `dashboard clears invalidation and does not retain failed balances`() = runTest(dispatcher) {
        val model = DashboardViewModel(repository, mockk(relaxed = true), mockk(relaxed = true))
        runCurrent()
        assertThat(model.state.value.profile).isEqualTo(snapshot)
        profiles.value = null
        runCurrent()
        assertThat(model.state.value.profile).isNull()
        coEvery { repository.getProfile(any()) } returns Result.Error("Offline")
        model.load()
        runCurrent()
        assertThat(model.state.value.profile).isNull()
        assertThat(model.state.value.error).isEqualTo("Offline")
        assertThat(model.state.value.isLoading).isFalse()
        coVerify { repository.getProfile(true) }
    }
}
