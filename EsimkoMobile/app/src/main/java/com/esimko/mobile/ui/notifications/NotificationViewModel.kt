package com.esimko.mobile.ui.notifications

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.data.repository.NotificationRepository
import com.esimko.mobile.domain.model.AppNotification
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.SharingStarted
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.flow.stateIn
import javax.inject.Inject

@HiltViewModel
class NotificationViewModel @Inject constructor(
    private val repository: NotificationRepository
) : ViewModel() {

    val notifications: StateFlow<List<AppNotification>> = repository.data

    val unread: StateFlow<Int> = repository.data
        .map { list -> list.count { !it.read } }
        .stateIn(viewModelScope, SharingStarted.Eagerly, 0)

    fun markRead(id: String) = repository.markRead(id)
    fun markAllRead() = repository.markAllRead()
}