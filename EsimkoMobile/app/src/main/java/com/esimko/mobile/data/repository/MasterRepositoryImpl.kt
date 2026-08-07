package com.esimko.mobile.data.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.core.network.apiErrorMessage
import com.esimko.mobile.data.remote.api.MasterApi
import com.esimko.mobile.domain.model.TransactionType
import com.esimko.mobile.domain.model.TransactionStatus
import com.esimko.mobile.domain.repository.MasterRepository
import javax.inject.Inject

class MasterRepositoryImpl @Inject constructor(
    private val api: MasterApi
) : MasterRepository {

    override suspend fun getTransactionTypes(modul: String): Result<List<TransactionType>> {
        return try {
            val response = api.getTransactionTypes(modul)
            if (response.success && response.data != null) {
                Result.Success(response.data.map { dto ->
                    TransactionType(
                        id = dto.id,
                        nama = dto.nama.orEmpty(),
                        kode = dto.group.orEmpty()
                    )
                })
            } else {
                Result.Error(response.message ?: "Gagal memuat jenis transaksi")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Tidak ada koneksi. Periksa jaringan."))
        }
    }

    override suspend fun getTransactionStatuses(modul: String): Result<List<TransactionStatus>> {
        return try {
            val response = api.getTransactionStatuses(modul)
            if (response.success && response.data != null) {
                Result.Success(response.data.map { dto ->
                    TransactionStatus(
                        id = dto.id,
                        nama = dto.nama.orEmpty(),
                        kode = dto.caption.orEmpty(),
                        color = dto.color
                    )
                })
            } else {
                Result.Error(response.message ?: "Gagal memuat status transaksi")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Tidak ada koneksi. Periksa jaringan."))
        }
    }
}
