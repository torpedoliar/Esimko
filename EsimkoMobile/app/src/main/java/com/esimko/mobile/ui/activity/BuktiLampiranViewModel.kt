package com.esimko.mobile.ui.activity

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.data.repository.ReceiptRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.Job
import kotlinx.coroutines.ensureActive
import kotlin.coroutines.coroutineContext
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

sealed interface LampiranState {
    object Loading : LampiranState
    data class Success(val bytes: ByteArray) : LampiranState
    data class Error(val message: String) : LampiranState
}

@HiltViewModel
class BuktiLampiranViewModel @Inject constructor(
    private val repository: ReceiptRepository
) : ViewModel() {

    private val _state = MutableStateFlow<LampiranState>(LampiranState.Loading)
    val state: StateFlow<LampiranState> = _state.asStateFlow()

    private var loadedFor: Long? = null
    private var job: Job? = null

    init {
        val initialRevision = repository.sessionRevision.value
        viewModelScope.launch {
            var observed = initialRevision
            repository.sessionRevision.collect { revision ->
                if (revision != observed) {
                    observed = revision
                    job?.cancel()
                    loadedFor = null
                    _state.value = LampiranState.Error("Sesi berubah. Muat ulang rincian.")
                }
            }
        }
    }

    fun load(id: Long) {
        if (loadedFor == id && (job?.isActive == true || _state.value is LampiranState.Success)) return
        job?.cancel()
        loadedFor = id
        val owner = repository.sessionRevision.value
        _state.value = LampiranState.Loading
        job = viewModelScope.launch {
            try {
                val bytes = repository.loadLampiran(id)
                coroutineContext.ensureActive()
                if (repository.sessionRevision.value == owner) _state.value = LampiranState.Success(bytes)
            } catch (e: CancellationException) {
                throw e
            } catch (_: Exception) {
                coroutineContext.ensureActive()
                if (repository.sessionRevision.value == owner) {
                    _state.value = LampiranState.Error("Gagal memuat lampiran. Periksa sesi dan koneksi, lalu coba lagi.")
                }
            }
        }
    }
}