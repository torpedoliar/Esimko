package com.esimko.mobile.ui.savings

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.Profile
import com.esimko.mobile.domain.model.Transaction
import com.esimko.mobile.domain.repository.ProfileRepository
import com.esimko.mobile.domain.repository.TransactionRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

data class SavingsState(
    val transactions: List<Transaction> = emptyList(),
    val profile: Profile? = null,
    val isLoading: Boolean = false,
    val isSubmitting: Boolean = false,
    val error: String? = null,
    val submitError: String? = null,
    val submitSuccess: String? = null
)

@HiltViewModel
class SavingsViewModel @Inject constructor(
    private val transactionRepository: TransactionRepository,
    private val profileRepository: ProfileRepository
) : ViewModel() {

    private val _state = MutableStateFlow(SavingsState())
    val state: StateFlow<SavingsState> = _state

    init {
        load()
    }

    fun load() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, error = null)
            loadProfile()
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

    private suspend fun loadProfile() {
        when (val result = profileRepository.getProfile()) {
            is Result.Success -> {
                _state.value = _state.value.copy(profile = result.data)
            }
            is Result.Error -> Unit
            is Result.Loading -> Unit
        }
    }

    fun submitTransaction(jenis: String, nominal: Long) {
        if (_state.value.isSubmitting || nominal <= 0) return
        viewModelScope.launch {
            _state.value = _state.value.copy(isSubmitting = true, submitError = null, submitSuccess = null)
            when (val result = transactionRepository.processTransaction(jenis, nominal, null)) {
                is Result.Success -> {
                    _state.value = _state.value.copy(
                        isSubmitting = false,
                        submitSuccess = "${if (jenis == "setoran") "Setoran" else "Penarikan"} berhasil diajukan"
                    )
                    load()
                }
                is Result.Error -> {
                    _state.value = _state.value.copy(
                        isSubmitting = false,
                        submitError = result.message
                    )
                }
                is Result.Loading -> Unit
            }
        }
    }

    fun clearSubmitFeedback() {
        _state.value = _state.value.copy(submitError = null, submitSuccess = null)
    }
}
