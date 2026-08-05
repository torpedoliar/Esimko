package com.esimko.mobile.ui.auth.login

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
class LoginViewModel @Inject constructor(
    private val repository: AuthRepository
) : ViewModel() {

    var uiState by mutableStateOf(LoginUiState())
        private set

    fun onUsernameChange(username: String) {
        uiState = uiState.copy(username = username)
    }

    fun onPasswordChange(password: String) {
        uiState = uiState.copy(password = password)
    }

    fun login() {
        if (uiState.username.isBlank() || uiState.password.isBlank()) {
            uiState = uiState.copy(error = "Username dan password wajib diisi")
            return
        }

        viewModelScope.launch {
            uiState = uiState.copy(isLoading = true, error = null)
            when (val result = repository.login(uiState.username, uiState.password)) {
                is Result.Success -> {
                    uiState = uiState.copy(isLoading = false, isLoggedIn = true)
                }
                is Result.Error -> {
                    uiState = uiState.copy(isLoading = false, error = result.message)
                }
                is Result.Loading -> {}
            }
        }
    }
}

data class LoginUiState(
    val username: String = "",
    val password: String = "",
    val isLoading: Boolean = false,
    val error: String? = null,
    val isLoggedIn: Boolean = false
)
