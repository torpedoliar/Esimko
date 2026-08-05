package com.esimko.mobile.domain.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.Transaction

interface TransactionRepository {
    suspend fun getTransactions(
        modul: String,
        tanggalAwal: String? = null,
        tanggalAkhir: String? = null,
        page: Int? = null,
        perPage: Int? = null
    ): Result<List<Transaction>>

    suspend fun getTransactionDetail(modul: String, id: Long): Result<Transaction>

    suspend fun processTransaction(
        jenis: String,
        nominal: Long,
        keterangan: String?
    ): Result<Transaction>

    suspend fun cancelTransaction(id: Long, alasan: String): Result<Unit>
}
