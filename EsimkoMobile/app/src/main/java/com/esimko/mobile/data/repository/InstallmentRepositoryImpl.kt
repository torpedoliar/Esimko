package com.esimko.mobile.data.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.core.network.apiErrorMessage
import com.esimko.mobile.data.remote.api.InstallmentApi
import com.esimko.mobile.data.remote.dto.TransactionRequest
import com.esimko.mobile.domain.model.Installment
import com.esimko.mobile.domain.model.Salary
import com.esimko.mobile.domain.repository.InstallmentRepository
import javax.inject.Inject

class InstallmentRepositoryImpl @Inject constructor(
    private val api: InstallmentApi
) : InstallmentRepository {

    override suspend fun getLoanInstallments(): Result<List<Installment>> {
        return try {
            val response = api.getLoanInstallments()
            if (response.success && response.data != null) {
                Result.Success(response.data.map { dto ->
                    Installment(
                        id = dto.id ?: 0,
                        ke = dto.angsuranKe ?: 0,
                        pokok = dto.angsuranPokok ?: 0,
                        bunga = dto.angsuranBunga ?: 0,
                        status = dto.statusAngsuran.orEmpty(),
                        jenisTransaksi = dto.jenisTransaksi,
                        namaBulan = dto.namaBulan
                    )
                })
            } else {
                Result.Error(response.message ?: "Failed to load installments")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Network error"))
        }
    }

    override suspend fun getBaseSalary(): Result<Salary> {
        return try {
            val response = api.getBaseSalary()
            if (response.success && response.data != null) {
                Result.Success(Salary(gajiPokok = response.data.gajiPokok ?: 0))
            } else {
                Result.Error(response.message ?: "Failed to load salary")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Network error"))
        }
    }

    override suspend fun submitLoan(
        jenisPinjaman: Int,
        nominal: Long,
        tenor: Int,
        gajiPokok: Long,
        keterangan: String?
    ): Result<Long> {
        return try {
            val request = TransactionRequest(
                action = "add",
                nominal = nominal,
                tenor = tenor,
                jenisPinjaman = jenisPinjaman,
                gajiPokok = gajiPokok,
                keterangan = keterangan
            )
            val response = api.submitLoan("pinjaman", request)
            if (response.success && response.data != null) {
                Result.Success(response.data.id)
            } else {
                Result.Error(response.message ?: "Pengajuan pinjaman gagal")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Network error"))
        }
    }
}
