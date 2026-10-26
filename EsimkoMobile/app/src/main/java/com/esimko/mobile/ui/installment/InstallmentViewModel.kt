package com.esimko.mobile.ui.installment

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.Installment
import com.esimko.mobile.domain.model.Salary
import com.esimko.mobile.domain.repository.InstallmentRepository
import com.esimko.mobile.util.StatusMeta
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

data class InstallmentState(
    val installments: List<Installment> = emptyList(),
    val salary: Salary? = null,
    val isLoading: Boolean = false,
    val error: String? = null,
    // Pencarian & urutan (Okt 2026): diteruskan ke endpoint angsuran.
    val search: String = "",
    val sortTerbaru: Boolean = true
) {
    /** Ada yang bisa digambar; gagal memuat tidak boleh menghapusnya. */
    val hasContent: Boolean get() = installments.isNotEmpty() || salary != null

    val totalBerjalan: Long
        get() = installments.filterNot { StatusMeta.isFinal(it.status) }
            .sumOf { it.pokok + it.bunga }
}

@HiltViewModel
class InstallmentViewModel @Inject constructor(
    private val repository: InstallmentRepository
) : ViewModel() {

    private val _state = MutableStateFlow(InstallmentState())
    val state: StateFlow<InstallmentState> = _state.asStateFlow()

    private var searchJob: kotlinx.coroutines.Job? = null

    init {
        load()
    }

    fun load() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, error = null)
            loadSalary()
            val s = _state.value
            when (val result = repository.getLoanInstallments(
                search = s.search.takeIf { it.isNotBlank() },
                sort = if (s.sortTerbaru) "terbaru" else "terlama"
            )) {
                is Result.Success -> _state.value = _state.value.copy(
                    installments = result.data,
                    isLoading = false,
                    error = null
                )
                is Result.Error -> _state.value = _state.value.copy(
                    error = result.message,
                    isLoading = false
                    // `installments` sengaja tidak dikosongkan — Global Constraint: gagal tidak menghapus data.
                )
                is Result.Loading -> Unit
            }
        }
    }

    /** Debounce ketikan pencarian supaya tidak membebani server tiap huruf. */
    fun onSearchChange(value: String) {
        _state.value = _state.value.copy(search = value)
        searchJob?.cancel()
        searchJob = viewModelScope.launch {
            kotlinx.coroutines.delay(400)
            load()
        }
    }

    fun toggleSort() {
        _state.value = _state.value.copy(sortTerbaru = !_state.value.sortTerbaru)
        load()
    }

    private suspend fun loadSalary() {
        when (val result = repository.getBaseSalary()) {
            is Result.Success -> _state.value = _state.value.copy(salary = result.data)
            else -> Unit
        }
    }
}
