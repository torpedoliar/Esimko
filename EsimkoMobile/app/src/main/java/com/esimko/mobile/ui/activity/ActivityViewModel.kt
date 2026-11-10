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
import kotlinx.coroutines.Job
import kotlinx.coroutines.delay
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
    val hasMore: Boolean = false,
    // Pencarian & urutan (Okt 2026): diteruskan ke semua endpoint daftar.
    val search: String = "",
    val sortTerbaru: Boolean = true
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
    private var generation = 0L
    private var statusGeneration = 0L
    private var pageJob: Job? = null
    private var statusJob: Job? = null
    private var searchJob: Job? = null

    /** Invalidate immediately, including during debounce and non-cooperative repository calls. */
    private fun invalidatePages() {
        generation++
        pageJob?.cancel()
        searchJob?.cancel()
        _state.value = _state.value.copy(isLoadingMore = false, isRefreshing = false)
    }

    /** Dipanggil sekali dari `LaunchedEffect(Unit)` dengan `filter` dari argumen rute. */
    fun start(filterKey: String?) {
        if (initialised) return
        initialised = true
        selectSegment(ActivitySegment.fromKey(filterKey))
    }

    fun selectSegment(segment: ActivitySegment) {
        invalidatePages()
        statusGeneration++
        statusJob?.cancel()
        _state.value = ActivityState(segment = segment)
        if (segment.modul != null) loadStatuses(segment)
        loadPage(1)
    }

    fun selectStatus(statusId: Int?) {
        invalidatePages()
        _state.value = _state.value.copy(selectedStatusId = statusId, isFirstLoad = true)
        loadPage(1)
    }

    fun refresh() {
        invalidatePages()
        _state.value = _state.value.copy(isRefreshing = true)
        loadPage(1)
    }

    fun loadMore() {
        val s = _state.value
        if (!s.hasMore || s.isLoadingMore || s.isRefreshing || s.isFirstLoad ||
            pageJob?.isActive == true || searchJob?.isActive == true) return
        _state.value = s.copy(isLoadingMore = true)
        loadPage(s.page + 1)
    }

    fun retry() {
        if (_state.value.items.isEmpty()) {
            invalidatePages()
            _state.value = _state.value.copy(isFirstLoad = true)
            loadPage(1)
        } else refresh()
    }

    /** Debounce ketikan pencarian supaya tidak membebani server tiap huruf. */
    fun onSearchChange(value: String) {
        invalidatePages()
        _state.value = _state.value.copy(search = value)
        searchJob = viewModelScope.launch {
            delay(400)
            _state.value = _state.value.copy(isFirstLoad = true)
            loadPage(1)
        }
    }

    fun toggleSort() {
        invalidatePages()
        _state.value = _state.value.copy(sortTerbaru = !_state.value.sortTerbaru, isFirstLoad = true)
        loadPage(1)
    }

    private fun loadStatuses(segment: ActivitySegment) {
        val requestGeneration = statusGeneration
        statusJob = viewModelScope.launch {
            val result = masterRepository.getTransactionStatuses(requireNotNull(segment.modul))
            if (requestGeneration != statusGeneration) return@launch
            when (result) {
                is Result.Success -> _state.value = _state.value.copy(
                    statuses = result.data, statusError = null
                )
                is Result.Error -> _state.value = _state.value.copy(statusError = result.message)
                is Result.Loading -> Unit
            }
        }
    }

    private fun loadPage(page: Int) {
        // Snapshot all filters once: the three purchase calls must use the same query.
        val query = _state.value
        val requestGeneration = ++generation
        pageJob?.cancel()
        pageJob = viewModelScope.launch {
            val result: Result<Pair<List<Transaction>, Boolean>> = when (query.segment) {
                ActivitySegment.SIMPANAN, ActivitySegment.PINJAMAN -> loadTransactions(query, page)
                ActivitySegment.BELANJA -> loadPurchases(query, page)
                ActivitySegment.ANGSURAN_BELANJA -> loadInstallments(query, page)
                ActivitySegment.RETUR -> loadReturns(query, page)
            }
            if (requestGeneration != generation) return@launch
            when (result) {
                is Result.Success -> {
                    val (fresh, hasMore) = result.data
                    val merged = ActivityGrouping.distinct(if (page == 1) fresh else query.items + fresh)
                    _state.value = _state.value.copy(
                        items = merged,
                        rows = ActivityGrouping.build(merged, terbaru = query.sortTerbaru),
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
        query: ActivityState,
        page: Int
    ): Result<Pair<List<Transaction>, Boolean>> {
        val result = transactionRepository.getTransactions(
            modul = requireNotNull(query.segment.modul),
            status = query.selectedStatusId,
            search = query.search.takeIf { it.isNotBlank() },
            sort = if (query.sortTerbaru) "terbaru" else "terlama",
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
    private suspend fun loadPurchases(query: ActivityState, page: Int): Result<Pair<List<Transaction>, Boolean>> {
        val merged = mutableListOf<Transaction>()
        var hasMore = false
        var lastError: String? = null
        JENIS_BELANJA.forEach { jenis ->
            when (val r = shoppingRepository.getPurchaseHistory(
                jenis, page = page,
                search = query.search.takeIf { it.isNotBlank() },
                sort = if (query.sortTerbaru) "terbaru" else "terlama"
            )) {
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

    private suspend fun loadInstallments(query: ActivityState, page: Int): Result<Pair<List<Transaction>, Boolean>> {
        return when (val r = shoppingRepository.getShoppingInstallments(
            page = page,
            search = query.search.takeIf { it.isNotBlank() },
            sort = if (query.sortTerbaru) "terbaru" else "terlama"
        )) {
            is Result.Success ->
                Result.Success(r.data.items.map(ActivityMapper::installmentToActivity) to r.data.hasMore)
            is Result.Error -> r
            is Result.Loading -> Result.Loading
        }
    }

    private suspend fun loadReturns(query: ActivityState, page: Int): Result<Pair<List<Transaction>, Boolean>> {
        return when (val r = shoppingRepository.getReturns(
            search = query.search.takeIf { it.isNotBlank() },
            sort = if (query.sortTerbaru) "terbaru" else "terlama",
            page = page
        )) {
            is Result.Success ->
                Result.Success(r.data.items.map(ActivityMapper::returnToActivity) to r.data.hasMore)
            is Result.Error -> r
            is Result.Loading -> Result.Loading
        }
    }
}
