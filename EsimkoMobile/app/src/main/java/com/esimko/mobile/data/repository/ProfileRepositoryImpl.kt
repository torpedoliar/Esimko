package com.esimko.mobile.data.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.data.local.TokenStore
import com.esimko.mobile.data.remote.api.ProfileApi
import com.esimko.mobile.data.remote.dto.ChangePasswordRequest
import com.esimko.mobile.domain.model.Profile
import com.esimko.mobile.domain.repository.ProfileRepository
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
                        saldoSimpanan = data.totalSaldoSimpanan ?: 0,
                        saldoPinjaman = data.sisaPinjaman ?: 0,
                        angsuranBulan = data.totalAngsuranPinjaman ?: 0,
                        saldoSimpananPokok = data.saldoSimpananPokok ?: 0,
                        saldoSimpananWajib = data.saldoSimpananWajib ?: 0,
                        saldoSimpananSukarela = data.saldoSimpananSukarela ?: 0,
                        saldoSimpananHariRaya = data.saldoSimpananHariRaya ?: 0,
                        bungaPinjaman = data.bungaPinjaman ?: 0,
                        angsuranJangkaPanjang = data.angsuranJangkaPanjang ?: 0,
                        angsuranJangkaPendek = data.angsuranJangkaPendek ?: 0,
                        angsuranBarang = data.angsuranBarang ?: 0,
                        totalAngsuranBelanja = data.totalAngsuranBelanja ?: 0,
                        setoranBerkala = data.setoranBerkala ?: 0,
                        setoranSimpananAnggota = data.setoranSimpananAnggota ?: 0,
                        statusAnggota = data.statusAnggota.orEmpty(),
                        divisi = data.divisi.orEmpty(),
                        bagian = data.bagian.orEmpty()
                    )
                )
            } else {
                Result.Error(response.message ?: "Failed to load profile")
            }
        } catch (e: Exception) {
            Result.Error(e.message ?: "Network error")
        }
    }

    override suspend fun changePassword(oldPassword: String, newPassword: String): Result<Unit> {
        return try {
            val response = api.changePassword(ChangePasswordRequest(oldPassword, newPassword))
            if (response.success) {
                Result.Success(Unit)
            } else {
                Result.Error(response.message ?: "Failed to change password")
            }
        } catch (e: Exception) {
            Result.Error(e.message ?: "Network error")
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
            Result.Error(e.message ?: "Network error")
        }
    }

    override suspend fun logout() {
        tokenStore.clear()
    }
}
