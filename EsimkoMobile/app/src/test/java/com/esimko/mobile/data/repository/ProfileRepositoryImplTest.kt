package com.esimko.mobile.data.repository

import android.content.Context
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.data.local.TokenStore
import com.esimko.mobile.data.remote.api.ProfileApi
import com.esimko.mobile.data.remote.dto.ApiResponse
import com.esimko.mobile.data.remote.dto.ProfileResponse
import com.esimko.mobile.domain.repository.AuthRepository
import com.google.common.truth.Truth.assertThat
import io.mockk.*
import kotlinx.coroutines.*
import org.junit.Before
import org.junit.Rule
import org.junit.Test
import org.junit.rules.TemporaryFolder

class ProfileRepositoryImplTest {
    @get:Rule val folder = TemporaryFolder()
    private val api = mockk<ProfileApi>()
    private val auth = mockk<AuthRepository>()
    private val tokens = mockk<TokenStore>(relaxed = true)
    private val context = mockk<Context>()
    private var revision = 0L
    private var owner: String? = "A"
    private var token: String? = "token-A"
    private lateinit var invalidate: () -> Unit
    private lateinit var repository: ProfileRepositoryImpl

    @Before fun setup() {
        every { context.filesDir } returns folder.root
        every { tokens.sessionRevision } answers { revision }
        every { tokens.noAnggota } answers { owner }
        every { tokens.token } answers { token }
        every { tokens.addSessionListener(any()) } answers { invalidate = firstArg() }
        every { tokens.clear() } answers {
            revision++
            token = null
            owner = null
            invalidate()
        }
        coEvery { api.getProfile() } returns response()
        coEvery { auth.logout() } coAnswers {
            tokens.clear()
            Result.Success(Unit)
        }
        repository = ProfileRepositoryImpl(api, tokens, context, auth)
    }

    private fun response(member: String = "A") = ApiResponse(
        success = true, message = null,
        data = ProfileResponse(noAnggota = member, namaLengkap = "Member $member"), meta = null
    )

    @Test fun `fresh memory reused and plaintext file deleted never loaded`() = runBlocking {
        val legacy = folder.newFile("profile_cache.json")
        legacy.writeText("sensitive legacy data")
        assertThat(repository.getProfile(false)).isInstanceOf(Result.Success::class.java)
        repository.getProfile(false)
        coVerify(exactly = 1) { api.getProfile() }
        assertThat(legacy.exists()).isFalse()
        assertThat(repository.profile.value?.noAnggota).isEqualTo("A")
    }

    @Test fun `missing owner rejects cache and network`() = runBlocking {
        repository.getProfile(false)
        owner = null
        assertThat(repository.getProfile(false)).isInstanceOf(Result.Error::class.java)
        assertThat(repository.profile.value).isNull()
        coVerify(exactly = 1) { api.getProfile() }
    }

    @Test fun `logout clears memory and rejects in flight response`() = runBlocking {
        val started = CompletableDeferred<Unit>()
        val release = CompletableDeferred<Unit>()
        coEvery { api.getProfile() } coAnswers {
            started.complete(Unit)
            release.await()
            response()
        }
        val request = async { repository.getProfile(true) }
        started.await()
        repository.logout()
        release.complete(Unit)
        assertThat(request.await()).isInstanceOf(Result.Error::class.java)
        assertThat(repository.profile.value).isNull()
    }

    @Test fun `logout delegates before credentials disappear and removes legacy file`() = runBlocking {
        val legacy = folder.newFile("profile_cache.json")
        legacy.writeText("private cache")
        coEvery { auth.logout() } coAnswers {
            assertThat(token).isEqualTo("token-A")
            tokens.clear()
            Result.Success(Unit)
        }
        repository.logout()
        coVerify(exactly = 1) { auth.logout() }
        verify(exactly = 1) { tokens.clear() }
        assertThat(legacy.exists()).isFalse()
    }

    @Test fun `logout cleanup survives cancellation from auth repository`() = runBlocking {
        val legacy = folder.newFile("profile_cache.json")
        coEvery { auth.logout() } coAnswers {
            tokens.clear()
            throw CancellationException("screen removed")
        }
        var cancelled = false
        try { repository.logout() } catch (_: CancellationException) { cancelled = true }
        assertThat(cancelled).isTrue()
        assertThat(legacy.exists()).isFalse()
    }

    @Test fun `account change invalidates memory and rejects previous response`() = runBlocking {
        repository.getProfile(false)
        revision++
        owner = "B"
        token = "token-B"
        invalidate()
        assertThat(repository.profile.value).isNull()
        // Even a successful server response with the wrong owner must not be published.
        assertThat(repository.getProfile(false)).isInstanceOf(Result.Error::class.java)
        assertThat(repository.profile.value).isNull()
    }

    @Test fun `overlapping forced refreshes share one request`() = runBlocking {
        val started = CompletableDeferred<Unit>()
        val secondObserved = CompletableDeferred<Unit>()
        val release = CompletableDeferred<Unit>()
        var reads = 0
        every { tokens.sessionRevision } answers {
            reads++
            if (reads == 3) secondObserved.complete(Unit)
            revision
        }
        coEvery { api.getProfile() } coAnswers {
            started.complete(Unit)
            release.await()
            response()
        }
        val first = async { repository.getProfile(true) }
        started.await()
        val second = async { repository.getProfile(true) }
        secondObserved.await()
        release.complete(Unit)
        assertThat(first.await()).isInstanceOf(Result.Success::class.java)
        assertThat(second.await()).isInstanceOf(Result.Success::class.java)
        coVerify(exactly = 1) { api.getProfile() }
    }

    @Test fun `401 invalidates session and profile`() = runBlocking {
        repository.getProfile(false)
        every { tokens.clearIfCurrent(any()) } answers {
            if (firstArg<Long>() == revision) {
                tokens.clear()
                true
            } else false
        }
        coEvery { api.getProfile() } throws retrofit2.HttpException(
            retrofit2.Response.error<Any>(401, okhttp3.ResponseBody.create(null, "Unauthorized"))
        )
        assertThat(repository.getProfile(true)).isInstanceOf(Result.Error::class.java)
        assertThat(repository.profile.value).isNull()
        assertThat(token).isNull()
    }

    @Test fun `refresh failure hides financial snapshot`() = runBlocking {
        repository.getProfile(false)
        coEvery { api.getProfile() } returns ApiResponse<ProfileResponse>(false, "Offline", null, null)
        assertThat(repository.getProfile(true)).isInstanceOf(Result.Error::class.java)
        assertThat(repository.profile.value).isNull()
    }

    @Test fun `cancellation propagates from profile password and avatar`() = runBlocking {
        coEvery { api.getProfile() } throws CancellationException("cancelled")
        coEvery { api.changePassword(any()) } throws CancellationException("cancelled")
        coEvery { api.uploadAvatar(any()) } throws CancellationException("cancelled")
        val calls: List<suspend () -> Any> = listOf(
            { repository.getProfile(true) },
            { repository.changePassword("old", "new") },
            { repository.uploadAvatar(mockk()) }
        )
        for (call in calls) {
            var cancelled = false
            try { call() } catch (_: CancellationException) { cancelled = true }
            assertThat(cancelled).isTrue()
        }
        assertThat(repository.profile.value).isNull()
    }
}
