package com.esimko.mobile.data.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.core.network.apiErrorMessage
import com.esimko.mobile.data.remote.api.InstallmentApi
import com.esimko.mobile.domain.model.Installment
import com.esimko.mobile.domain.model.Salary
import com.esimko.mobile.domain.repository.InstallmentRepository
import okhttp3.MultipartBody
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.RequestBody.Companion.toRequestBody
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
                        pokok = dto.angsuranPokok?.let { Math.round(it) } ?: 0,
                        bunga = dto.angsuranBunga?.let { Math.round(it) } ?: 0,
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
                Result.Success(Salary(gajiPokok = response.data.gajiPokok?.let { Math.round(it) } ?: 0))
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
        keterangan: String?,
        slipBytes: ByteArray?,
        slipMime: String?
    ): Result<Long> {
        return try {
            val toPart = { value: String -> value.toRequestBody("text/plain".toMediaType()) }
            val slipPart = slipBytes?.let { bytes ->
                MultipartBody.Part.createFormData(
                    "attachment", "slip_gaji", bytes.toRequestBody(slipMime?.toMediaType() ?: "image/jpeg".toMediaType())
                )
            }
            val response = api.submitLoan(
                "pinjaman",
                toPart("add"),
                toPart(nominal.toString()),
                toPart(tenor.toString()),
                toPart(jenisPinjaman.toString()),
                toPart(gajiPokok.toString()),
                toPart(keterangan ?: ""),
                slipPart
            )
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
