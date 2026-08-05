package com.esimko.mobile.ui.profile

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.Profile
import com.esimko.mobile.domain.repository.ProfileRepository
import com.esimko.mobile.ui.common.UiState
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.toRequestBody
import javax.inject.Inject

@HiltViewModel
class ProfileViewModel @Inject constructor(
    private val profileRepository: ProfileRepository
) : ViewModel() {

    private val _uiState = MutableStateFlow<UiState<Profile>>(UiState.Idle)
    val uiState: StateFlow<UiState<Profile>> = _uiState.asStateFlow()

    private val _passwordChangeState = MutableStateFlow<UiState<Unit>>(UiState.Idle)
    val passwordChangeState: StateFlow<UiState<Unit>> = _passwordChangeState.asStateFlow()

    private val _avatarUploadState = MutableStateFlow<UiState<Unit>>(UiState.Idle)
    val avatarUploadState: StateFlow<UiState<Unit>> = _avatarUploadState.asStateFlow()

    fun loadProfile() {
        viewModelScope.launch {
            _uiState.value = UiState.Loading
            when (val result = profileRepository.getProfile()) {
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

    fun changePassword(oldPassword: String, newPassword: String) {
        viewModelScope.launch {
            _passwordChangeState.value = UiState.Loading
            when (val result = profileRepository.changePassword(oldPassword, newPassword)) {
                is Result.Success -> {
                    _passwordChangeState.value = UiState.Success(Unit)
                }
                is Result.Error -> {
                    _passwordChangeState.value = UiState.Error(result.message)
                }
                is Result.Loading -> {
                    // Already handled
                }
            }
        }
    }

    fun uploadAvatar(imageBytes: ByteArray, mimeType: String = "image/jpeg") {
        viewModelScope.launch {
            _avatarUploadState.value = UiState.Loading
            val requestBody = imageBytes.toRequestBody(mimeType.toMediaTypeOrNull())
            val part = MultipartBody.Part.createFormData("avatar", "avatar.jpg", requestBody)
            when (val result = profileRepository.uploadAvatar(part)) {
                is Result.Success -> {
                    _avatarUploadState.value = UiState.Success(Unit)
                    loadProfile()
                }
                is Result.Error -> {
                    _avatarUploadState.value = UiState.Error(result.message)
                }
                is Result.Loading -> {
                    // Already handled
                }
            }
        }
    }

    fun logout() {
        viewModelScope.launch {
            profileRepository.logout()
        }
    }
}
