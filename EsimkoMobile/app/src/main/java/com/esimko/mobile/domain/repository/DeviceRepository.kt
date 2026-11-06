package com.esimko.mobile.domain.repository

interface DeviceRepository {
    suspend fun registerToken(token: String)
}