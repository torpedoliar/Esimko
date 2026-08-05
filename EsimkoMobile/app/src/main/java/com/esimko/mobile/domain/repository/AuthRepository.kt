package com.esimko.mobile.domain.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.User

interface AuthRepository {
    suspend fun login(username: String, password: String): Result<User>
    suspend fun register(noKtp: String, telepon: String, password: String, nama: String): Result<User>
    suspend fun logout(): Result<Unit>
    fun isLoggedIn(): Boolean
}
