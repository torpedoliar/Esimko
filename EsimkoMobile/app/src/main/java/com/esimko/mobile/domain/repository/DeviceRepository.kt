package com.esimko.mobile.domain.repository

interface DeviceRepository {
    /** True only when this token is registered for the current authenticated session. */
    suspend fun registerToken(token: String, expectedSessionRevision: Long? = null): Boolean
}