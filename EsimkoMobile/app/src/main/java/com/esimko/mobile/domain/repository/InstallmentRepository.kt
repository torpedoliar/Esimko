package com.esimko.mobile.domain.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.Installment
import com.esimko.mobile.domain.model.Salary

interface InstallmentRepository {
    suspend fun getLoanInstallments(): Result<List<Installment>>
    suspend fun getBaseSalary(): Result<Salary>
    suspend fun submitLoan(jenisPinjaman: Int, nominal: Long, tenor: Int, gajiPokok: Long, keterangan: String?, slipBytes: ByteArray?, slipMime: String?): Result<Long>
}
