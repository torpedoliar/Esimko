package com.esimko.mobile.ui.settings

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.VersionCheck
import com.esimko.mobile.domain.repository.VersionRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

data class SettingsState(
    val versionInfo: VersionCheck? = null,
    val checking: Boolean = false,
    val error: String? = null
)

@HiltViewModel
class SettingsViewModel @Inject constructor(
    private val versionRepository: VersionRepository
) : ViewModel() {

    private val _state = MutableStateFlow(SettingsState())
    val state: StateFlow<SettingsState> = _state

    fun checkVersion() {
        viewModelScope.launch {
            _state.value = _state.value.copy(checking = true, error = null)
            when (val result = versionRepository.checkVersion()) {
                is Result.Success -> _state.value = _state.value.copy(versionInfo = result.data, checking = false)
                is Result.Error -> _state.value = _state.value.copy(error = result.message, checking = false)
                is Result.Loading -> Unit
            }
        }
    }
}
