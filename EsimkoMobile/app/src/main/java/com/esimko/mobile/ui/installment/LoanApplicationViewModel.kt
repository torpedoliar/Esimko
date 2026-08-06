package com.esimko.mobile.ui.installment

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.Salary
import com.esimko.mobile.domain.model.TransactionType
import com.esimko.mobile.domain.repository.InstallmentRepository
import com.esimko.mobile.domain.repository.MasterRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

// Tenor maksimal per jenis pinjaman, sama dengan validasi backend
// (MobileController::validasi_transaksi: array(9 => 50, 10 => 18, 11 => 18))
private val MAX_TENOR = mapOf(9 to 50, 10 to 18, 11 to 18)

data class LoanApplicationState(
    val loanTypes: List<TransactionType> = emptyList(),
    val salary: Salary? = null,
    val isLoading: Boolean = false,
    val isSubmitting: Boolean = false,
    val error: String? = null,
    val submitError: String? = null,
    val submitSuccess: Long? = null,
    val selectedJenis: Int? = null,
    val nominal: String = "",
    val tenor: String = ""
)

@HiltViewModel
class LoanApplicationViewModel @Inject constructor(
    private val installmentRepository: InstallmentRepository,
    private val masterRepository: MasterRepository
) : ViewModel() {

    private val _state = MutableStateFlow(LoanApplicationState())
    val state: StateFlow<LoanApplicationState> = _state

    init {
        load()
    }

    fun load() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, error = null)
            loadSalary()
            when (val result = masterRepository.getTransactionTypes("pinjaman")) {
                is Result.Success -> _state.value = _state.value.copy(
                    loanTypes = result.data,
                    isLoading = false
                )
                is Result.Error -> _state.value = _state.value.copy(
                    error = result.message,
                    isLoading = false
                )
                is Result.Loading -> Unit
            }
        }
    }

    private suspend fun loadSalary() {
        when (val result = installmentRepository.getBaseSalary()) {
            is Result.Success -> _state.value = _state.value.copy(salary = result.data)
            else -> Unit
        }
    }

    fun selectJenis(id: Int) {
        _state.value = _state.value.copy(selectedJenis = id, tenor = "")
    }

    fun onNominalChange(value: String) {
        _state.value = _state.value.copy(nominal = value.filter { it.isDigit() })
    }

    fun onTenorChange(value: String) {
        _state.value = _state.value.copy(tenor = value.filter { it.isDigit() }.take(2))
    }

    fun maxTenor(): Int = MAX_TENOR[_state.value.selectedJenis] ?: 0

    fun submit() {
        val s = _state.value
        val jenis = s.selectedJenis ?: return
        val nominal = s.nominal.toLongOrNull() ?: 0
        val tenor = s.tenor.toIntOrNull() ?: 0
        val gaji = s.salary?.gajiPokok ?: 0
        if (s.isSubmitting || nominal <= 0 || tenor <= 0) return

        viewModelScope.launch {
            _state.value = _state.value.copy(isSubmitting = true, submitError = null)
            when (val result = installmentRepository.submitLoan(jenis, nominal, tenor, gaji, null)) {
                is Result.Success -> _state.value = _state.value.copy(
                    isSubmitting = false,
                    submitSuccess = result.data
                )
                is Result.Error -> _state.value = _state.value.copy(
                    isSubmitting = false,
                    submitError = result.message
                )
                is Result.Loading -> Unit
            }
        }
    }

    fun clearSubmit() {
        _state.value = _state.value.copy(submitError = null, submitSuccess = null)
    }
}
