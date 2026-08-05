package com.esimko.mobile.ui.savings

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.Transaction
import com.esimko.mobile.domain.repository.TransactionRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

data class SavingsState(
    val transactions: List<Transaction> = emptyList(),
    val isLoading: Boolean = false,
    val error: String? = null
)

@HiltViewModel
class SavingsViewModel @Inject constructor(
    private val transactionRepository: TransactionRepository
) : ViewModel() {

    private val _state = MutableStateFlow(SavingsState())
    val state: StateFlow<SavingsState> = _state

    init {
        loadTransactions()
    }

    fun loadTransactions() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, error = null)
            when (val result = transactionRepository.getTransactions("simpanan")) {
                is Result.Success -> {
                    _state.value = _state.value.copy(
                        transactions = result.data,
                        isLoading = false
                    )
                }
                is Result.Error -> {
                    _state.value = _state.value.copy(
                        error = result.message,
                        isLoading = false
                    )
                }
                is Result.Loading -> Unit
            }
        }
    }
}
