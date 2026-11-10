package com.esimko.mobile.data.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.core.network.apiErrorMessage
import com.esimko.mobile.data.local.TokenStore
import com.esimko.mobile.data.remote.api.AuthApi
import com.esimko.mobile.data.remote.dto.LoginRequest
import com.esimko.mobile.data.remote.dto.RegisterRequest
import com.esimko.mobile.domain.model.User
import com.esimko.mobile.domain.repository.AuthRepository
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.NonCancellable
import kotlinx.coroutines.withContext
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class AuthRepositoryImpl @Inject constructor(
    private val api: AuthApi,
    private val tokenStore: TokenStore
) : AuthRepository {
    private var authAttempt = 0L

    override suspend fun login(username: String, password: String): Result<User> {
        val (revision, attempt) = synchronized(tokenStore) {
            tokenStore.sessionRevision to ++authAttempt
        }
        return try {
            val response = api.login(LoginRequest(username, password))
            val data = response.data
            if (response.success && data != null) {
                val token = data.token
                val noAnggota = data.no_anggota
                if (token.isNullOrBlank() || noAnggota.isNullOrBlank()) {
                    Result.Error(data.msg ?: "Login gagal")
                } else if (!synchronized(tokenStore) {
                    attempt == authAttempt && tokenStore.saveSessionIfCurrent(revision, token, noAnggota)
                }) {
                    Result.Error("Sesi berubah. Silakan masuk kembali.")
                } else {
                    Result.Success(User(noAnggota, data.nama.orEmpty(), token, data.avatar))
                }
            } else {
                Result.Error(response.message ?: "Login gagal")
            }
        } catch (e: CancellationException) {
            throw e
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Network error"))
        }
    }

    override suspend fun register(
        noKtp: String,
        telepon: String,
        password: String,
        nama: String
    ): Result<User> {
        return try {
            val response = api.register(
                RegisterRequest(
                    namaLengkap = nama,
                    noKtp = noKtp,
                    noHandphone = telepon,
                    password = password,
                    ulangiPassword = password
                )
            )
            if (response.success) {
                Result.Success(User("", nama, "", null))
            } else {
                Result.Error(response.message ?: "Registrasi gagal")
            }
        } catch (e: CancellationException) {
            throw e
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Network error"))
        }
    }

    override suspend fun logout(): Result<Unit> {
        val session = synchronized(tokenStore) {
            authAttempt++
            val current = tokenStore.currentSession()
            // End the local session immediately, including a pending login with no saved token yet.
            tokenStore.clear()
            current
        }
        if (session != null) {
            // Local clear may immediately remove the profile/login screen and cancel its VM.
            // Finish this already-requested revocation (bounded by the HTTP client's timeout).
            withContext(NonCancellable) {
                try {
                    api.logout("Bearer ${session.token}")
                } catch (e: CancellationException) {
                    throw e
                } catch (_: Exception) {
                    // Local logout remains successful offline; never clear a later login.
                }
            }
        }
        return Result.Success(Unit)
    }

    override fun isLoggedIn(): Boolean = tokenStore.isLoggedIn()
}
