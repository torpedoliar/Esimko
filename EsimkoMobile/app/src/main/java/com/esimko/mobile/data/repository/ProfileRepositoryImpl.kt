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
import com.squareup.moshi.Moshi
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.SupervisorJob
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import kotlinx.coroutines.sync.Mutex
import kotlinx.coroutines.sync.withLock
import kotlinx.coroutines.withContext
import okhttp3.MultipartBody
import java.io.File
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class ProfileRepositoryImpl @Inject constructor(
    private val api: ProfileApi,
    private val tokenStore: TokenStore,
    private val moshi: Moshi,
    @ApplicationContext private val context: Context
) : ProfileRepository {

    // Profil di-cache di memori + disk. Tiap GET lewat Cloudflare makan ~2 detik; cache ini
    // menghindari menunggu jaringan saat cold start maupun membuka menu Akun. Pola:
    // balas cache yang masih segar langsung; bila kedaluwarsa, balas cache dulu lalu segarkan
    // di latar (stale-while-revalidate). Hanya saat belum ada cache sama sekali kita menunggu.
    private val scope = CoroutineScope(SupervisorJob() + Dispatchers.IO)
    private val mutex = Mutex()
    private val profileAdapter by lazy { moshi.adapter(Profile::class.java) }
    private val cacheFile: File get() = File(context.filesDir, "profile_cache.json")

    private val _profile = MutableStateFlow<Profile?>(null)
    override val profile: StateFlow<Profile?> = _profile.asStateFlow()

    @Volatile private var lastFetchAt = 0L

    init {
        // Muat cache disk di luar thread utama. Validasi pemiliknya (no_anggota) supaya
        // akun yang berbeda di HP yang sama tidak saling melihat profil.
        scope.launch {
            val owner = tokenStore.noAnggota
            val fromDisk = readDisk()
            if (fromDisk != null && owner != null && fromDisk.noAnggota == owner) {
                _profile.value = fromDisk
            }
        }
    }

    override suspend fun getProfile(forceRefresh: Boolean): Result<Profile> {
        val cached = cachedProfile()
        if (!forceRefresh && cached != null) {
            if (System.currentTimeMillis() - lastFetchAt < CACHE_TTL_MS) {
                return Result.Success(cached)
            }
            // Kedaluwarsa: pakai cache untuk tampil cepat, segarkan di latar.
            refreshInBackground()
            return Result.Success(cached)
        }
        return fetchAndCache()
    }

    /** Profil dari memori, atau disk bila memori masih kosong (mis. tepat setelah cold start). */
    private suspend fun cachedProfile(): Profile? {
        val owner = tokenStore.noAnggota
        val mem = _profile.value
        if (mem != null && (owner == null || mem.noAnggota == owner)) return mem
        val fromDisk = withContext(Dispatchers.IO) { readDisk() }
        if (fromDisk != null && owner != null && fromDisk.noAnggota == owner) {
            _profile.value = fromDisk
            return fromDisk
        }
        return null
    }

    private suspend fun fetchAndCache(): Result<Profile> {
        return try {
            val response = api.getProfile()
            if (response.success && response.data != null) {
                val data = response.data
                val profile = Profile(
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
                _profile.value = profile
                lastFetchAt = System.currentTimeMillis()
                withContext(Dispatchers.IO) { writeDisk(profile) }
                Result.Success(profile)
            } else {
                Result.Error(response.message ?: "Failed to load profile")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Network error"))
        }
    }

    private fun refreshInBackground() {
        scope.launch {
            mutex.withLock { fetchAndCache() }
        }
    }

    private fun readDisk(): Profile? = try {
        val f = cacheFile
        if (f.exists()) profileAdapter.fromJson(f.readText()) else null
    } catch (e: Exception) {
        null
    }

    private fun writeDisk(value: Profile) {
        try {
            cacheFile.writeText(profileAdapter.toJson(value))
        } catch (e: Exception) {
            // Cache disk bukan syarat wajib — abaikan bila penyimpanan bermasalah.
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
        clearCache()
    }

    /** Hapus cache agar akun berikutnya tidak melihat data anggota sebelumnya. */
    private fun clearCache() {
        _profile.value = null
        lastFetchAt = 0L
        scope.launch {
            try { cacheFile.delete() } catch (_: Exception) {}
        }
    }

    companion object {
        private const val CACHE_TTL_MS = 60_000L
    }
}