package com.esimko.mobile.data.repository

import com.esimko.mobile.core.network.Result
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
                        createdAt = it.created_at,
                        caption = it.caption,
                        noAnggota = it.no_anggota,
                        namaLengkap = it.nama_lengkap
                    )
                })
            } else {
                Result.Error(response.message ?: "Failed to load history")
            }
        } catch (e: Exception) {
            Result.Error(e.message ?: "Network error")
        }
    }
}
