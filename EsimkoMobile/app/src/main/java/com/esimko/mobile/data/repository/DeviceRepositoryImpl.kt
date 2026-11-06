package com.esimko.mobile.data.repository

import com.esimko.mobile.data.remote.api.DeviceApi
import com.esimko.mobile.domain.repository.DeviceRepository
import javax.inject.Inject

class DeviceRepositoryImpl @Inject constructor(
    private val api: DeviceApi
) : DeviceRepository {
    override suspend fun registerToken(token: String) {
        try {
            api.register(token)
        } catch (_: Exception) {
            // Gagal register token tidak menggagalkan alur login — coba lagi token berikutnya.
        }
    }
}