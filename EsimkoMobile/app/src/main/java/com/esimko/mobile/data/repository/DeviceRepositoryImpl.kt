package com.esimko.mobile.data.repository

import com.esimko.mobile.data.local.TokenStore
import com.esimko.mobile.data.remote.api.DeviceApi
import com.esimko.mobile.domain.repository.DeviceRepository
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.sync.Mutex
import kotlinx.coroutines.sync.withLock
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class DeviceRepositoryImpl @Inject constructor(
    private val api: DeviceApi,
    private val tokenStore: TokenStore
) : DeviceRepository {
    private val registrationMutex = Mutex()
    private var registered: Pair<Long, String>? = null

    override suspend fun registerToken(token: String, expectedSessionRevision: Long?): Boolean {
        if (token.isBlank()) return false
        val session = tokenStore.currentSession() ?: return false
        if (expectedSessionRevision != null && session.revision != expectedSessionRevision) return false
        // Serialize activity/service requests; only confirmed success is deduplicated.
        return registrationMutex.withLock {
            if (tokenStore.currentSession() != session) return@withLock false
            val key = session.revision to token
            if (registered == key) return@withLock true
            try {
                val response = api.register(token, session)
                if (response.success && tokenStore.currentSession() == session) {
                    registered = key
                    true
                } else {
                    false
                }
            } catch (e: CancellationException) {
                throw e
            } catch (_: Exception) {
                // Retry on next login/resume/new token, without blocking login.
                false
            }
        }
    }
}
