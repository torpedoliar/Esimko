package com.esimko.mobile.data.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.core.network.apiErrorMessage
import com.esimko.mobile.data.remote.api.TransactionApi
import com.esimko.mobile.data.remote.dto.TransactionRequest
import com.esimko.mobile.data.remote.dto.TransactionResponse
import com.esimko.mobile.data.remote.dto.CancelRequest
import com.esimko.mobile.domain.model.Paged
import com.esimko.mobile.domain.model.Transaction
import com.esimko.mobile.domain.model.TransactionDetail
import com.esimko.mobile.domain.repository.TransactionRepository
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.toRequestBody
import javax.inject.Inject

class TransactionRepositoryImpl @Inject constructor(
    private val api: TransactionApi
) : TransactionRepository {

    override suspend fun getTransactions(
        modul: String,
        jenis: Int?,
        status: Int?,
        tanggalAwal: String?,
        tanggalAkhir: String?,
        page: Int,
        perPage: Int
    ): Result<Paged<Transaction>> {
        return try {
            val response = api.getTransactions(
                modul = modul,
                jenis = jenis,
                status = status,
                tanggalMulai = tanggalAwal,
                tanggalAkhir = tanggalAkhir,
                page = page,
                perPage = perPage
            )
            if (response.success && response.data != null) {
                Result.Success(
                    Paged(
                        items = response.data.map { it.toDomain(modul) },
                        page = response.meta?.page ?: page,
                        // Tanpa meta (backend hanya mengirimnya saat paginasi) anggap satu halaman —
                        // lebih baik berhenti memuat daripada meminta halaman yang tidak ada.
                        lastPage = response.meta?.last_page ?: (response.meta?.page ?: page)
                    )
                )
            } else {
                Result.Error(response.message ?: "Gagal memuat transaksi")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Tidak ada koneksi. Periksa jaringan."))
        }
    }

    override suspend fun getTransactionDetail(modul: String, id: Long): Result<TransactionDetail> {
        return try {
            // `$modul` di path diterima backend tapi tidak dipakai di query (lookup murni by id),
            // jadi nilai apa pun aman. Tetap dikirim supaya URL sesuai rute.
            val response = api.getTransactionDetail(modul, id)
            if (response.success && response.data != null) {
                val dto = response.data
                Result.Success(
                    TransactionDetail(
                        id = dto.id,
                        jenis = dto.jenisTransaksi.orEmpty(),
                        nominal = dto.nominal?.let { Math.round(it) } ?: 0L,
                        tanggal = dto.tanggal.orEmpty(),
                        status = dto.status.orEmpty(),
                        statusLabel = dto.status.orEmpty(),
                        keterangan = dto.keterangan,
                        buktiTransaksi = dto.buktiTransaksi,
                        items = null,
                        color = dto.color,
                        statusKeterangan = dto.statusKeterangan?.takeIf { it.isNotBlank() },
                        namaPetugas = dto.namaPetugas,
                        metodePembayaran = dto.metodePembayaran,
                        noAnggota = dto.noAnggota.orEmpty(),
                        namaLengkap = dto.namaLengkap.orEmpty()
                    )
                )
            } else {
                Result.Error(response.message ?: "Gagal memuat detail transaksi")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Tidak ada koneksi. Periksa jaringan."))
        }
    }

    override suspend fun processTransaction(
        jenis: String,
        nominal: Long,
        keterangan: String?
    ): Result<Transaction> {
        return try {
            val request = TransactionRequest(action = "add", nominal = nominal, keterangan = keterangan)
            val response = api.processTransaction(jenis, request)
            if (response.success && response.data != null) {
                val dto = response.data
                Result.Success(Transaction(
                    id = dto.id,
                    jenis = dto.jenisTransaksi ?: "",
                    modul = "",
                    nominal = dto.nominal?.let { Math.round(it) } ?: 0L,
                    tanggal = dto.tanggal ?: "",
                    status = dto.status ?: "",
                    statusLabel = dto.status ?: "",
                    keterangan = dto.keterangan
                ))
            } else {
                Result.Error(response.message ?: "Gagal mengirim pengajuan")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Tidak ada koneksi. Periksa jaringan."))
        }
    }

    override suspend fun cancelTransaction(id: Long, alasan: String): Result<Unit> {
        return try {
            val request = CancelRequest(id = id, alasan = alasan)
            val response = api.cancelTransaction(request)
            if (response.success) {
                Result.Success(Unit)
            } else {
                Result.Error(response.message ?: "Gagal membatalkan transaksi")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Tidak ada koneksi. Periksa jaringan."))
        }
    }

    override suspend fun uploadTransactionProof(id: Long, fileBytes: ByteArray, mimeType: String): Result<Unit> {
        return try {
            val idBody = id.toString().toRequestBody("text/plain".toMediaTypeOrNull())
            val fileBody = fileBytes.toRequestBody(mimeType.toMediaTypeOrNull())
            val part = MultipartBody.Part.createFormData("bukti_transaksi", "bukti.jpg", fileBody)
            val response = api.uploadTransactionProof(idBody, part)
            if (response.success) {
                Result.Success(Unit)
            } else {
                Result.Error(response.message ?: "Gagal mengunggah bukti")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Tidak ada koneksi. Periksa jaringan."))
        }
    }
}

/**
 * DTO → model. Murni, tanpa Android/Retrofit, jadi bisa diuji di JVM.
 * `nominal_tampil` dibaca hanya untuk tandanya; angkanya diformat MoneyFormatter.
 */
fun TransactionResponse.toDomain(modul: String): Transaction = Transaction(
    id = id,
    jenis = jenisTransaksi.orEmpty(),
    modul = modul,
    nominal = nominal?.let { Math.round(it) } ?: 0L,
    tanggal = tanggal.orEmpty(),
    status = status.orEmpty(),
    statusLabel = status.orEmpty(),
    keterangan = keterangan,
    color = color,
    nominalTampil = nominalTampil,
    isDebit = nominalTampil?.trimStart()?.startsWith('-') == true,
    totalAngsuran = totalAngsuran?.let { Math.round(it) },
    sisaPinjaman = sisaPinjaman?.let { Math.round(it) },
    sisaTenor = sisaTenor
)
