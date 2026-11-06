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

    init {
        // ProfileRepository menyimpan profil (memori+disk) & menyegarkannya di latar.
        // Ikuti pembaruan itu agar layar Akun menampilkan data segar tanpa spinner.
        viewModelScope.launch {
            profileRepository.profile.collect { p ->
                if (p != null) _uiState.value = UiState.Success(p)
            }
        }
    }

    fun loadProfile(forceRefresh: Boolean = false) {
        viewModelScope.launch {
            val cached = profileRepository.profile.value
            if (cached == null) _uiState.value = UiState.Loading
            when (val result = profileRepository.getProfile(forceRefresh)) {
                is Result.Success -> {
                    _uiState.value = UiState.Success(result.data)
                }
                is Result.Error -> {
                    // Masih ada cache: jangan ganti konten dengan layar error.
                    if (cached == null) _uiState.value = UiState.Error(result.message)
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
                    loadProfile(forceRefresh = true)
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
