package com.esimko.mobile.ui.history

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.TransactionHistory
import com.esimko.mobile.domain.repository.TransactionHistoryRepository
import com.esimko.mobile.ui.common.UiState
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class HistoryViewModel @Inject constructor(
    private val historyRepository: TransactionHistoryRepository
) : ViewModel() {

    private val _uiState = MutableStateFlow<UiState<List<TransactionHistory>>>(UiState.Idle)
    val uiState: StateFlow<UiState<List<TransactionHistory>>> = _uiState.asStateFlow()

    private val _selectedModule = MutableStateFlow("transaksi")
    val selectedModule: StateFlow<String> = _selectedModule.asStateFlow()

    fun loadHistory(transactionId: Long) {
        viewModelScope.launch {
            _uiState.value = UiState.Loading
            val type = _selectedModule.value
            when (val result = historyRepository.getTransactionHistory(transactionId, type)) {
                is Result.Success -> {
                    _uiState.value = UiState.Success(result.data)
                }
                is Result.Error -> {
                    _uiState.value = UiState.Error(result.message)
                }
                is Result.Loading -> {
                    // Already handled
                }
            }
        }
    }

    fun selectModule(module: String) {
        _selectedModule.value = module
    }
}
