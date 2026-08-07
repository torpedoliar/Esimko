package com.esimko.mobile.ui.dashboard

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.Profile
import com.esimko.mobile.domain.repository.ProfileRepository
import com.esimko.mobile.domain.repository.TransactionRepository
import com.esimko.mobile.util.StatusMeta
import dagger.hilt.android.lifecycle.HiltViewModel
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
    private val transactionRepository: TransactionRepository
) : ViewModel() {

    private val _state = MutableStateFlow(DashboardState())
    val state: StateFlow<DashboardState> = _state

    init {
        load()
    }

    fun load() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, error = null)
            when (val result = profileRepository.getProfile()) {
                is Result.Success -> _state.value = _state.value.copy(
                    profile = result.data,
                    isLoading = false
                )
                is Result.Error -> _state.value = _state.value.copy(
                    isLoading = false,
                    error = result.message
                )
                is Result.Loading -> Unit
            }
            loadPending()
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
        val result = transactionRepository.getTransactions("transaksi", page = 1, perPage = 20)
        if (result is Result.Success) {
            _state.value = _state.value.copy(
                pendingCount = result.data.items.count { !StatusMeta.isFinal(it.status) }
            )
        }
    }
}
