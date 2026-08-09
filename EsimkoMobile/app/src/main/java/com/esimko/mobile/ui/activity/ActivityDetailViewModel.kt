package com.esimko.mobile.ui.activity

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.PurchaseDetail
import com.esimko.mobile.domain.model.TransactionDetail
import com.esimko.mobile.domain.model.TransactionHistory
import com.esimko.mobile.domain.repository.ShoppingRepository
import com.esimko.mobile.domain.repository.TransactionHistoryRepository
import com.esimko.mobile.domain.repository.TransactionRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

/** `modul` yang punya endpoint `belanja/riwayat/{jenis}/detail`. */
private val MODUL_BELANJA = setOf("toko", "konsinyasi", "online")

/**
 * Satu layar detail untuk lima segmen Aktivitas. `modul` yang dibawa baris
 * (`Transaction.modul`) menentukan endpoint mana yang dipanggil — bukan nama tabel.
 */
data class ActivityDetailState(
    val isLoading: Boolean = true,
    val error: String? = null,
    val transaksi: TransactionDetail? = null,
    val belanja: PurchaseDetail? = null,
    /** Timeline verifikasi; gagal memuatnya tidak menggagalkan layar. */
    val timeline: List<TransactionHistory> = emptyList(),
    /** true = `modul` ini tidak punya endpoint detail (retur). */
    val noDetail: Boolean = false
) {
    val hasContent: Boolean get() = transaksi != null || belanja != null
}

@HiltViewModel
class ActivityDetailViewModel @Inject constructor(
    private val transactionRepository: TransactionRepository,
    private val shoppingRepository: ShoppingRepository,
    private val historyRepository: TransactionHistoryRepository
) : ViewModel() {

    private val _state = MutableStateFlow(ActivityDetailState())
    val state: StateFlow<ActivityDetailState> = _state.asStateFlow()

    private var id: Long = 0L
    private var modul: String = "transaksi"
    private var started = false

    fun start(transactionId: Long, modul: String) {
        if (started) return
        started = true
        this.id = transactionId
        this.modul = modul
        load()
    }

    fun load() {
        // `retur_barang` tidak punya endpoint detail. Jangan panggil apa pun — tampilkan
        // pesan, bukan spinner yang tak pernah berhenti.
        if (modul == "retur") {
            _state.value = ActivityDetailState(isLoading = false, noDetail = true)
            return
        }
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, error = null)
            if (modul in MODUL_BELANJA) loadBelanja() else loadTransaksi()
            loadTimeline()
        }
    }

    private suspend fun loadTransaksi() {
        when (val r = transactionRepository.getTransactionDetail(modul, id)) {
            is Result.Success -> _state.value =
                _state.value.copy(transaksi = r.data, isLoading = false, error = null)
            // Gagal tidak menghapus data lama (spec Bagian 6).
            is Result.Error -> _state.value = _state.value.copy(error = r.message, isLoading = false)
            is Result.Loading -> Unit
        }
    }

    private suspend fun loadBelanja() {
        when (val r = shoppingRepository.getPurchaseDetail(modul, id)) {
            is Result.Success -> _state.value =
                _state.value.copy(belanja = r.data, isLoading = false, error = null)
            is Result.Error -> _state.value = _state.value.copy(error = r.message, isLoading = false)
            is Result.Loading -> Unit
        }
    }

    /**
     * `riwayat_transaksi` hanya mengenal dua nilai `jenis`: `transaksi` dan `penjualan`
     * (dibuktikan oleh dua call site `add_riwayat_transaksi`). Nilai `modul` tidak boleh
     * diteruskan mentah — `toko` akan mengembalikan daftar kosong tanpa error.
     */
    private suspend fun loadTimeline() {
        val jenis = if (modul in MODUL_BELANJA) "penjualan" else "transaksi"
        when (val r = historyRepository.getTransactionHistory(id, jenis)) {
            is Result.Success -> _state.value = _state.value.copy(timeline = r.data)
            // Timeline gagal itu bukan kegagalan layar — detail utama tetap tampil.
            is Result.Error, is Result.Loading -> Unit
        }
    }

    fun retry() {
        started = true
        load()
    }
}
