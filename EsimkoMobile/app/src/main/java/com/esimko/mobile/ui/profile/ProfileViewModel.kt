package com.esimko.mobile.ui.profile

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.Profile
import com.esimko.mobile.domain.repository.ProfileRepository
import com.esimko.mobile.ui.common.UiState
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.CoroutineStart
import kotlinx.coroutines.Job
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
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

    val cacheStatus = profileRepository.cacheStatus
    private var loadJob: Job? = null

    init {
        viewModelScope.launch(start = CoroutineStart.UNDISPATCHED) {
            profileRepository.profile.collect { p ->
                if (p != null) _uiState.value = UiState.Success(p)
                else if (_uiState.value is UiState.Success) _uiState.value = UiState.Loading
            }
        }
        loadProfile()
    }

    fun loadProfile(forceRefresh: Boolean = false) {
        if (loadJob?.isActive == true) return
        loadJob = viewModelScope.launch {
            val cached = profileRepository.profile.value
            _uiState.value = if (cached != null) UiState.Success(cached) else UiState.Loading
            when (val result = profileRepository.getProfile(forceRefresh)) {
                is Result.Success -> {
                    // A logout may have invalidated the result before this continuation.
                    val current = profileRepository.profile.value
                    _uiState.value = if (current != null && current == result.data)
                        UiState.Success(current) else UiState.Error("Sesi berakhir. Silakan masuk kembali.")
                }
                is Result.Error -> {
                    val current = profileRepository.profile.value
                    _uiState.value = if (current != null) UiState.Success(current) else UiState.Error(result.message)
                }
                is Result.Loading -> Unit
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

    fun uploadAvatar(readImage: () -> ByteArray?) {
        if (_avatarUploadState.value is UiState.Loading) return
        _avatarUploadState.value = UiState.Loading
        viewModelScope.launch {
            val bytes = try {
                withContext(Dispatchers.IO) { readImage() }
                    ?: error("Gambar tidak dapat dibaca. Pilih foto lain.")
            } catch (e: CancellationException) {
                throw e
            } catch (e: Exception) {
                _avatarUploadState.value = UiState.Error("Gagal membaca foto. Pastikan berkas gambar valid dan maksimal 20 MB.")
                return@launch
            }
            performAvatarUpload(bytes, "image/jpeg")
        }
    }

    fun uploadAvatar(imageBytes: ByteArray, mimeType: String = "image/jpeg") {
        if (_avatarUploadState.value is UiState.Loading) return
        _avatarUploadState.value = UiState.Loading
        viewModelScope.launch { performAvatarUpload(imageBytes, mimeType) }
    }

    private suspend fun performAvatarUpload(imageBytes: ByteArray, mimeType: String) {
        val requestBody = imageBytes.toRequestBody(mimeType.toMediaTypeOrNull())
        val part = MultipartBody.Part.createFormData("avatar", "avatar.jpg", requestBody)
        when (val result = profileRepository.uploadAvatar(part)) {
            is Result.Success -> {
                _avatarUploadState.value = UiState.Success(Unit)
                loadProfile(forceRefresh = true)
            }
            is Result.Error -> _avatarUploadState.value = UiState.Error(result.message)
            is Result.Loading -> Unit
        }
    }

    fun logout() {
        // Clear locally before session-driven navigation can dispose this view model.
        viewModelScope.launch(start = CoroutineStart.UNDISPATCHED) {
            profileRepository.logout()
        }
    }
}
