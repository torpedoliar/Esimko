package com.esimko.mobile.ui.auth.register

import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.setValue
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.repository.AuthRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class RegisterViewModel @Inject constructor(
    private val repository: AuthRepository
) : ViewModel() {

    var uiState by mutableStateOf(RegisterUiState())
        private set

    fun setNama(value: String) { uiState = uiState.copy(nama = value) }
    fun setNoKtp(value: String) { uiState = uiState.copy(noKtp = value) }
    fun setTelepon(value: String) { uiState = uiState.copy(telepon = value) }
    fun setPassword(value: String) { uiState = uiState.copy(password = value) }
    fun setConfirmPassword(value: String) { uiState = uiState.copy(confirmPassword = value) }

    fun register() {
        val s = uiState
        if (s.nama.isBlank() || s.noKtp.isBlank() || s.telepon.isBlank() ||
            s.password.isBlank() || s.confirmPassword.isBlank()
        ) {
            uiState = uiState.copy(error = "Semua field wajib diisi")
            return
        }
        if (s.password != s.confirmPassword) {
            uiState = uiState.copy(error = "Password dan konfirmasi tidak sama")
            return
        }

        viewModelScope.launch {
            uiState = uiState.copy(isLoading = true, error = null)
            when (val result = repository.register(s.noKtp, s.telepon, s.password, s.nama)) {
                is Result.Success -> {
                    uiState = uiState.copy(isLoading = false, isRegistered = true)
                }
                is Result.Error -> {
                    uiState = uiState.copy(isLoading = false, error = result.message)
                }
                is Result.Loading -> {}
            }
        }
    }
}

data class RegisterUiState(
    val nama: String = "",
    val noKtp: String = "",
    val telepon: String = "",
    val password: String = "",
    val confirmPassword: String = "",
    val isLoading: Boolean = false,
    val error: String? = null,
    val isRegistered: Boolean = false
)
