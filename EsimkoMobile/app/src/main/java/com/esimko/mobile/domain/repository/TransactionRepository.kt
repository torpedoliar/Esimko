package com.esimko.mobile.domain.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.data.local.TokenStore
import com.esimko.mobile.domain.model.Paged
import com.esimko.mobile.domain.model.Transaction
import com.esimko.mobile.domain.model.TransactionDetail

/** Only Abandoned is permission to discard unresolved consent; errors/absence never are. */
sealed interface SavingsReconciliation {
    data class Existing(val transactionId: Long) : SavingsReconciliation
    data class Abandoned(val key: String, val jenis: String) : SavingsReconciliation
}

interface TransactionRepository {
    suspend fun getTransactions(
        modul: String,
        jenis: Int? = null,
        status: Int? = null,
        tanggalAwal: String? = null,
        tanggalAkhir: String? = null,
        search: String? = null,
        sort: String? = null,
        page: Int = 1,
        perPage: Int = 20
    ): Result<Paged<Transaction>>

    suspend fun getTransactionDetail(modul: String, id: Long): Result<TransactionDetail>

    suspend fun processTransaction(
        jenis: String,
        nominal: Long,
        keterangan: String?,
        idempotencyKey: String,
        session: TokenStore.Session
    ): Result<Transaction>

    suspend fun reconcileSavings(
        jenis: String,
        nominal: Long,
        keterangan: String?,
        idempotencyKey: String,
        session: TokenStore.Session
    ): Result<SavingsReconciliation>

    suspend fun cancelTransaction(id: Long, alasan: String): Result<Unit>

    suspend fun uploadTransactionProof(
        id: Long, fileBytes: ByteArray, mimeType: String, session: TokenStore.Session? = null
    ): Result<Unit>
}
