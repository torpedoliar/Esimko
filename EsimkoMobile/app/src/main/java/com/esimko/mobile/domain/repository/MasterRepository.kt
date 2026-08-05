package com.esimko.mobile.domain.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.TransactionType
import com.esimko.mobile.domain.model.TransactionStatus

interface MasterRepository {
    suspend fun getTransactionTypes(modul: String): Result<List<TransactionType>>
    suspend fun getTransactionStatuses(modul: String): Result<List<TransactionStatus>>
}
