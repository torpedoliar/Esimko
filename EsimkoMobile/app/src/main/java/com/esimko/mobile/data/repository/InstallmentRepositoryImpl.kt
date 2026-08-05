package com.esimko.mobile.data.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.data.remote.api.InstallmentApi
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
                        id = dto.id,
                        ke = dto.ke ?: 0,
                        nominal = dto.nominal ?: 0,
                        bunga = dto.bunga ?: dto.angsuranBunga ?: 0,
                        pokok = dto.pokok ?: dto.angsuranPokok ?: 0,
                        tanggalJatuhTempo = dto.tanggalJatuhTempo.orEmpty(),
                        tanggalBayar = dto.tanggalBayar,
                        status = dto.status ?: dto.statusAngsuran.orEmpty()
                    )
                })
            } else {
                Result.Error(response.message ?: "Failed to load installments")
            }
        } catch (e: Exception) {
            Result.Error(e.message ?: "Network error")
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
            Result.Error(e.message ?: "Network error")
        }
    }
}
