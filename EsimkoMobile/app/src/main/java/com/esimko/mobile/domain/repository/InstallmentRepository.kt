package com.esimko.mobile.domain.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.DokumenFile
import com.esimko.mobile.domain.model.Installment
import com.esimko.mobile.domain.model.PengajuanPinjaman
import com.esimko.mobile.domain.model.Salary
import com.esimko.mobile.domain.model.SyaratPinjaman

interface InstallmentRepository {
    suspend fun getLoanInstallments(search: String? = null, sort: String? = null): Result<List<Installment>>
    suspend fun getBaseSalary(): Result<Salary>
    suspend fun getSyaratPinjaman(): Result<SyaratPinjaman>
    suspend fun getPengajuanPinjaman(id: Long): Result<PengajuanPinjaman>
    suspend fun submitLoan(
        action: String = "add",
        id: Long? = null,
        jenisPinjaman: Int,
        nominal: Long,
        tenor: Int,
        gajiPokok: Long,
        keterangan: String?,
        persetujuanVersi: String?,
        persetujuanPinjamanVersi: String?,
        dokumen: Map<String, DokumenFile>
    ): Result<Long>
}
