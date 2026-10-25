package com.esimko.mobile.ui.update

import android.content.BroadcastReceiver
import android.content.Context
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.repository.VersionRepository
import com.esimko.mobile.update.ApkInstaller
import dagger.hilt.android.lifecycle.HiltViewModel
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

data class UpdateState(
    val info: com.esimko.mobile.domain.model.VersionCheck? = null,
    val sedangCek: Boolean = true,
    val sedangUnduh: Boolean = false,
    val tahap: String? = null,
    val error: String? = null,
    /** Android 8+ belum mengizinkan app ini memasang APK — arahkan ke setelan (sekali per HP). */
    val mintaIzin: Boolean = false
)

/**
 * Cek versi saat app dibuka (dan dari Pengaturan), lalu bila ada pembaruan:
 * unduh (DownloadManager) → verifikasi SHA-256 → buka installer sistem.
 * Force-update (versi < min_mobile_version) dirender sebagai dialog mengunci oleh UpdateGate.
 */
@HiltViewModel
class UpdateViewModel @Inject constructor(
    @ApplicationContext private val appContext: Context,
    private val versionRepository: VersionRepository
) : ViewModel() {

    private val _state = MutableStateFlow(UpdateState())
    val state: StateFlow<UpdateState> = _state.asStateFlow()

    private var installer: ApkInstaller? = null
    private var receiver: BroadcastReceiver? = null

    init {
        cek()
    }

    fun cek() {
        viewModelScope.launch {
            _state.value = _state.value.copy(sedangCek = true, error = null)
            // Retry hingga 3x: kegagalan sekali (jaringan/edge) dulu membuat dialog
            // pembaruan tak pernah muncul senyap — anggota tak tahu ada rilis baru.
            var hasil: Result<com.esimko.mobile.domain.model.VersionCheck> = Result.Error("Gagal memeriksa versi")
            cek@ for (percobaan in 0..2) {
                when (val r = versionRepository.checkVersion()) {
                    is Result.Success -> { hasil = r; break@cek }
                    is Result.Error -> hasil = r
                    is Result.Loading -> Unit
                }
                if (percobaan < 2) kotlinx.coroutines.delay(2000)
            }
            when (hasil) {
                is Result.Success -> _state.value = _state.value.copy(info = (hasil as Result.Success).data, sedangCek = false)
                is Result.Error -> _state.value = _state.value.copy(sedangCek = false, error = (hasil as Result.Error).message)
                is Result.Loading -> Unit
            }
        }
    }

    /** Pintu darurat yang tak mungkin gagal: buka halaman unduh di browser. */
    fun unduhViaBrowser() {
        val url = _state.value.info?.apkUrl ?: "https://esimko.com/apk"
        try {
            val i = android.content.Intent(android.content.Intent.ACTION_VIEW, android.net.Uri.parse(url))
                .addFlags(android.content.Intent.FLAG_ACTIVITY_NEW_TASK)
            appContext.startActivity(i)
        } catch (_: Exception) {
        }
    }

    fun unduhDanPasang() {
        val info = _state.value.info ?: return
        val inst = installer ?: ApkInstaller(appContext).also { installer = it }
        val url = info.apkUrl
        if (url.isNullOrBlank()) {
            _state.value = _state.value.copy(error = "Paket pembaruan belum tersedia — unduh manual di esimko.com/apk")
            return
        }
        try {
            if (!inst.bisaMintaInstall()) {
                _state.value = _state.value.copy(mintaIzin = true)
                return
            }
        } catch (_: Exception) {
            // perangkat OEM aneh: anggap boleh, biarkan installer yang menolak nanti.
        }
        _state.value = _state.value.copy(
            sedangUnduh = true,
            mintaIzin = false,
            error = null,
            tahap = "Mengunduh pembaruan — progres ada di notifikasi."
        )
        receiver?.let { r -> try { appContext.unregisterReceiver(r) } catch (_: Exception) {} }
        try {
            receiver = inst.unduh(url, info.latestVersion ?: "baru") { path ->
                viewModelScope.launch {
                    if (path == null) {
                        _state.value = _state.value.copy(sedangUnduh = false, tahap = null, error = "Unduhan gagal — coba lagi, atau gunakan Unduh via Browser")
                        return@launch
                    }
                    _state.value = _state.value.copy(tahap = "Memverifikasi berkas…")
                    val hash = inst.sha256(path)
                    val diharapkan = info.apkSha256
                    if (!diharapkan.isNullOrBlank() && hash != diharapkan) {
                        _state.value = _state.value.copy(sedangUnduh = false, tahap = null, error = "Berkas rusak (verifikasi gagal) — coba lagi, atau Unduh via Browser")
                        return@launch
                    }
                    val ok = inst.pasang(path)
                    _state.value = _state.value.copy(
                        sedangUnduh = false,
                        tahap = null,
                        error = if (ok) null else "Tidak bisa membuka installer — gunakan Unduh via Browser"
                    )
                }
            }
        } catch (e: Exception) {
            // DownloadManager bermasalah (OEM) — jangan crash, arahkan ke browser.
            _state.value = _state.value.copy(sedangUnduh = false, tahap = null, error = "Unduhan gagal dimulai (${e.javaClass.simpleName}) — gunakan Unduh via Browser")
        }
    }

    fun bukaIzinInstall() {
        val inst = installer ?: ApkInstaller(appContext).also { installer = it }
        inst.bukaIzinInstall()
    }

    fun tutupMintaIzin() {
        _state.value = _state.value.copy(mintaIzin = false)
    }

    fun hapusError() {
        _state.value = _state.value.copy(error = null)
    }

    override fun onCleared() {
        receiver?.let { r -> try { appContext.unregisterReceiver(r) } catch (_: Exception) {} }
        super.onCleared()
    }
}
