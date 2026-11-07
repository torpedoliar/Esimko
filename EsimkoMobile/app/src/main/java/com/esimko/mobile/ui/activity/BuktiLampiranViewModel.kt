package com.esimko.mobile.ui.activity

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.data.repository.ReceiptRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.CancellationException
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

    fun load(id: Long) {
        if (loadedFor == id) return
        loadedFor = id
        viewModelScope.launch {
            _state.value = LampiranState.Loading
            try {
                _state.value = LampiranState.Success(repository.loadLampiran(id))
            } catch (e: CancellationException) {
                throw e
            } catch (e: Exception) {
                _state.value = LampiranState.Error(e.message ?: "Gagal memuat lampiran.")
            }
        }
    }
}