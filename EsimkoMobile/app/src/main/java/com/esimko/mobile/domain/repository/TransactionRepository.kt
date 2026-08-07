package com.esimko.mobile.domain.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.Paged
import com.esimko.mobile.domain.model.Transaction
import com.esimko.mobile.domain.model.TransactionDetail

interface TransactionRepository {
    suspend fun getTransactions(
        modul: String,
        jenis: Int? = null,
        status: Int? = null,
        tanggalAwal: String? = null,
        tanggalAkhir: String? = null,
        page: Int = 1,
        perPage: Int = 20
    ): Result<Paged<Transaction>>

    suspend fun getTransactionDetail(modul: String, id: Long): Result<TransactionDetail>

    suspend fun processTransaction(
        jenis: String,
        nominal: Long,
        keterangan: String?
    ): Result<Transaction>

    suspend fun cancelTransaction(id: Long, alasan: String): Result<Unit>

    suspend fun uploadTransactionProof(id: Long, fileBytes: ByteArray, mimeType: String): Result<Unit>
}
