package com.esimko.mobile.domain.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.TransactionHistory

interface TransactionHistoryRepository {
    suspend fun getTransactionHistory(id: Long, type: String): Result<List<TransactionHistory>>
}
