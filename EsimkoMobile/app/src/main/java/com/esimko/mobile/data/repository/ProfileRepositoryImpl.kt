package com.esimko.mobile.data.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.core.network.apiErrorMessage
import com.esimko.mobile.data.local.TokenStore
import com.esimko.mobile.data.remote.api.ProfileApi
import com.esimko.mobile.data.remote.dto.ChangePasswordRequest
import com.esimko.mobile.domain.model.Profile
import com.esimko.mobile.domain.repository.ProfileRepository
import com.esimko.mobile.util.roundL
import okhttp3.MultipartBody
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class ProfileRepositoryImpl @Inject constructor(
    private val api: ProfileApi,
    private val tokenStore: TokenStore
) : ProfileRepository {

    override suspend fun getProfile(): Result<Profile> {
        return try {
            val response = api.getProfile()
            if (response.success && response.data != null) {
                val data = response.data
                Result.Success(
                    Profile(
                        noAnggota = data.noAnggota.orEmpty(),
                        nama = data.namaLengkap.orEmpty(),
                        ktp = data.noKtp.orEmpty(),
                        alamat = data.alamat.orEmpty(),
                        telepon = data.noHandphone.orEmpty(),
                        email = data.email,
                        avatar = data.avatar,
                        saldoSimpanan = data.totalSaldoSimpanan.roundL(),
                        saldoPinjaman = data.sisaPinjaman.roundL(),
                        angsuranBulan = data.totalAngsuranPinjaman.roundL(),
                        saldoSimpananPokok = data.saldoSimpananPokok.roundL(),
                        saldoSimpananWajib = data.saldoSimpananWajib.roundL(),
                        saldoSimpananSukarela = data.saldoSimpananSukarela.roundL(),
                        saldoSimpananHariRaya = data.saldoSimpananHariRaya.roundL(),
                        bungaPinjaman = data.bungaPinjaman.roundL(),
                        angsuranJangkaPanjang = data.angsuranJangkaPanjang.roundL(),
                        angsuranJangkaPendek = data.angsuranJangkaPendek.roundL(),
                        angsuranBarang = data.angsuranBarang.roundL(),
                        totalAngsuranBelanja = data.totalAngsuranBelanja.roundL(),
                        setoranBerkala = data.setoranBerkala.roundL(),
                        setoranSimpananAnggota = data.setoranSimpananAnggota.roundL(),
                        statusAnggota = data.statusAnggota.orEmpty(),
                        divisi = data.divisi.orEmpty(),
                        bagian = data.bagian.orEmpty()
                    )
                )
            } else {
                Result.Error(response.message ?: "Failed to load profile")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Network error"))
        }
    }

    override suspend fun changePassword(oldPassword: String, newPassword: String): Result<Unit> {
        return try {
            val response = api.changePassword(ChangePasswordRequest(oldPassword, newPassword, newPassword))
            if (response.success) {
                Result.Success(Unit)
            } else {
                Result.Error(response.message ?: "Failed to change password")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Network error"))
        }
    }

    override suspend fun uploadAvatar(avatar: MultipartBody.Part): Result<Unit> {
        return try {
            val response = api.uploadAvatar(avatar)
            if (response.success) {
                Result.Success(Unit)
            } else {
                Result.Error(response.message ?: "Failed to upload avatar")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Network error"))
        }
    }

    override suspend fun logout() {
        tokenStore.clear()
    }
}
