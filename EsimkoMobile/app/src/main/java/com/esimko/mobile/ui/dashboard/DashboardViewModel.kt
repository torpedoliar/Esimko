package com.esimko.mobile.ui.dashboard

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.data.repository.NotificationRepository
import com.esimko.mobile.domain.model.AppNotification
import com.esimko.mobile.domain.model.NotificationType
import com.esimko.mobile.domain.model.Profile
import com.esimko.mobile.domain.repository.ProfileRepository
import com.esimko.mobile.domain.repository.TransactionRepository
import com.esimko.mobile.util.StatusMeta
import com.esimko.mobile.util.MoneyFormatter
import kotlin.math.abs
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.CoroutineStart
import kotlinx.coroutines.Job
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

data class DashboardState(
    val profile: Profile? = null,
    val pendingCount: Int = 0,
    val isLoading: Boolean = false,
    /** Gagal muat. Kalau `profile` masih ada, ini dipasang di `StaleBanner`, bukan `ErrorView`. */
    val error: String? = null
)

@HiltViewModel
class DashboardViewModel @Inject constructor(
    private val profileRepository: ProfileRepository,
    private val transactionRepository: TransactionRepository,
    private val notificationRepository: NotificationRepository
) : ViewModel() {

    private val _state = MutableStateFlow(DashboardState())
    val state: StateFlow<DashboardState> = _state

    private var loadJob: Job? = null

    init {
        viewModelScope.launch(start = CoroutineStart.UNDISPATCHED) {
            profileRepository.profile.collect { p ->
                if (p != null) _state.value = _state.value.copy(profile = p)
                else if (_state.value.profile != null) _state.value = DashboardState(isLoading = true)
            }
        }
        load(forceRefresh = false)
    }

    fun load() = load(forceRefresh = true)

    private fun load(forceRefresh: Boolean) {
        if (loadJob?.isActive == true) return
        loadJob = viewModelScope.launch {
            // Hide the financial snapshot until this refresh is verified.
            _state.value = DashboardState(isLoading = true)
            when (val result = profileRepository.getProfile(forceRefresh)) {
                is Result.Success -> {
                    val current = profileRepository.profile.value
                    _state.value = if (current != null && current == result.data)
                        DashboardState(profile = current)
                    else DashboardState(error = "Sesi berakhir. Silakan masuk kembali.")
                    if (_state.value.profile != null) loadPending()
                }
                is Result.Error -> _state.value = DashboardState(error = result.message)
                is Result.Loading -> Unit
            }
        }
    }

    /**
     * Hitung pengajuan yang belum final. Gagal di sini tidak memunculkan error apa pun —
     * strip pengajuan cuma pemanis, kegagalannya tidak boleh menutupi saldo.
     *
     * ponytail: hanya halaman pertama (20 transaksi terbaru). Anggota dengan lebih dari 20
     * pengajuan berjalan sekaligus akan lihat angka mentok di 20 — kirim `perPage` lebih besar
     * kalau itu pernah terjadi.
     */
    private suspend fun loadPending() {
        val expectedProfile = profileRepository.profile.value ?: return
        val result = transactionRepository.getTransactions("transaksi", page = 1, perPage = 20)
        if (profileRepository.profile.value !== expectedProfile) return
        if (result is Result.Success) {
            _state.value = _state.value.copy(
                pendingCount = result.data.items.count { !StatusMeta.isFinal(it.status) }
            )
            result.data.items.forEach { trx ->
                val key = trx.status.trim().lowercase()
                val jenis = trx.jenis.ifBlank { "Pengajuan" }
                val nominal = if (trx.nominal != 0L) " ${MoneyFormatter.format(abs(trx.nominal))}" else ""
                val (title, message) = when {
                    key == "ditolak" -> "Pengajuan ditolak" to "$jenis$nominal ditolak. Cek alasan dan ajukan ulang."
                    key in setOf("disetujui", "selesai", "pinjaman lunas", "dibayar", "kredit lunas") ->
                        "Pengajuan disetujui" to "$jenis$nominal selesai diproses."
                    else -> "Pengajuan menunggu verifikasi" to "$jenis$nominal sedang menunggu verifikasi."
                }
                notificationRepository.capture(
                    AppNotification(
                        id = "status:${trx.id}:$key",
                        type = NotificationType.STATUS,
                        title = title,
                        message = message,
                        createdAt = System.currentTimeMillis(),
                        refId = trx.id
                    )
                )
            }
        }
    }
}
