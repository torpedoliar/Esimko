package com.esimko.mobile.data.repository

import android.util.Log
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.data.local.TokenStore
import com.esimko.mobile.data.remote.api.AuthApi
import com.esimko.mobile.data.remote.dto.LoginRequest
import com.esimko.mobile.data.remote.dto.RegisterRequest
import com.esimko.mobile.domain.model.User
import com.esimko.mobile.domain.repository.AuthRepository
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class AuthRepositoryImpl @Inject constructor(
    private val api: AuthApi,
    private val tokenStore: TokenStore
) : AuthRepository {

    override suspend fun login(username: String, password: String): Result<User> {
        return try {
            Log.d("AuthRepository", "Attempting login for user: $username")
            val response = api.login(LoginRequest(username, password))
            Log.d("AuthRepository", "Login response: success=${response.success}, message=${response.message}")

            if (response.success && response.data != null) {
                val data = response.data
                val token = data.token
                val noAnggota = data.no_anggota
                Log.d("AuthRepository", "Token received: ${token?.take(10)}..., no_anggota: $noAnggota")

                // Check if login actually succeeded (token exists)
                if (token.isNullOrEmpty() || noAnggota.isNullOrEmpty()) {
                    // Login failed - backend returned error message
                    val errorMsg = data.msg ?: "Login gagal"
                    Log.e("AuthRepository", "Login failed: $errorMsg")
                    Result.Error(errorMsg)
                } else {
                    tokenStore.token = token
                    tokenStore.noAnggota = noAnggota
                    Log.d("AuthRepository", "Token saved to TokenStore successfully")
                    Result.Success(User(noAnggota, data.nama.orEmpty(), token, data.avatar))
                }
            } else {
                Log.e("AuthRepository", "Login failed: ${response.message}")
                Result.Error(response.message ?: "Login gagal")
            }
        } catch (e: Exception) {
            Log.e("AuthRepository", "Login exception: ${e.message}", e)
            Result.Error(e.message ?: "Network error")
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
        } catch (e: Exception) {
            Result.Error(e.message ?: "Network error")
        }
    }

    override suspend fun logout(): Result<Unit> {
        return try {
            val response = api.logout()
            tokenStore.clear()
            if (response.success) {
                Result.Success(Unit)
            } else {
                Result.Error(response.message ?: "Logout failed")
            }
        } catch (e: Exception) {
            tokenStore.clear()
            Result.Success(Unit)
        }
    }

    override fun isLoggedIn(): Boolean = tokenStore.isLoggedIn()
}
