package com.esimko.mobile.ui.update

import android.content.Context
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.data.repository.NotificationRepository
import com.esimko.mobile.domain.model.AppNotification
import com.esimko.mobile.domain.model.NotificationType
import com.esimko.mobile.domain.repository.VersionRepository
import com.esimko.mobile.update.ApkInstaller
import dagger.hilt.android.lifecycle.HiltViewModel
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import kotlinx.coroutines.CancellationException
import com.esimko.mobile.update.validUpdateHash
import javax.inject.Inject

data class UpdateState(
    val info: com.esimko.mobile.domain.model.VersionCheck? = null,
    val sedangCek: Boolean = true,
    val sedangUnduh: Boolean = false,
    val tahap: String? = null,
    val error: String? = null,
    /** Android 8+ belum mengizinkan app ini memasang APK — arahkan ke setelan (sekali per HP). */
    val mintaIzin: Boolean = false,
    /** Unduhan selesai & SHA-256 lolos; layar (foreground) akan memicu pemasangan. */
    val siapPasang: String? = null,
    val otomatisPasang: Boolean = false,
    val siapVersiCode: Int? = null,
    val siapHash: String? = null
)

/**
 * Cek versi saat app dibuka (dan dari Pengaturan), lalu bila ada pembaruan:
 * unduh (DownloadManager) → verifikasi SHA-256 → buka installer sistem.
 * Force-update (versi < min_mobile_version) dirender sebagai dialog mengunci oleh UpdateGate.
 */
@HiltViewModel
class UpdateViewModel @Inject constructor(
    @ApplicationContext private val appContext: Context,
    private val versionRepository: VersionRepository,
    private val notificationRepository: NotificationRepository
) : ViewModel() {

    private val _state = MutableStateFlow(UpdateState())
    val state: StateFlow<UpdateState> = _state.asStateFlow()

    private var installer: ApkInstaller? = null
    private var lastCheckAt = 0L

    init {
        cek()
    }

    /** Cek saat app kembali ke depan, maksimal sekali per 5 menit. */
    fun cekJikaPerlu() {
        if (_state.value.sedangUnduh) return
        if (System.currentTimeMillis() - lastCheckAt < 5 * 60 * 1000L) return
        cek()
    }

    fun cek() {
        lastCheckAt = System.currentTimeMillis()
        viewModelScope.launch {
            _state.value = _state.value.copy(sedangCek = true, error = null)
            // Retry hingga 3x: kegagalan sekali (jaringan/edge) dulu membuat dialog
            // pembaruan tak pernah muncul senyap — anggota tak tahu ada rilis baru.
            var hasil: Result<com.esimko.mobile.domain.model.VersionCheck> = Result.Error("Gagal memeriksa versi")
            loop@ for (percobaan in 0..2) {
                when (val r = versionRepository.checkVersion()) {
                    is Result.Success -> { hasil = r; break@loop }
                    is Result.Error -> hasil = r
                    is Result.Loading -> Unit
                }
                if (percobaan < 2) kotlinx.coroutines.delay(2000)
            }
            when (hasil) {
                is Result.Success -> {
                    val changed = _state.value.info?.let {
                        it.latestVersionCode != hasil.data.latestVersionCode || it.apkSha256 != hasil.data.apkSha256
                    } ?: false
                    _state.value = _state.value.copy(
                        info = hasil.data, sedangCek = false,
                        siapPasang = if (changed) null else _state.value.siapPasang,
                        otomatisPasang = if (changed) false else _state.value.otomatisPasang,
                        siapVersiCode = if (changed) null else _state.value.siapVersiCode,
                        siapHash = if (changed) null else _state.value.siapHash
                    )
                    val vc = hasil.data
                    val inst = installer ?: ApkInstaller(appContext).also { installer = it }
                    if (vc.updateAvailable && inst.adaUnduhan(vc.latestVersion.orEmpty()) &&
                        !_state.value.sedangUnduh && _state.value.siapPasang == null) unduhDanPasang()
                    if (vc.updateAvailable) {
                        notificationRepository.capture(
                            AppNotification(
                                id = "update:${vc.latestVersion ?: "baru"}",
                                type = NotificationType.UPDATE,
                                title = "Pembaruan tersedia",
                                message = "Versi v${vc.latestVersion ?: "-"} tersedia. Ketuk untuk membuka Pengaturan.",
                                createdAt = System.currentTimeMillis()
                            )
                        )
                    }
                }
                is Result.Error -> _state.value = _state.value.copy(sedangCek = false, error = hasil.message)
                is Result.Loading -> Unit
            }
        }
    }

    /** Jalur alternatif bila pemasangan dalam aplikasi tidak tersedia pada ROM perangkat. */
    fun unduhViaBrowser() {
        val url = _state.value.info?.apkUrl ?: "https://esimko.com/apk"
        try {
            val i = android.content.Intent(android.content.Intent.ACTION_VIEW, android.net.Uri.parse(url))
                .addFlags(android.content.Intent.FLAG_ACTIVITY_NEW_TASK)
            appContext.startActivity(i)
        } catch (_: Exception) {
            _state.value = _state.value.copy(error = "Browser tidak tersedia. Buka https://esimko.com/apk di browser perangkat.")
        }
    }

    fun unduhDanPasang() {
        if (_state.value.sedangUnduh) return
        if (_state.value.siapPasang != null) {
            _state.value = _state.value.copy(otomatisPasang = true, error = null)
            return
        }
        val info = _state.value.info ?: return
        val inst = installer ?: ApkInstaller(appContext).also { installer = it }
        val url = info.apkUrl
        if (url.isNullOrBlank() || !url.startsWith("https://")) {
            _state.value = _state.value.copy(error = "URL pembaruan aman belum tersedia")
            return
        }
        if (!inst.bisaMintaInstall()) {
            _state.value = _state.value.copy(mintaIzin = true)
            return
        }
        _state.value = _state.value.copy(
            sedangUnduh = true, mintaIzin = false, error = null,
            tahap = "Mengunduh pembaruan — progres ada di notifikasi."
        )
        viewModelScope.launch {
            try {
                val path = inst.unduh(url, info.latestVersion.orEmpty())
                    ?: error("Unduhan gagal — coba lagi atau gunakan browser")
                _state.value = _state.value.copy(tahap = "Memverifikasi berkas…")
                val valid = withContext(Dispatchers.IO) {
                    validUpdateHash(inst.sha256(path), info.apkSha256) &&
                        inst.paketValid(path, info.latestVersion, info.latestVersionCode)
                }
                if (!valid) {
                    inst.hapusUnduhan()
                    error("APK tidak sesuai hash/versi rilis. Coba lagi atau gunakan browser.")
                }
                val current = _state.value.info
                if (current?.latestVersionCode != info.latestVersionCode || current.apkSha256 != info.apkSha256) {
                    inst.hapusUnduhan()
                    error("Rilis berubah saat mengunduh. Tekan Perbarui untuk versi terbaru.")
                }
                _state.value = _state.value.copy(
                    sedangUnduh = false, tahap = "Unduhan siap. Konfirmasikan pemasangan di Android.",
                    siapPasang = path, otomatisPasang = true,
                    siapVersiCode = info.latestVersionCode, siapHash = info.apkSha256
                )
            } catch (e: CancellationException) {
                throw e
            } catch (e: Exception) {
                try { inst.hapusUnduhan() } catch (_: Exception) { }
                _state.value = _state.value.copy(
                    sedangUnduh = false, tahap = null,
                    error = e.message ?: "Pembaruan gagal — gunakan Unduh via Browser"
                )
            }
        }
    }

    /** Path tetap disimpan bila installer dibatalkan: tombol Pasang dapat dicoba lagi. */
    fun pasangSiap(foregroundContext: Context) {
        val path = _state.value.siapPasang ?: return
        val info = _state.value.info
        if (info?.latestVersionCode != _state.value.siapVersiCode || info?.apkSha256 != _state.value.siapHash) {
            _state.value = _state.value.copy(siapPasang = null, otomatisPasang = false, error = "Rilis berubah. Unduh versi terbaru.")
            return
        }
        val inst = installer ?: ApkInstaller(appContext).also { installer = it }
        _state.value = _state.value.copy(otomatisPasang = false)
        if (!inst.bisaMintaInstall()) {
            _state.value = _state.value.copy(mintaIzin = true)
            return
        }
        val ok = inst.pasang(path, foregroundContext)
        _state.value = _state.value.copy(
            error = if (ok) null else "Tidak bisa membuka installer — gunakan Unduh via Browser"
        )
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

}
