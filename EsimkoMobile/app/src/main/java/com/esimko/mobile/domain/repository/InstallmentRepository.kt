package com.esimko.mobile.domain.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.DokumenFile
import com.esimko.mobile.domain.model.Installment
import com.esimko.mobile.domain.model.Salary
import com.esimko.mobile.domain.model.SyaratPinjaman

interface InstallmentRepository {
    suspend fun getLoanInstallments(): Result<List<Installment>>
    suspend fun getBaseSalary(): Result<Salary>
    suspend fun getSyaratPinjaman(): Result<SyaratPinjaman>
    suspend fun submitLoan(
        jenisPinjaman: Int,
        nominal: Long,
        tenor: Int,
        gajiPokok: Long,
        keterangan: String?,
        persetujuanVersi: String?,
        dokumen: Map<String, DokumenFile>
    ): Result<Long>
}
