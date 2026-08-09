package com.esimko.mobile.ui.activity

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.Transaction
import com.esimko.mobile.domain.model.TransactionStatus
import com.esimko.mobile.domain.repository.MasterRepository
import com.esimko.mobile.domain.repository.ShoppingRepository
import com.esimko.mobile.domain.repository.TransactionRepository
import com.esimko.mobile.util.ActivityGrouping
import com.esimko.mobile.util.ActivityRow
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

/** Tiga nilai `jenis_belanja` yang dikenal `belanja/riwayat/{jenis}`. */
private val JENIS_BELANJA = listOf("toko", "konsinyasi", "online")

data class ActivityState(
    val segment: ActivitySegment = ActivitySegment.SIMPANAN,
    val items: List<Transaction> = emptyList(),
    val rows: List<ActivityRow> = emptyList(),
    /** Skeleton hanya saat belum ada data sama sekali. */
    val isFirstLoad: Boolean = true,
    val isRefreshing: Boolean = false,
    val isLoadingMore: Boolean = false,
    /** Gagal muat daftar. Kalau `items` tidak kosong, tampil sebagai `StaleBanner`. */
    val error: String? = null,
    val statuses: List<TransactionStatus> = emptyList(),
    /** Gagal muat chip status — sengaja terpisah supaya tidak mengosongkan daftar. */
    val statusError: String? = null,
    val selectedStatusId: Int? = null,
    val page: Int = 1,
    val hasMore: Boolean = false
) {
    /** Chip status hanya ada untuk segmen yang memakai `transaksi/{modul}`. */
    val showStatusFilter: Boolean get() = segment.modul != null
}

@HiltViewModel
class ActivityViewModel @Inject constructor(
    private val transactionRepository: TransactionRepository,
    private val shoppingRepository: ShoppingRepository,
    private val masterRepository: MasterRepository
) : ViewModel() {

    private val _state = MutableStateFlow(ActivityState())
    val state: StateFlow<ActivityState> = _state.asStateFlow()

    private var initialised = false

    /** Dipanggil sekali dari `LaunchedEffect(Unit)` dengan `filter` dari argumen rute. */
    fun start(filterKey: String?) {
        if (initialised) return
        initialised = true
        selectSegment(ActivitySegment.fromKey(filterKey))
    }

    fun selectSegment(segment: ActivitySegment) {
        // ponytail: pindah segmen memuat ulang dari halaman 1 — tidak ada cache per segmen.
        // Kalau bolak-balik segmen terasa lambat, simpan Map<ActivitySegment, List<Transaction>>.
        _state.value = ActivityState(segment = segment)
        if (segment.modul != null) loadStatuses(segment)
        loadPage(1)
    }

    fun selectStatus(statusId: Int?) {
        _state.value = _state.value.copy(selectedStatusId = statusId, isFirstLoad = true)
        loadPage(1)
    }

    fun refresh() {
        _state.value = _state.value.copy(isRefreshing = true)
        loadPage(1)
    }

    fun loadMore() {
        val s = _state.value
        if (!s.hasMore || s.isLoadingMore || s.isRefreshing) return
        _state.value = s.copy(isLoadingMore = true)
        loadPage(s.page + 1)
    }

    fun retry() = if (_state.value.items.isEmpty()) loadPage(1) else refresh()

    private fun loadStatuses(segment: ActivitySegment) {
        viewModelScope.launch {
            // `master/status_transaksi/{modul}`: 'simpanan' mengembalikan 5 baris (id 6 dibuang),
            // modul lain 6. `filterKey` sudah persis nilai yang diterima backend.
            when (val result = masterRepository.getTransactionStatuses(segment.filterKey)) {
                is Result.Success -> _state.value = _state.value.copy(
                    statuses = result.data, statusError = null
                )
                is Result.Error -> _state.value = _state.value.copy(statusError = result.message)
                is Result.Loading -> Unit
            }
        }
    }

    private fun loadPage(page: Int) {
        val segment = _state.value.segment
        viewModelScope.launch {
            val result: Result<Pair<List<Transaction>, Boolean>> = when (segment) {
                ActivitySegment.SIMPANAN, ActivitySegment.PINJAMAN -> loadTransactions(segment, page)
                ActivitySegment.BELANJA -> loadPurchases(page)
                ActivitySegment.ANGSURAN_BELANJA -> loadInstallments(page)
                ActivitySegment.RETUR -> loadReturns(page)
            }
            when (result) {
                is Result.Success -> {
                    val (fresh, hasMore) = result.data
                    val merged = if (page == 1) fresh else _state.value.items + fresh
                    _state.value = _state.value.copy(
                        items = merged,
                        rows = ActivityGrouping.build(merged),
                        page = page,
                        hasMore = hasMore,
                        isFirstLoad = false,
                        isRefreshing = false,
                        isLoadingMore = false,
                        error = null
                    )
                }
                is Result.Error -> _state.value = _state.value.copy(
                    // Gagal tidak menghapus data lama — Bagian 6 spec.
                    error = result.message,
                    isFirstLoad = false,
                    isRefreshing = false,
                    isLoadingMore = false
                )
                is Result.Loading -> Unit
            }
        }
    }

    private suspend fun loadTransactions(
        segment: ActivitySegment,
        page: Int
    ): Result<Pair<List<Transaction>, Boolean>> {
        val result = transactionRepository.getTransactions(
            modul = segment.modul ?: "transaksi",
            status = _state.value.selectedStatusId,
            page = page
        )
        return when (result) {
            is Result.Success -> Result.Success(result.data.items to result.data.hasMore)
            is Result.Error -> result
            is Result.Loading -> Result.Loading
        }
    }

    /**
     * `belanja/riwayat/{jenis}` menerima satu `jenis_belanja` per permintaan, jadi segmen Belanja
     * menggabungkan tiga panggilan. Gagal sebagian tidak membatalkan seluruhnya — daftar apa pun
     * yang berhasil tetap ditampilkan; hanya kalau ketiganya gagal barulah error muncul.
     */
    private suspend fun loadPurchases(page: Int): Result<Pair<List<Transaction>, Boolean>> {
        val merged = mutableListOf<Transaction>()
        var hasMore = false
        var lastError: String? = null
        JENIS_BELANJA.forEach { jenis ->
            when (val r = shoppingRepository.getPurchaseHistory(jenis, page = page)) {
                is Result.Success -> {
                    merged += r.data.items.map { ActivityMapper.purchaseToActivity(it, jenis) }
                    if (r.data.hasMore) hasMore = true
                }
                is Result.Error -> lastError = r.message
                is Result.Loading -> Unit
            }
        }
        val err = lastError
        return if (merged.isEmpty() && err != null) Result.Error(err)
        else Result.Success(merged to hasMore)
    }

    private suspend fun loadInstallments(page: Int): Result<Pair<List<Transaction>, Boolean>> {
        return when (val r = shoppingRepository.getShoppingInstallments(page = page)) {
            is Result.Success ->
                Result.Success(r.data.items.map(ActivityMapper::installmentToActivity) to r.data.hasMore)
            is Result.Error -> r
            is Result.Loading -> Result.Loading
        }
    }

    private suspend fun loadReturns(page: Int): Result<Pair<List<Transaction>, Boolean>> {
        return when (val r = shoppingRepository.getReturns(page = page)) {
            is Result.Success ->
                Result.Success(r.data.items.map(ActivityMapper::returnToActivity) to r.data.hasMore)
            is Result.Error -> r
            is Result.Loading -> Result.Loading
        }
    }
}
