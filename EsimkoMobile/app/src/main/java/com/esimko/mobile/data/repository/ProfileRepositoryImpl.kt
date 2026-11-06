package com.esimko.mobile.data.repository

import android.content.Context
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.core.network.apiErrorMessage
import com.esimko.mobile.data.local.TokenStore
import com.esimko.mobile.data.remote.api.ProfileApi
import com.esimko.mobile.data.remote.dto.ChangePasswordRequest
import com.esimko.mobile.domain.model.Profile
import com.esimko.mobile.domain.repository.ProfileRepository
import com.esimko.mobile.util.roundL
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.sync.Mutex
import kotlinx.coroutines.sync.withLock
import kotlinx.coroutines.withContext
import okhttp3.MultipartBody
import retrofit2.HttpException
import java.io.File
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class ProfileRepositoryImpl @Inject constructor(
    private val api: ProfileApi,
    private val tokenStore: TokenStore,
    @ApplicationContext private val context: Context
) : ProfileRepository {
    // Memory only: TokenStore's encryption can fall back to plaintext, so it is not
    // suitable for persisting identity and financial data. Never read the legacy file.
    private val mutex = Mutex()
    private val _profile = MutableStateFlow<Profile?>(null)
    override val profile: StateFlow<Profile?> = _profile.asStateFlow()
    private var lastFetchAt = 0L
    private var completedFetch = 0L
    private var lastResult: Result<Profile>? = null

    init {
        tokenStore.addSessionListener {
            _profile.value = null
            lastFetchAt = 0L
            lastResult = null
        }
    }

    override suspend fun getProfile(forceRefresh: Boolean): Result<Profile> {
        val revision: Long
        val owner: String
        val observedFetch: Long
        synchronized(tokenStore) {
            revision = tokenStore.sessionRevision
            owner = tokenStore.noAnggota.orEmpty()
            observedFetch = completedFetch
        }
        // Never read the legacy plaintext cache, even when deletion fails.
        withContext(Dispatchers.IO) {
            try { File(context.filesDir, "profile_cache.json").delete() } catch (_: SecurityException) { }
        }
        return mutex.withLock {
            synchronized(tokenStore) {
                if (revision != tokenStore.sessionRevision) {
                    return@withLock Result.Error("Sesi telah berubah. Silakan coba lagi.")
                }
                if (owner.isBlank() || tokenStore.token.isNullOrBlank()) {
                    _profile.value = null
                    return@withLock Result.Error("Sesi berakhir. Silakan masuk kembali.")
                }
                // All overlapping callers (including forced refreshes and failures)
                // share the request that completed while they waited for the lock.
                if (completedFetch != observedFetch) {
                    lastResult?.let { return@withLock it }
                }
                val cached = _profile.value
                if (!forceRefresh && cached != null && cached.noAnggota == owner &&
                    System.nanoTime() - lastFetchAt < CACHE_TTL_NS) {
                    return@withLock Result.Success(cached)
                }
                // Do not display unverified financial snapshots during/after refresh.
                _profile.value = null
            }
            val result = fetch(owner, revision)
            synchronized(tokenStore) {
                if (revision != tokenStore.sessionRevision) {
                    return@withLock Result.Error("Sesi telah berubah. Silakan masuk kembali.")
                }
                completedFetch++
                lastResult = result
                if (result is Result.Success) {
                    lastFetchAt = System.nanoTime()
                    _profile.value = result.data
                }
                result
            }
        }
    }

    private suspend fun fetch(owner: String, revision: Long): Result<Profile> = try {
        val response = api.getProfile()
        val data = response.data
        if (response.success && data != null && data.noAnggota == owner) {
            Result.Success(Profile(
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
            ))
        } else {
            Result.Error(response.message ?: "Gagal memuat profil. Silakan coba lagi.")
        }
    } catch (e: CancellationException) {
        throw e
    } catch (e: Exception) {
        if (e is HttpException && e.code() == 401) tokenStore.clearIfCurrent(revision)
        Result.Error(apiErrorMessage(e, "Network error"))
    }

    override suspend fun changePassword(oldPassword: String, newPassword: String): Result<Unit> = try {
        val response = api.changePassword(ChangePasswordRequest(oldPassword, newPassword, newPassword))
        if (response.success) Result.Success(Unit)
        else Result.Error(response.message ?: "Failed to change password")
    } catch (e: CancellationException) {
        throw e
    } catch (e: Exception) {
        Result.Error(apiErrorMessage(e, "Network error"))
    }

    override suspend fun uploadAvatar(avatar: MultipartBody.Part): Result<Unit> = try {
        val response = api.uploadAvatar(avatar)
        if (response.success) Result.Success(Unit)
        else Result.Error(response.message ?: "Failed to upload avatar")
    } catch (e: CancellationException) {
        throw e
    } catch (e: Exception) {
        Result.Error(apiErrorMessage(e, "Network error"))
    }

    override suspend fun logout() {
        tokenStore.clear()
        withContext(Dispatchers.IO) { File(context.filesDir, "profile_cache.json").delete() }
    }

    companion object {
        private const val CACHE_TTL_NS = 60_000_000_000L
    }
}
