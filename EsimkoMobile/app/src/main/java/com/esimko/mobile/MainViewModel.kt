package com.esimko.mobile

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.data.local.TokenStore
import com.esimko.mobile.domain.repository.AuthRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.SharingStarted
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.flow.stateIn
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class MainViewModel @Inject constructor(
    private val authRepository: AuthRepository,
    private val tokenStore: TokenStore
) : ViewModel() {
    val sessionChanges = tokenStore.sessionChanges
    val isLoggedIn = sessionChanges.map { tokenStore.isLoggedIn() }
        .stateIn(viewModelScope, SharingStarted.Eagerly, tokenStore.isLoggedIn())

    fun logout() {
        viewModelScope.launch { authRepository.logout() }
    }
}
