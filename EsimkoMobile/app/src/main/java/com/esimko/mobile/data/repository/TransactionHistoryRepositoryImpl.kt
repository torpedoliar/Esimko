package com.esimko.mobile.data.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.core.network.apiErrorMessage
import com.esimko.mobile.data.remote.api.TransactionHistoryApi
import com.esimko.mobile.domain.model.TransactionHistory
import com.esimko.mobile.domain.repository.TransactionHistoryRepository
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class TransactionHistoryRepositoryImpl @Inject constructor(
    private val api: TransactionHistoryApi
) : TransactionHistoryRepository {

    override suspend fun getTransactionHistory(id: Long, type: String): Result<List<TransactionHistory>> {
        return try {
            val response = api.getTransactionHistory(id, type)
            if (response.success && response.data != null) {
                Result.Success(response.data.map {
                    TransactionHistory(
                        createdAt = it.created_at.orEmpty(),
                        caption = it.caption.orEmpty(),
                        noAnggota = it.no_anggota.orEmpty(),
                        namaLengkap = it.nama_lengkap.orEmpty()
                    )
                })
            } else {
                Result.Error(response.message ?: "Gagal memuat riwayat")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Tidak ada koneksi. Periksa jaringan."))
        }
    }
}
