package com.esimko.mobile.data.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.data.remote.api.TransactionApi
import com.esimko.mobile.data.remote.dto.TransactionRequest
import com.esimko.mobile.data.remote.dto.CancelRequest
import com.esimko.mobile.domain.model.Transaction
import com.esimko.mobile.domain.repository.TransactionRepository
import javax.inject.Inject

class TransactionRepositoryImpl @Inject constructor(
    private val api: TransactionApi
) : TransactionRepository {

    override suspend fun getTransactions(
        modul: String,
        tanggalAwal: String?,
        tanggalAkhir: String?,
        page: Int?,
        perPage: Int?
    ): Result<List<Transaction>> {
        return try {
            val response = api.getTransactions(modul, tanggalAwal, tanggalAkhir, page, perPage)
            if (response.success && response.data != null) {
                Result.Success(response.data.map { dto ->
                    Transaction(
                        id = dto.id,
                        jenis = dto.jenisTransaksi ?: "",
                        modul = modul,
                        nominal = dto.nominal ?: 0L,
                        tanggal = dto.tanggal ?: "",
                        status = dto.status ?: "",
                        statusLabel = dto.status ?: "",
                        keterangan = dto.keterangan
                    )
                })
            } else {
                Result.Error(response.message ?: "Failed to load transactions")
            }
        } catch (e: Exception) {
            Result.Error(e.message ?: "Network error")
        }
    }

    override suspend fun getTransactionDetail(modul: String, id: Long): Result<Transaction> {
        return try {
            val response = api.getTransactionDetail(modul, id)
            if (response.success && response.data != null) {
                val dto = response.data
                Result.Success(Transaction(
                    id = dto.id,
                    jenis = dto.jenisTransaksi ?: "",
                    modul = modul,
                    nominal = dto.nominal ?: 0L,
                    tanggal = dto.tanggal ?: "",
                    status = dto.status ?: "",
                    statusLabel = dto.status ?: "",
                    keterangan = dto.keterangan
                ))
            } else {
                Result.Error(response.message ?: "Failed to load transaction detail")
            }
        } catch (e: Exception) {
            Result.Error(e.message ?: "Network error")
        }
    }

    override suspend fun processTransaction(
        jenis: String,
        nominal: Long,
        keterangan: String?
    ): Result<Transaction> {
        return try {
            val request = TransactionRequest(jenis = jenis, nominal = nominal, keterangan = keterangan)
            val response = api.processTransaction(jenis, request)
            if (response.success && response.data != null) {
                val dto = response.data
                Result.Success(Transaction(
                    id = dto.id,
                    jenis = dto.jenisTransaksi ?: "",
                    modul = "",
                    nominal = dto.nominal ?: 0L,
                    tanggal = dto.tanggal ?: "",
                    status = dto.status ?: "",
                    statusLabel = dto.status ?: "",
                    keterangan = dto.keterangan
                ))
            } else {
                Result.Error(response.message ?: "Failed to process transaction")
            }
        } catch (e: Exception) {
            Result.Error(e.message ?: "Network error")
        }
    }

    override suspend fun cancelTransaction(id: Long, alasan: String): Result<Unit> {
        return try {
            val request = CancelRequest(id = id, alasan = alasan)
            val response = api.cancelTransaction(request)
            if (response.success) {
                Result.Success(Unit)
            } else {
                Result.Error(response.message ?: "Failed to cancel transaction")
            }
        } catch (e: Exception) {
            Result.Error(e.message ?: "Network error")
        }
    }
}
